# The current system

How the PHP API and the Angular app work today (October 2026), written down as the spec for the rebuild (#7). It describes what the code actually does, including its bugs, so the rebuild can choose what to keep.

- [Data model](data-model.md): tables, relationships, seed data, and how the PHP models read and write them
- [API](api.md): every endpoint, with real request and response examples
- [Matching](matching.md): how match scores and "top" answers are computed
- This page: the overall shape, sign-in, the side flows, what the frontend calls, and what to change

## Overall shape

| Piece | What it is |
|---|---|
| API | This repo: plain PHP with no framework, on Bluehost shared hosting (cPanel) at `api.humanforhuman.net` |
| Frontend | [divmazie/fullspectrumdatingwww](https://github.com/divmazie/fullspectrumdatingwww): Angular 6, served as static files at `humanforhuman.net` |
| Database | MySQL/MariaDB, 8 tables, no foreign keys |
| Pictures | Files in the API's `pictures/` folder, served directly by the web server |
| Email | SendGrid, through the vendored `thirdparty/sendgrid-php` (the SendGrid account is currently disabled, see #10) |
| Config | `config.php` on the server only, never in git: database login, `sendgrid_key`, `patreon_webhook_key` |

Entry points on the server:

- `index.php`: every API call (see [API](api.md))
- `picture_upload.php`: profile picture uploads
- `new_patreon_member.php`: invites new Patreon members
- `admin/index.php`: list of signup emails with invite links, behind an Apache `.htaccess` password
- `pictures/<file>`: profile pictures, fetched directly by the browser

## Requests, responses and sessions

Every API call is a `POST` to `index.php` with one form field, `request`, holding JSON:

```json
{
  "resource": "profiles/get-matches",
  "data": null,
  "session_info": {"id": 12, "hash": "d930ee271cb1e8941b6242a3b7ffcddf"}
}
```

`resource` is split on `/` into a group and an action. Every response has the same envelope, normally with HTTP 200:

```json
{"status": 1, "errorMessage": "", "data": {...}, "resource": ["profiles", "get-matches"]}
```

- **`status` is 1 for success and 0 otherwise.** Two successful actions, `save-identity` and `save-preference`, still return 0.
- **`errorMessage` is set on some failures only.** For example, signing in with an unknown email gives `status: 0` and no message.
- **Crashes are HTTP 500 with an empty body.** This happens on an unknown profile id or an unknown email in password reset. Since PHP 8.1, any database error also does this.

**Signing in** (`accounts/signin`) creates a row in `sessions` and returns its `session_id` and `session_hash`. The frontend keeps both in `localStorage` and sends them as `session_info` with every call. A session is valid when:
- the hash matches;
- the request comes from the **same IP address** that signed in, so changing networks signs you out;
- `active = 1`.

Sessions never expire, and `last_action_time` is never updated. Signing out only clears `localStorage`; nothing on the server ends the session. The hash is `md5(email + IP + time)`.

Passwords are hashed in the browser with plain MD5. That hash is what's sent, stored in `accounts.password_hash` and compared, so it effectively *is* the password.

## Side flows

> **Signing up and password reset are disabled since #26.** `signup-emails/save`, `signup-emails/get-by-invite-code`, `signup-emails/reset-password` and `accounts/create` now only return `status: 0` with "Signup and password reset are temporarily disabled". Their code is still in `Api.php`, and the next two sections describe how they worked before #26. The admin page and the Patreon webhook still send invite emails, but the links lead to the disabled flow.

### Signing up and invites

1. The signup page sends the email to `signup-emails/save`. For a new email, it creates a `signup_emails` row and returns its invite code.
2. The frontend strips the leading `*` from the code and goes straight to `/newuser/<code>`. **Anyone can sign up immediately; there's no real invite gate.**
3. The new-user page looks the email up with `signup-emails/get-by-invite-code`, then calls `accounts/create` with the email, the MD5 of the password and the invite code.
   - Creating the account also creates the profile, named after the part of the email before the `@`, plus a row for every dimension in `identities` and `preferences`.
4. It then signs in, and goes to `/newprofile` for a new profile or to `/home` (My profile) otherwise.

Invite codes are stored as `*` followed by an MD5, and they're never random. The formula depends on how the row was made:

| Created by | Code |
|---|---|
| The signup page | `md5(id + email)` |
| The admin page "Invite" link | `md5(id + email + signup_time)`, replacing the earlier code |
| The Patreon webhook | `md5(email)` |

Invite emails (`email_templates/invite_email.html`) link to `https://humanforhuman.net/newuser/<code>`.

### Password reset

1. The reset page sends the email to `signup-emails/reset-password`.
2. That emails the same invite link as above, using `email_templates/reset_password_email.html`, over `http://` rather than `https://`.
   - An unknown email crashes with a 500, so the response shows whether an email is registered.
3. The new-user page then calls `accounts/create` again. For an existing account, that **sets a new password**, if the invite code matches the email (#3).

Codes never change or expire, so an old reset link works forever.

### Picture upload

1. The upload dialog posts the file as `file0` to `picture_upload.php?token=<session hash>`. The session token goes in the URL, so it ends up in server access logs.
2. The script checks:
   - **The session hash and IP.** An unknown hash crashes it.
   - **The file extension:** `jpg`, `jpeg` or `png`.
   - **The size:** 20 MB or less.

   It never checks the contents are an image: that check only runs when a `submit` field is posted, which the app never does. It also doesn't resize the file or strip EXIF data, which can include the photo's location.
3. It saves the file as `pictures/<md5(name + date)>.<ext>`, sets `profiles.picture_file` and replies with plain text, despite a JSON content type. The old picture is never deleted.
4. The app shows pictures from `<api url>pictures/<picture_file>`. Profiles without a picture show `vitruvian-man.jpg`, which has to exist in `pictures/`. All pictures are public to anyone who knows the file name, and every match response includes the file names.

`picture_upload.php` sends its own CORS headers and allows `http://humanforhuman.net` (not `https`). `index.php` has its CORS headers commented out, so the main API's CORS must be configured on the server.

### Patreon webhook

`new_patreon_member.php` takes a form `POST` with `email` and `key`. If `key` matches `patreon_webhook_key` in `config.php`, it creates a signup row and sends an invite. With no key configured, it refuses everything (#25).

This isn't Patreon's own webhook format, so something in between (probably Zapier) must send it. Whether to keep it is #11.

### Admin page

`admin/index.php` lists every signup email, marks the ones that have an account, and links to send or resend the invite. Apache's `.htaccess` password protects it, and the script refuses to run if Apache doesn't report a logged-in user. "Has account" is worked out by looking the email up in `accounts`.

## What the frontend calls

| Page (route) | API calls |
|---|---|
| Sign-up (`/signup`) | `signup-emails/save` |
| New user (`/newuser/:invite_code`) | `signup-emails/get-by-invite-code`, `accounts/create`, `accounts/signin`, `profiles/user-profile` |
| Sign-in (`/signin`) | `accounts/signin` |
| Reset password (`/resetpassword`) | `signup-emails/reset-password` |
| New profile (`/newprofile`, a step-by-step setup) | `profiles/user-profile-match`, `profiles/save-profile`, `picture_upload.php`; embeds the dimensions editor |
| My profile (`/myprofile`, also `/home`) | `profiles/save-profile` (name and contact); embeds the match view of your own profile and the dimensions editor |
| Search (`/search`) | `profiles/get-matches`; embeds the match view and the dimensions editor |
| Match view (`/search/:id`, and inside My profile) | Someone else: `profiles/get-match`. Your own profile: `profiles/user-profile-match`, plus `profiles/save-profile` and `picture_upload.php` for editing |
| Word cloud (`/wordcloud`) | `dimensions/get-all` |
| Playground (`/playground`) | nothing |

The dimensions editor is a shared component that calls `dimensions/get-identities`, `get-preferences`, `save-identity` and `save-preference`. Every API action is used somewhere; `dimensions/get-all` is used only by the word cloud page.

## Things to change

Found while documenting or during the October 2026 fixes. Several already have issues.

**Security and privacy**
- **Invite codes aren't a safe proof of email ownership.** They're built from guessable values, never change and never expire, yet they can set a password. That's why sign-up and password reset are disabled (#26). For the rebuild (#13): random, single-use, expiring tokens, and sign-up should never reveal anything about an existing email.
- **Accounts are never linked to their signup email.** `Account.php` maps the `signupEmail_id` key to the column name `SignupEmail_id`, so setting it is silently ignored and `accounts.signupEmail_id` is always NULL. Anything that checks it, such as "does this email already have an account", never works.
- **Everyone's contact details and full birthday are sent to every signed-in user.** `get-matches` returns every visible profile, including `contact`, the full `birthday` and `account_id`. Visibility rules are #14.
- **`get-identities`/`get-preferences` send the user's email and stored password hash** in each of the 284 rows they return, because the models expand linked records automatically.
- **Passwords, sessions and pictures:** browser-side MD5 passwords, IP-bound sessions that never expire, no server-side sign-out, no rate limiting (#13). Pictures are public, never deleted and keep their EXIF data (#16).

**Correctness**
- **Successful saves report failure:** `save-identity` and `save-preference` return `status: 0` even when they work.
- **Crashes instead of errors:** an unknown profile in `get-match`, an unknown email in `reset-password`, an unknown session hash in `picture_upload.php`, and since PHP 8.1 any database error. All give a bare 500.
- **`get-match` has its own versions of the "top 3" lists, and both are wrong:** see [Matching](matching.md#get-match).
- **Every profile gets a row for every dimension** (284 per table), mostly unanswered. Storing only answers would be smaller and simpler (#15).
- **The model layer quietly drops values**, see [Data model](data-model.md#how-the-php-models-read-and-write):
  - inserts skip `0` and empty values;
  - updates can't set a column back to NULL;
  - `updated` timestamps never change;
  - lookups by more than one column produce invalid SQL.
- **No database constraints:** no foreign keys, no unique emails. `signup_emails` is `latin1` and the other tables are 3-byte `utf8`, so emoji can't be stored.

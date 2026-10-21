# API

Every call is a `POST` to `index.php`; see [requests, responses and sessions](README.md#requests-responses-and-sessions) for the envelope. Below, **Request** is the `data` field of the request and **Response** is the `data` field of the response. The examples were captured from a local install (PHP 8.3, MariaDB), with long lists cut short. They use small slider values (0.6–0.9); the real UI's range is 0–10. All ids and numbers come back as strings, except where noted.

**Signed in** means the call needs a valid `session_info`. Otherwise it fails with `status: 0`, `errorMessage: "Authentication invalid"`, and `data: []`.

| Resource | Signed in | What it does |
|---|---|---|
| [`signup-emails/save`](#signup-emailssave) | no | **Disabled (#26).** Join the waiting list |
| [`signup-emails/get-by-invite-code`](#signup-emailsget-by-invite-code) | no | **Disabled (#26).** Look up a signup by invite code |
| [`signup-emails/reset-password`](#signup-emailsreset-password) | no | **Disabled (#26).** Email a password reset link |
| [`accounts/create`](#accountscreate) | no | **Disabled (#26).** Create an account, or set a new password |
| [`accounts/signin`](#accountssignin) | no | Sign in |
| [`dimensions/get-all`](#dimensionsget-all) | no | All dimensions and categories |
| [`dimensions/get-identities`](#dimensionsget-identities-and-get-preferences) | yes | Your identities |
| [`dimensions/get-preferences`](#dimensionsget-identities-and-get-preferences) | yes | Your preferences |
| [`dimensions/save-identity`](#dimensionssave-identity-and-save-preference) | yes | Save one identity answer |
| [`dimensions/save-preference`](#dimensionssave-identity-and-save-preference) | yes | Save one preference answer |
| [`profiles/user-profile`](#profilesuser-profile) | yes | Your profile |
| [`profiles/save-profile`](#profilessave-profile) | yes | Edit your profile |
| [`profiles/get-matches`](#profilesget-matches) | yes | Everyone you can match with, with scores |
| [`profiles/get-match`](#profilesget-match-and-user-profile-match) | yes | One profile, compared with yours |
| [`profiles/user-profile-match`](#profilesget-match-and-user-profile-match) | yes | Your own profile, as others see it |

An unknown group or action gives `status: 0` and `errorMessage: "Unrecognized resource: <name>"`.

## Signing up and signing in

### `signup-emails/save`
**Disabled since #26.** It used to work like this:
- **Request:** the email, as a plain string: `"alex@example.com"`.
- **Response, new email:** `status: 1` with its invite code: `"*65d792881f8697e4d320b906a7fbfbe6"`.
- **Response, email already on the list:** `status: 0`, `errorMessage: "That email is already signed up!"`.

### `signup-emails/get-by-invite-code`
**Disabled since #26.** It used to work like this:
- **Request:** the code without its leading `*`: `"65d792881f8697e4d320b906a7fbfbe6"`.
- **Response:** the `signup_emails` row:

```json
{"id": "1", "email": "alex@example.com", "signup_time": "2026-10-21 14:37:15", "invited_time": null,
 "invite_code": "*65d792881f8697e4d320b906a7fbfbe6"}
```

- **Unknown code:** `status: 0`, `errorMessage: "Cannot find signup email!"`.

### `signup-emails/reset-password`
**Disabled since #26.** It used to work like this:
- **Request:** the email, as a plain string.
- **Response:** `status: 1` if the reset email was sent, otherwise `status: 0` with no message. For an unknown email it crashed with an HTTP 500.

### `accounts/create`
**Disabled since #26.** It used to work like this:
- **Request:** `{"email": "alex@example.com", "password_hash": "<md5 of the password>", "invite_code": "<code without *>"}`.
- **Response, new account:** `{"id": "1", "new_profile": true}`. It also created the profile, plus a row for every dimension in `identities` and `preferences`.
- **Response, existing account:** set its password to `password_hash` and returned `{"id": "1", "new_profile": false}`. This is how password reset worked.
- **Wrong code, or a code for a different email:** `status: 0`, `errorMessage: "Invalid invite code"` (#3).

### `accounts/signin`
- **Request:** `{"email": "alex@example.com", "password_hash": "<md5 of the password>"}`.
- **Response:**

```json
{"session_id": "1", "session_hash": "d930ee271cb1e8941b6242a3b7ffcddf",
 "userprofile": {"id": "1", "account_id": "1", "preferred_name": "alex", "birthday": null, "picture_file": null,
                 "nyc": null, "contact": null, "bioline": "Human", "bio1": null, "bio2": null, "bio3": null}}
```

- **Wrong password:** `status: 0`, `errorMessage: "Incorrect password"`.
- **Unknown email:** `status: 0` with no message.

## Dimensions, identities and preferences

### `dimensions/get-all`
- **Request:** nothing.
- **Response:** every dimension with its category expanded, plus the categories:

```json
{"dimensions": [{"id": "1", "name": "Cis", "category": "1", "default_order": "1",
                 "category_values": {"id": "1", "name": "Gender", "color": "rgb(138,70,171)", "default_order": "1"}},
                "... 284 in total"],
 "dimension_categories": [{"id": "1", "name": "Gender", "color": "rgb(138,70,171)", "default_order": "1"}, "... 8 in total"]}
```

### `dimensions/get-identities` and `get-preferences`
- **Signed in.** Request: nothing.
- **Response:** every one of your rows, the dimensions without their categories, and the categories. If you have no row for a dimension yet, an unanswered one is created first.

```json
{"identities": [{"id": "1", "profile_id": "1", "dimension_id": "1", "yesNo": "0", "slider": "1", "updated": "2026-10-21 14:37:15",
                 "profile_id_values": {"id": "1", "preferred_name": "alex", "...": "...",
                                       "account_id_values": {"id": "1", "email": "alex@example.com", "password_hash": "...", "...": "..."}},
                 "dimension_id_values": {"id": "1", "name": "Cis", "category": "1", "default_order": "1",
                                         "category_values": {"id": "1", "name": "Gender", "...": "..."}}},
                "... 284 in total"],
 "dimensions": [{"id": "1", "name": "Cis", "category": "1", "default_order": "1"}, "..."],
 "dimension_categories": [{"id": "1", "name": "Gender", "color": "rgb(138,70,171)", "default_order": "1"}, "..."]}
```

`get-preferences` returns `preferences` instead of `identities`, in the same shape. Every row repeats your whole profile and account (see [Data model](data-model.md#how-the-php-models-read-and-write)). The frontend only reads `id`, `yesNo`, `slider` and `dimension_id_values`.

### `dimensions/save-identity` and `save-preference`
- **Signed in.** Request: the row's `id`, plus `yesNo` and/or `slider`. Other fields are ignored (#3). The frontend also sends the dimension's name, id and order.

```json
{"id": "4", "yesNo": 1, "slider": 0.8}
```

- **Response:** `status: 0`, `errorMessage: ""`, `data: []`, **even when the save worked**. It also recalculates your stored vector length (#20).
- **A row that isn't yours, or doesn't exist:** `status: 0`, `errorMessage: "Not found"`.

## Profiles

### `profiles/user-profile`
- **Signed in.** Request: nothing.
- **Response:** your `profiles` row, as in `accounts/signin`'s `userprofile`.

### `profiles/save-profile`
- **Signed in.** Request: any of `preferred_name`, `birthday` (`YYYY-MM-DD`), `nyc` (1 or 0), `contact`, `bioline`, `bio1`, `bio2`, `bio3`. Anything else is ignored (#3), and a value can't be cleared back to NULL.

```json
{"preferred_name": "Alex", "birthday": "1990-05-01", "nyc": 1, "bio1": "Hello"}
```

- **Response:** the same as `profiles/user-profile-match`, so the page can redraw.

### `profiles/get-matches`
- **Signed in.** Request: nothing.
- **Response:** every profile you can match with, ordered by id: not yourself, and either no `nyc` answer or the same one as yours. Each comes with its 3 strongest positive identities and preferences. Scores are a separate JSON *string*, keyed by profile id. See [Matching](matching.md).

```json
{"matches": [{"id": "2", "account_id": "2", "preferred_name": "Sam", "birthday": "1992-02-02", "picture_file": "vitruvian-man.jpg",
              "nyc": "1", "contact": "sam@example.com", "bioline": "Human", "bio1": null, "bio2": null, "bio3": null,
              "top_identities": [{"id": "5", "name": "Woman", "category": "1", "default_order": "5",
                                  "category_values": {"id": "1", "name": "Gender", "color": "rgb(138,70,171)", "default_order": "1"}}],
              "top_preferences": [{"id": "4", "name": "Man", "...": "..."}]}],
 "cosines": "{\"2\":{\"myprefs\":0,\"theirprefs\":0}}"}
```

The scores inside `cosines` are numbers. `picture_file` is `vitruvian-man.jpg` for profiles without a picture. The whole list comes back at once: about 8 MB at 10,000 profiles (#15).

### `profiles/get-match` and `user-profile-match`
- **Signed in.** Request for `get-match`: the profile id, as a plain value: `"2"`. `user-profile-match` takes nothing and uses your own profile.
- **Response:**

```json
{"match": {"id": "2", "preferred_name": "Sam", "birthday": "1992-02-02", "contact": "sam@example.com", "...": "...", "age": 34},
 "top_identities": [{"id": "5", "name": "Woman", "...": "..."}],
 "top_preferences": [{"id": "1", "name": "Cis", "...": "..."}, "... 3 in total"],
 "theyLikeYou": [{"dimension": {"id": "4", "name": "Man", "...": "..."}, "match_value": 0.5599999999999999, "not": false}],
 "youLikeThem": [{"dimension": {"id": "5", "name": "Woman", "...": "..."}, "match_value": 0.54, "not": false}],
 "youNotLikeThem": []}
```

- **`match`:** the profile row plus `age` in whole years. With no birthday, `age` is 0. `picture_file` gets the same default as in `get-matches`.
- **`youLikeThem` / `youNotLikeThem`:** dimensions where your preference times their identity is positive or negative. `match_value` is that product of the two `yesNo × slider` values (not cubed), as a number. `not` is true when their identity is a "no".
- **`theyLikeYou`:** dimensions where their preference times your identity is positive. `not` is true when your identity is a "no".
- **`top_identities` / `top_preferences`** are computed differently from `get-matches`, and wrongly; see [Matching](matching.md#get-match).
- **A profile id that doesn't exist** crashes with HTTP 500.
- **`user-profile-match` with a session id that doesn't exist** also crashes with HTTP 500, because it reads the session before checking it.

## Outside `index.php`

| Endpoint | Request | Response |
|---|---|---|
| `picture_upload.php?token=<session hash>` | Multipart `POST` with the file as `file0` | Plain-text messages; see [picture upload](README.md#picture-upload) |
| `new_patreon_member.php` | Form `POST` with `email` and `key` | Nothing; see [Patreon webhook](README.md#patreon-webhook) |
| `admin/index.php` | `GET`, or `?invite=<email>` to send an invite. Needs the `.htaccess` login | An HTML list; see [admin page](README.md#admin-page) |
| `pictures/<file>` | `GET` | The image |

# Matching

Search ranks people by how well they fit you in both directions: whether they're what you're looking for, and whether you're what they're looking for. The scores come from `profiles/get-matches`, computed in SQL by `Matcher.php` since #20. Before that, the same formula ran in `python/match_cosine.py` over CSV files. `profiles/get-match` adds a per-dimension comparison for one profile, using its own separate code.

## Answers as vectors

Each profile has an answer for every dimension (see [identities and preferences](data-model.md#identities-and-preferences)):

- **value** = `yesNo × slider`, from -10 to 10. Positive means yes, negative no, 0 unanswered or zero strength.
- **scoring value** = value³, from -1000 to 1000. Cubing keeps the sign and makes strong answers count far more than weak ones: a 10 weighs 1000 times as much as a 1.

So each profile has two vectors over the 284 dimensions: its identities **I** and its preferences **P**.

## Scores

For you (the viewer) and another profile, there are two cosine distances:

- `myprefs` = 1 − cos(their **I**, your **P**): how well they fit what you're looking for.
- `theirprefs` = 1 − cos(their **P**, your **I**): how well you fit what they're looking for.

Here cos(**a**, **b**) = (**a** · **b**) / (|**a**| |**b**|), and only dimensions both of you answered add to **a** · **b**.

| Distance | Meaning |
|---|---|
| 0 | Their answers point exactly the same way as yours (only the direction counts, not the overall strength) |
| 1 | No overlap, or either vector is empty (all unanswered) |
| 2 | Exactly opposite |

The frontend combines them as `0.5 × myprefs + 0.5 × theirprefs` and lists matches smallest first (`search.component.ts`). The API returns both scores, but the 50/50 weighting is fixed in the frontend.

**Who's included:** everyone except you, as long as they either haven't answered `nyc` or gave the same answer as you. If you haven't answered `nyc` yourself, you only see others who haven't either.

### How the SQL works

- **Dot products** are a join between your rows and everyone else's on `dimension_id`, skipping rows with `yesNo = 0`, grouped by profile. One join is your preferences against their identities, the other your identities against their preferences.
- **Vector lengths** |**I**| and |**P**| are stored in `profiles.identity_norm` and `preference_norm`. They're recalculated from scratch whenever the profile saves an identity or preference. If you edit answers directly in the database, re-run step 5 of `sql/migrations/001_sql_matching.sql`.
- **Only real dimensions count:** rows for dimension ids that aren't in `dimensions` are ignored, in both the dot products and the lengths.

## Top 3 in `get-matches`

Each match comes with its 3 strongest positive identities and preferences:
- only values above 0;
- highest value first;
- ties broken by the oldest row.

`Matcher::topDimensions` reads every positive answer in one streamed query and keeps the best 3 per profile.

## get-match

`profiles/get-match` (and `user-profile-match`, for your own profile) doesn't use `Matcher.php`. It loads both profiles' 284 identities and preferences and compares them in PHP. Its lists differ from `get-matches`, and two of them are wrong:

| List | What it actually contains |
|---|---|
| `top_identities` | Their first 3 positive identities after a rough sort. The sort's comparison returns the difference of two values, which PHP turns into a whole number, so answers less than 1 apart count as equal and keep their database order. |
| `top_preferences` | **Not filtered to positive answers.** It's the first 3 preferences visited by a nested loop, usually the first 3 after the same rough sort, whether answered, "no" or unanswered. It can repeat a dimension. |
| `youLikeThem` | Dimensions where your preference × their identity is positive, using plain values, not cubed |
| `youNotLikeThem` | The same, where the product is negative |
| `theyLikeYou` | Dimensions where their preference × your identity is positive. There's no "they don't like you" list. |

The like/dislike lists follow the order of their identities after the rough sort, not the size of `match_value`. Finding the matching answer for each dimension scans the other profile's rows until it finds it, so a call does about 80,000 comparisons, plus two database queries per listed dimension.

The rebuild should define these lists once and share the code with `get-matches` (#15).

## Checking a new implementation

The `claude/matching-tests` branch has scripts that generate random profiles, compare two implementations' `get-matches` results to within float rounding, and benchmark them. That's how #20 was checked against the old Python version. As of this writing the branch isn't merged, and its generator uses slider values from 0.01 to 1 rather than the UI's 0–10.

-- Prepares the database for matching in SQL (Matcher.php). Run once, in order, before deploying that code.
-- Back up the database first (phpMyAdmin > Export).

-- 1. Index identities/preferences by profile and dimension. Not unique yet, because duplicates may exist;
-- it makes step 2 fast (without it, step 2 compares every row with every other row).
ALTER TABLE identities ADD KEY profile_dimension (profile_id, dimension_id);
ALTER TABLE preferences ADD KEY profile_dimension (profile_id, dimension_id);

-- 2. Remove duplicate identity/preference rows, which would be double counted when scoring.
-- Keeps the copy that has an answer (yesNo <> 0); if both or neither do, keeps the newer row.
DELETE a FROM identities a
JOIN identities b ON b.profile_id = a.profile_id AND b.dimension_id = a.dimension_id AND b.id <> a.id
WHERE (b.yesNo <> 0 AND a.yesNo = 0)
   OR ((b.yesNo <> 0) = (a.yesNo <> 0) AND b.id > a.id);

DELETE a FROM preferences a
JOIN preferences b ON b.profile_id = a.profile_id AND b.dimension_id = a.dimension_id AND b.id <> a.id
WHERE (b.yesNo <> 0 AND a.yesNo = 0)
   OR ((b.yesNo <> 0) = (a.yesNo <> 0) AND b.id > a.id);

-- 3. Make the index unique so duplicates can't come back, and index the columns matching reads
ALTER TABLE identities
  DROP KEY profile_dimension,
  ADD UNIQUE KEY profile_dimension (profile_id, dimension_id),
  ADD KEY dimension_answer (dimension_id, yesNo, profile_id, slider),
  ADD KEY answered (yesNo, profile_id, slider, dimension_id);

ALTER TABLE preferences
  DROP KEY profile_dimension,
  ADD UNIQUE KEY profile_dimension (profile_id, dimension_id),
  ADD KEY dimension_answer (dimension_id, yesNo, profile_id, slider),
  ADD KEY answered (yesNo, profile_id, slider, dimension_id);

-- 4. Store each profile's vector lengths
ALTER TABLE profiles
  ADD identity_norm double NOT NULL DEFAULT 0,
  ADD preference_norm double NOT NULL DEFAULT 0;

-- 5. Fill in the lengths. The app keeps them current from now on; rerun this after editing
-- identities or preferences directly in the database.
UPDATE profiles SET
  identity_norm = (SELECT COALESCE(SQRT(SUM(POW(POW(t.yesNo * t.slider, 3), 2))), 0)
                   FROM identities t JOIN dimensions d ON d.id = t.dimension_id WHERE t.profile_id = profiles.id),
  preference_norm = (SELECT COALESCE(SQRT(SUM(POW(POW(t.yesNo * t.slider, 3), 2))), 0)
                     FROM preferences t JOIN dimensions d ON d.id = t.dimension_id WHERE t.profile_id = profiles.id);

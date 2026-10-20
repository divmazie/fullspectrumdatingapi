<?php
/**
 * Scores profiles against each other in MySQL.
 *
 * Each identity/preference is a vector over dimensions with values (yesNo * slider)^3; cubing keeps the
 * sign and emphasizes strong answers. For a viewer and another profile there are two cosine distances:
 *   myprefs    = distance between their identities and the viewer's preferences
 *   theirprefs = distance between their preferences and the viewer's identities
 * 0 means a perfect match, 1 no overlap (or an empty vector), 2 the exact opposite.
 * Vector lengths are stored in profiles.identity_norm/preference_norm and kept current by updateNorms().
 */
class Matcher {

    private static function value($alias) {
        return "POW($alias.yesNo * $alias.slider, 3)";
    }

    private static function normSql($table, $profileExpr) {
        return "(SELECT COALESCE(SQRT(SUM(POW(" . self::value('t') . ", 2))), 0)
                 FROM $table t JOIN dimensions d ON d.id = t.dimension_id
                 WHERE t.profile_id = $profileExpr)";
    }

    // Dot products of the viewer's $myTable vector with every other profile's $theirTable vector.
    // Unanswered dimensions (yesNo = 0) add nothing, so they're skipped.
    private static function dotSql($myTable, $theirTable, $me) {
        return "SELECT o.profile_id, SUM(" . self::value('o') . " * " . self::value('m') . ") AS dot
                FROM $myTable m
                JOIN dimensions d ON d.id = m.dimension_id
                JOIN $theirTable o ON o.dimension_id = m.dimension_id AND o.yesNo <> 0
                WHERE m.profile_id = $me AND m.yesNo <> 0
                GROUP BY o.profile_id";
    }

    private static function rows($sql) {
        $result = DBConnectionFactory::Instance()->runQuery($sql);
        $rows = [];
        while ($result && $row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    public static function updateNorms($profile_id) {
        $id = (int)$profile_id;
        DBConnectionFactory::Instance()->runQuery("UPDATE profiles SET
            identity_norm = " . self::normSql('identities', 'profiles.id') . ",
            preference_norm = " . self::normSql('preferences', 'profiles.id') . "
            WHERE id = $id");
    }

    // Every profile the viewer can match with (not themselves; same nyc setting or none), with both distances
    public static function rankedProfiles($profile_id) {
        $me = (int)$profile_id;
        return self::rows("SELECT o.*,
                COALESCE(1 - COALESCE(mine.dot, 0) / NULLIF(o.identity_norm * me.preference_norm, 0), 1) AS myprefs,
                COALESCE(1 - COALESCE(theirs.dot, 0) / NULLIF(o.preference_norm * me.identity_norm, 0), 1) AS theirprefs
            FROM profiles o
            JOIN profiles me ON me.id = $me
            LEFT JOIN (" . self::dotSql('preferences', 'identities', $me) . ") mine ON mine.profile_id = o.id
            LEFT JOIN (" . self::dotSql('identities', 'preferences', $me) . ") theirs ON theirs.profile_id = o.id
            WHERE o.id <> me.id AND (o.nyc IS NULL OR o.nyc = me.nyc)
            ORDER BY o.id");
    }

    // profile_id => up to $limit ids from $dimensions with the strongest positive values, strongest first
    // (ties: oldest row first). Streams the rows, keeping only the current top few per profile in memory.
    public static function topDimensions($table, $dimensions, $limit = 3) {
        $result = DBConnectionFactory::Instance()->runQuery("SELECT profile_id, dimension_id, yesNo * slider AS value, id
            FROM $table WHERE yesNo <> 0 AND yesNo * slider > 0", MYSQLI_USE_RESULT);
        $stronger = function($a, $b) {
            return $a['value'] == $b['value'] ? $a['id'] - $b['id'] : ($a['value'] < $b['value'] ? 1 : -1);
        };
        $top = [];
        while ($result && $row = $result->fetch_assoc()) {
            if (!isset($dimensions[$row['dimension_id']])) {
                continue;
            }
            $row['value'] = (float)$row['value'];
            $top[$row['profile_id']][] = $row;
            if (count($top[$row['profile_id']]) > $limit) {
                usort($top[$row['profile_id']], $stronger);
                array_pop($top[$row['profile_id']]);
            }
        }
        if ($result) {
            $result->free();
        }
        foreach ($top as $profile_id => $rows) {
            usort($rows, $stronger);
            $top[$profile_id] = array_column($rows, 'dimension_id');
        }
        return $top;
    }

    // dimension id => dimension values with its category expanded, as Dimension::getValues(true) returns them
    public static function dimensionValues() {
        $categories = [];
        foreach (DimensionCategory::getDimensionCategories() as $category) {
            $categories[$category->getValue('id')] = $category->getValues();
        }
        $dimensions = [];
        foreach (Dimension::getDimensions() as $dimension) {
            $values = $dimension->getValues();
            if (isset($categories[$values['category']])) {
                $values['category_values'] = $categories[$values['category']];
            }
            $dimensions[$values['id']] = $values;
        }
        return $dimensions;
    }

}

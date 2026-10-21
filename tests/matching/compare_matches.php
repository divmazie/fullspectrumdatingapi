<?php
// Usage: php compare_matches.php <reference api url> <candidate api url> [viewer profile ids...]
// Calls profiles/get-matches on both APIs (backed by the same test database) for each viewer and checks the
// candidate against the reference: the same matches in the same order with the same profile fields, and the
// same myprefs/theirprefs scores to within float rounding (sliders are stored at single precision).
// Top identities/preferences are checked against their definition (the 3 strongest positive answers, ties
// by row id) rather than against the reference, because before #20 they were only roughly sorted (PHP
// truncated the sort's comparison to a whole number, so values less than 1 apart kept their database order).
//
// Without viewer ids it picks a mix: profiles with no answers, each nyc setting, and some at random.
// When porting matching to a new API, adapt fetch() below to its request format.
require __DIR__ . '/db.php';
if (count($argv) < 3) {
    fwrite(STDERR, "Usage: php compare_matches.php <reference api url> <candidate api url> [viewer ids...]\n");
    exit(1);
}
[$_, $reference, $candidate] = $argv;
$db = matching_test_db();
$viewers = array_slice($argv, 3);
if (!$viewers) {
    $viewers = array_column($db->query("
        (SELECT p.id FROM profiles p WHERE NOT EXISTS (SELECT 1 FROM identities i WHERE i.profile_id = p.id AND i.yesNo <> 0) LIMIT 2)
        UNION (SELECT id FROM profiles WHERE nyc IS NULL LIMIT 3)
        UNION (SELECT id FROM profiles WHERE nyc = '0' LIMIT 3)
        UNION (SELECT id FROM profiles WHERE nyc = '1' LIMIT 3)
        UNION (SELECT id FROM profiles ORDER BY RAND(3) LIMIT 9)")->fetch_all(MYSQLI_ASSOC), 'id');
}
$fails = 0; $maxDiff = 0; $checked = 0;

function fetch($url, $profile) {
    $r = matching_test_call($url, 'profiles/get-matches', false, $profile);
    if (!$r || $r['status'] !== 1) {
        fwrite(STDERR, "Bad response from $url for profile $profile\n");
        exit(1);
    }
    return [$r['data']['matches'], json_decode($r['data']['cosines'], true)];
}
function fail($msg) { global $fails; $fails++; if ($fails <= 10) echo "FAIL $msg\n"; }
function expectedTop($db, $table, $profile) {
    $rows = $db->query("SELECT t.dimension_id FROM $table t JOIN dimensions d ON d.id = t.dimension_id
                        WHERE t.profile_id = $profile AND t.yesNo * t.slider > 0
                        ORDER BY t.yesNo * t.slider DESC, t.id LIMIT 3")->fetch_all(MYSQLI_ASSOC);
    return array_column($rows, 'dimension_id');
}

foreach ($viewers as $v) {
    [$refMatches, $refScores] = fetch($reference, $v);
    [$newMatches, $newScores] = fetch($candidate, $v);
    if (array_column($refMatches, 'id') !== array_column($newMatches, 'id')) {
        fail("viewer $v: match list differs (" . count($refMatches) . " reference vs " . count($newMatches) . " candidate)");
        continue;
    }
    foreach ($newMatches as $i => $m) {
        $withoutTop = function($x) { unset($x['top_identities'], $x['top_preferences']); return $x; };
        if ($withoutTop($refMatches[$i]) !== $withoutTop($m)) fail("viewer $v match {$m['id']}: profile fields differ");
        foreach (['myprefs', 'theirprefs'] as $k) {
            $d = abs($refScores[$m['id']][$k] - $newScores[$m['id']][$k]);
            $maxDiff = max($maxDiff, $d);
            if ($d > 1e-6) fail("viewer $v match {$m['id']} $k: reference {$refScores[$m['id']][$k]}, candidate {$newScores[$m['id']][$k]}");
            $checked++;
        }
        foreach (['top_identities' => 'identities', 'top_preferences' => 'preferences'] as $key => $table) {
            if (array_column($m[$key], 'id') != expectedTop($db, $table, $m['id'])) fail("viewer $v match {$m['id']}: $key aren't the strongest 3");
            foreach ($m[$key] as $dim) {
                if (array_keys($dim) !== ['id', 'name', 'category', 'default_order', 'category_values']) fail("viewer $v: $key entry has keys " . json_encode(array_keys($dim)));
            }
        }
    }
}
printf("%d viewers, %d scores compared, largest difference %.2e\n", count($viewers), $checked, $maxDiff);
echo $fails ? "$fails FAILED\n" : "ALL MATCH\n";
exit($fails ? 1 : 0);

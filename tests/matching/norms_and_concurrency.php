<?php
// Usage: php norms_and_concurrency.php <api url>
// Serve the API with several workers (e.g. PHP_CLI_SERVER_WORKERS=8 php -S ...) so requests really overlap.
// 1. Saving identities/preferences keeps profiles.identity_norm/preference_norm equal to a fresh recomputation.
// 2. Simultaneous first loads of a profile's identities/preferences, which create its missing rows, create
//    exactly one row per dimension and every request gets back the real rows.
require __DIR__ . '/db.php';
if (!isset($argv[1])) {
    fwrite(STDERR, "Usage: php norms_and_concurrency.php <api url>\n");
    exit(1);
}
$url = $argv[1];
$db = matching_test_db(true);
$fails = 0;
function check($name, $ok) { global $fails; echo ($ok ? 'PASS ' : 'FAIL ') . "$name\n"; if (!$ok) $fails++; }
function recomputed($db, $table, $profile) {
    return (float)$db->query("SELECT COALESCE(SQRT(SUM(POW(POW(t.yesNo * t.slider, 3), 2))), 0) n FROM $table t
        JOIN dimensions d ON d.id = t.dimension_id WHERE t.profile_id = $profile")->fetch_assoc()['n'];
}

// 1. Norms follow saves
$p = (int)$db->query("SELECT id FROM profiles WHERE identity_norm > 0 ORDER BY id LIMIT 1")->fetch_assoc()['id'];
foreach (['identities' => ['get-identities', 'save-identity', 'identity_norm'],
          'preferences' => ['get-preferences', 'save-preference', 'preference_norm']] as $table => [$get, $save, $col]) {
    $rows = matching_test_call($url, "dimensions/$get", false, $p)['data'][$table];
    $edits = [[$rows[0]['id'], 1, 0.8], [$rows[1]['id'], -1, 0.35], [$rows[2]['id'], 0, 1], [$rows[0]['id'], -1, 0.6]];
    $ok = true;
    foreach ($edits as [$id, $yes, $slider]) {
        matching_test_call($url, "dimensions/$save", ['id' => $id, 'yesNo' => $yes, 'slider' => $slider], $p);
        $stored = (float)$db->query("SELECT $col FROM profiles WHERE id = $p")->fetch_assoc()[$col];
        $ok = $ok && abs($stored - recomputed($db, $table, $p)) < 1e-9;
    }
    check("$save: $col stays equal to a fresh recomputation across " . count($edits) . " edits", $ok);
}

// 2. Simultaneous first loads, for a new profile with no identity/preference rows yet
$dims = (int)$db->query("SELECT COUNT(*) c FROM dimensions")->fetch_assoc()['c'];
$new = (int)$db->query("SELECT MAX(id) + 1 m FROM profiles")->fetch_assoc()['m'];
$db->query("INSERT INTO accounts (id, email, password_hash) VALUES ($new, 'concurrent$new@example.com', 'x')");
$db->query("INSERT INTO profiles (id, account_id) VALUES ($new, $new)");
$db->query("INSERT INTO sessions (id, account, profile, ip_address, session_hash, active) VALUES ($new, $new, $new, '127.0.0.1', 'h$new', 1)");
foreach (['identities' => 'get-identities', 'preferences' => 'get-preferences'] as $table => $get) {
    $mh = curl_multi_init(); $handles = [];
    for ($i = 0; $i < 6; $i++) {
        $handles[] = $h = matching_test_request($url, "dimensions/$get", false, $new);
        curl_multi_add_handle($mh, $h);
    }
    do { curl_multi_exec($mh, $running); curl_multi_select($mh); } while ($running);
    $dbIds = array_column($db->query("SELECT id FROM $table WHERE profile_id = $new")->fetch_all(MYSQLI_ASSOC), 'id');
    sort($dbIds);
    check("$get x6 at once: exactly $dims rows created (got " . count($dbIds) . ")", count($dbIds) == $dims);
    $allReal = true;
    foreach ($handles as $h) {
        $r = json_decode(curl_multi_getcontent($h), true);
        $ids = array_column($r['data'][$table] ?? [], 'id');
        sort($ids);
        $allReal = $allReal && ($r['status'] ?? 0) === 1 && $ids == $dbIds;
    }
    check("$get x6 at once: every response lists exactly the real rows", $allReal);
}
echo $fails ? "\n$fails FAILED\n" : "\nALL PASSED\n";
exit($fails ? 1 : 0);

<?php
// Usage: php gen_profiles.php <profiles> [seed] [answered fraction, default 0.15]
// Adds random profiles to a test database. Like production, every profile gets a row for every dimension in
// identities and preferences, mostly unanswered (yesNo = 0); 1 in 20 profiles answers nothing, to exercise
// empty vectors. Answers are yes or no with a slider from 0 to 10, as in the UI. nyc is random among
// NULL, 0 and 1. Each profile also gets a session (id = profile id, token "h<id>", IP 127.0.0.1) so the
// other scripts can call the API as it, from this machine.
require __DIR__ . '/db.php';
if (!isset($argv[1])) {
    fwrite(STDERR, "Usage: php gen_profiles.php <profiles> [seed] [answered fraction]\n");
    exit(1);
}
$n = (int)$argv[1];
mt_srand((int)($argv[2] ?? 1));
$answered = (float)($argv[3] ?? 0.15);
$db = matching_test_db(true);
$dims = array_column($db->query('SELECT id FROM dimensions ORDER BY id')->fetch_all(MYSQLI_ASSOC), 'id');
$nyc = ['NULL', "'0'", "'1'"];

function flush_rows($db, $table, $cols, &$rows) {
    if ($rows) {
        $db->query("INSERT INTO $table ($cols) VALUES " . implode(',', $rows));
        $rows = [];
    }
}

$start = (int)$db->query('SELECT COALESCE(MAX(id), 0) m FROM profiles')->fetch_assoc()['m'];
$ids = []; $prefs = []; $profiles = []; $accounts = []; $sessions = [];
for ($k = 1; $k <= $n; $k++) {
    $p = $start + $k;
    $accounts[] = "($p, 'gen$p@example.com', 'x')";
    $profiles[] = "($p, $p, 'gen$p', " . $nyc[mt_rand(0, 2)] . ")";
    $sessions[] = "($p, $p, $p, '127.0.0.1', 'h$p', 1)";
    $rate = mt_rand(1, 20) == 1 ? 0 : $answered;
    foreach ($dims as $d) {
        foreach (['ids', 'prefs'] as $which) {
            $yes = mt_rand() / mt_getrandmax() < $rate ? [1, -1][mt_rand(0, 1)] : 0;
            // Answered: 0 to 10 in steps of 0.1, like the UI. Unanswered rows keep the default of 1
            $slider = $yes ? mt_rand(0, 100) / 10 : 1;
            ${$which}[] = "($p, $d, $yes, $slider)";
        }
    }
    if (count($ids) > 20000 || $k == $n) {
        flush_rows($db, 'accounts', 'id, email, password_hash', $accounts);
        flush_rows($db, 'profiles', 'id, account_id, preferred_name, nyc', $profiles);
        flush_rows($db, 'sessions', 'id, account, profile, ip_address, session_hash, active', $sessions);
        flush_rows($db, 'identities', 'profile_id, dimension_id, yesNo, slider', $ids);
        flush_rows($db, 'preferences', 'profile_id, dimension_id, yesNo, slider', $prefs);
    }
}
// The rows were inserted directly, so fill in the stored vector lengths the app normally keeps up to date
$norm = function($table) {
    return "(SELECT COALESCE(SQRT(SUM(POW(POW(t.yesNo * t.slider, 3), 2))), 0)
             FROM $table t JOIN dimensions d ON d.id = t.dimension_id WHERE t.profile_id = profiles.id)";
};
$hasNorms = $db->query("SHOW COLUMNS FROM profiles LIKE 'identity_norm'")->num_rows > 0;
if ($hasNorms) {
    $db->query("UPDATE profiles SET identity_norm = {$norm('identities')}, preference_norm = {$norm('preferences')}
                WHERE id > $start");
}
echo "Created profiles " . ($start + 1) . ".." . ($start + $n) . ($hasNorms ? "" : " (no norm columns: pre-#20 schema)") . "\n";

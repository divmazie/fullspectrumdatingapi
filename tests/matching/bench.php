<?php
// Usage: php bench.php <api url> [viewer profile ids...]
// Times profiles/get-matches for each viewer (default: 5 random profiles that have answered something).
// Run the server with a realistic memory limit, e.g. php -d memory_limit=128M -S ...
require __DIR__ . '/db.php';
if (!isset($argv[1])) {
    fwrite(STDERR, "Usage: php bench.php <api url> [viewer ids...]\n");
    exit(1);
}
$url = $argv[1];
$db = matching_test_db();
$viewers = array_slice($argv, 2) ?: array_column($db->query(
    "SELECT id FROM profiles WHERE identity_norm > 0 ORDER BY RAND(5) LIMIT 5")->fetch_all(MYSQLI_ASSOC), 'id');
$profiles = $db->query("SELECT COUNT(*) c FROM profiles")->fetch_assoc()['c'];
echo "$profiles profiles\n";
foreach ($viewers as $v) {
    $ch = matching_test_request($url, 'profiles/get-matches', false, $v);
    $t = microtime(true);
    $out = curl_exec($ch);
    $t = microtime(true) - $t;
    $r = json_decode($out, true);
    printf("viewer %-7s %7.0f ms, %6d matches, %5.1f MB response, status %s\n",
        $v, $t * 1000, count($r['data']['matches'] ?? []), strlen($out) / 1e6, $r['status'] ?? 'error');
}

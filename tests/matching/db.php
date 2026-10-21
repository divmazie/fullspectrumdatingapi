<?php
// Shared by the matching test scripts. Connects with the settings in the repo's config.php, or the file
// named by the MATCHING_CONFIG environment variable.
//
// Scripts that write to the database pass $forWrites = true, which refuses any database whose name doesn't
// contain "test". gen_profiles.php creates sessions with predictable tokens, so running it against
// production would let anyone sign in as the generated profiles.
function matching_test_db($forWrites = false) {
    $configFile = getenv('MATCHING_CONFIG') ?: __DIR__ . '/../../config.php';
    $config = include $configFile;
    if (!is_array($config)) {
        fwrite(STDERR, "Can't read database settings from $configFile\n");
        exit(1);
    }
    if ($forWrites && stripos($config['dbname'], 'test') === false) {
        fwrite(STDERR, "Refusing to write to database '{$config['dbname']}': use a database whose name contains \"test\"\n");
        exit(1);
    }
    return new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);
}

// Builds a request in the format the PHP API expects, signed in as $profile via the session that
// gen_profiles.php created for it.
function matching_test_request($url, $resource, $data, $profile) {
    $request = json_encode(['resource' => $resource, 'data' => $data,
                            'session_info' => ['id' => $profile, 'hash' => "h$profile"]]);
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => http_build_query(['request' => $request]),
                            CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 900]);
    return $ch;
}

function matching_test_call($url, $resource, $data, $profile) {
    return json_decode(curl_exec(matching_test_request($url, $resource, $data, $profile)), true);
}

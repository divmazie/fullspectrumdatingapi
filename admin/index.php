<?php
/**
 * Created by PhpStorm.
 * User: davidamackenzie
 * Date: 9/28/19
 * Time: 2:10 PM
 */
spl_autoload_register(function ($class_name) {
    include '../' . $class_name . '.php';
});

// Requires 'admin_user' and 'admin_password_hash' (from password_hash()) in config.php; denies everyone if unset
$configs = include(__DIR__ . '/../config.php');
$admin_user = isset($configs['admin_user']) ? $configs['admin_user'] : '';
$admin_password_hash = isset($configs['admin_password_hash']) ? $configs['admin_password_hash'] : '';
if ($admin_user === '' || $admin_password_hash === ''
    || !isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])
    || !hash_equals($admin_user, $_SERVER['PHP_AUTH_USER'])
    || !password_verify($_SERVER['PHP_AUTH_PW'], $admin_password_hash)) {
    header('WWW-Authenticate: Basic realm="admin"');
    http_response_code(401);
    die('Not authorized');
}

function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

if (isset($_GET['invite'])) {
    $signup_email = SignupEmail::getEmailObject($_REQUEST['invite']);
    if ($signup_email && $signup_email->invite()) {
        echo "Invite sent to " . h($_REQUEST['invite']) . "<br /><br />";
    } else {
        echo "Something went wrong emailing " . h($_REQUEST['invite']) . "<br /><br />";
    }
}

$emails = SignupEmail::getAllSignupEmails();

foreach ($emails as $email) {
    $account = Account::getAccountObj($email->getValue('email'));
    // echo json_encode($account);
    $invite_href = h('index.php?invite=' . urlencode($email->getValue('email')));
    echo h($email->getValue('id')) . ". " . h($email->getValue('email'));
    if ($account) {
        echo " has account";
    } else if ($email->getValue('invited_time')) {
        echo " invited on ".h($email->getValue('invited_time'));
        echo " <a href='".$invite_href."'>Invite again</a>";
    } else {
        echo " <a href='".$invite_href."'>Invite</a>";
    }
    echo "<br />";
}
echo "Script ran";
?>

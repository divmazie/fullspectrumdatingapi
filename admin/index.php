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

// The login itself is enforced by Apache (an .htaccess password on this directory, set up on the server).
// Apache sets REMOTE_USER once someone has logged in, so refuse to run if it's missing, e.g. the .htaccess was lost
if (empty($_SERVER['REMOTE_USER']) && empty($_SERVER['REDIRECT_REMOTE_USER'])) {
    http_response_code(403);
    die('Not authorized: admin/ must be password protected with .htaccess');
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

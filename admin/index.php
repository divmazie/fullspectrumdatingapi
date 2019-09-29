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

if (isset($_GET['invite'])) {
    $signup_email = SignupEmail::getEmailObject($_REQUEST['invite']);
    if ($signup_email->invite()) {
        echo "Invite sent to " . $_REQUEST['invite'] . "<br /><br />";
    } else {
        echo "Something went wrong emailing " . $_REQUEST['invite'] . "<br /><br />";
    }
}

$emails = SignupEmail::getAllSignupEmails();

foreach ($emails as $email) {
    $account = Account::getAccountObj($email->getValue('email'));
    // echo json_encode($account);
    echo $email->getValue('id') . ". " . $email->getValue('email');
    if ($account) {
        echo " has account";
    } else if ($email->getValue('invited_time')) {
        echo " invited on ".$email->getValue('invited_time');
        echo " <a href='index.php?invite=".$email->getValue('email')."'>Invite again</a>";
    } else {
        echo " <a href='index.php?invite=".$email->getValue('email')."'>Invite</a>";
    }
    echo "<br />";
}
echo "Script ran";
?>


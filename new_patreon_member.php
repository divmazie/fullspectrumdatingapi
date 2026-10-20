<?php
/**
 * Created by PhpStorm.
 * User: davidamackenzie
 * Date: 10/11/19
 * Time: 1:20 PM
 */

spl_autoload_register(function ($class_name) {
    include $class_name . '.php';
});

error_log('new_patreon_member.php touched');

// The shared key lives in config.php as 'patreon_webhook_key'. Without one, every request is refused
$configs = include('config.php');
$key = isset($configs['patreon_webhook_key']) ? $configs['patreon_webhook_key'] : '';

if ($key !== '' && !empty($_POST['email']) && isset($_POST['key']) && is_string($_POST['key'])
    && hash_equals($key, $_POST['key'])) {
    $email = $_POST['email'];
    $signupEmail = SignupEmail::newSignupEmail($email);
    $signupEmail->invite();
    $signupEmail->saveToDB();
    error_log('Invite sent to '.$email);
}
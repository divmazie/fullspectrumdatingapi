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

$key = 'FD1409BC355C9AB81A24E62452164B81B1724C76B8CF17EDE065CFACF7C3483D';

if ($_POST['email'] && $_POST['key'] && $_POST['key']==$key) {
    $email = $_POST['emai'];
    $signupEmail = SignupEmail::newSignupEmail($email);
    $signupEmail->invite();
}
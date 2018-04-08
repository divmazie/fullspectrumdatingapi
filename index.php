<?php
spl_autoload_register(function ($class_name) {
    include $class_name . '.php';
});

$allowed_origins = ["http://fullspectrumdating.com","http://localhost:4200"];

header('Content-Type: application/json');
if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'],$allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
}
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
$response = ['status' => 0];

$request = json_decode($_POST['request']);
$resource = explode('/',$request->resource);
$data = $request->data;

if ($resource[0]=='signup-emails') {
    if ($resource[1]=='get-all' && false) {
        $emails = SignupEmail::getAllSignupEmails();
        $return_vals = [];
        foreach ($emails as $email) {
            $return_vals[] = $email->getValues();
        }
        $response['status'] = 1;
        $response['data'] = $return_vals;
    } else if ($resource[1]=='save') {
        $email = $data;
        $emailObj = SignupEmail::getEmailObject($email);
        if (!$emailObj) {
            $emailObj = SignupEmail::newSignupEmail($email);
            $success = $emailObj->saveToDB(true);
            $response['status'] = $success ? 1 : 0;
        } else {
            $response['errorMessage'] = 'That email is already signed up!';
        }
    } else {
        $response['errorMessage'] = 'Unrecognized resource';
    }
} else {
    $response['errorMessage'] = 'Unrecognized resource';
}

echo json_encode($response);
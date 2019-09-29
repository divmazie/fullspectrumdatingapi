<?php
spl_autoload_register(function ($class_name) {
    include $class_name . '.php';
});

$allowed_origins = ["http://fullspectrumdating.com","http://localhost:4200","http://humanforhuman.net","https://humanforhuman.net"];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'],$allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
}
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Content-Type: application/json');
$response = ['status' => 0];

error_log('Index touched');

$request = json_decode($_POST['request']);
$resource = explode('/',$request->resource);
$data = $request->data;
$session_info = $request->session_info;

$ip_address = $_SERVER['REMOTE_ADDR'];
$api = new Api($resource,$data,$ip_address,$session_info);
$response = $api->getResponse();

echo json_encode($response);

function debug_die($object) {
    echo json_encode($object);
    die;
}
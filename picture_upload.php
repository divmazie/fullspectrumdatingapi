<?php
/**
 * Created by PhpStorm.
 * User: davidamackenzie
 * Date: 9/24/19
 * Time: 7:19 PM
 */
spl_autoload_register(function ($class_name) {
    include $class_name . '.php';
});

$allowed_origins = ["http://fullspectrumdating.com","http://localhost:4200","http://humanforhuman.net"];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'],$allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
}
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Content-Type: application/json');

// error_log(json_encode($_FILES));
$formName = 'file0';

$authenticated = false;
$session = null;
if (isset($_GET['token'])) {
    $hash = $_GET['token'];
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $session = Session::getByHash($hash);
    if ($session->isValid($hash,$ip_address)) {
        $authenticated = true;
    }
}
if (!$authenticated) {
    die('Session not authenticated!');
}

$target_dir = "pictures/";
$target_file = $target_dir . md5(basename($_FILES[$formName]["name"]).date('c')).".".pathinfo($_FILES[$formName]["name"],PATHINFO_EXTENSION);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
// Check if image file is a actual image or fake image
if(isset($_POST["submit"])) {
    $check = getimagesize($_FILES[$formName]["tmp_name"]);
    if($check !== false) {
        echo "File is an image - " . $check["mime"] . ".";
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }
}
// Check if file already exists
if (file_exists($target_file)) {
    echo "Sorry, file already exists.";
    $uploadOk = 0;
}
// Check file size
if ($_FILES[$formName]["size"] > 20000000) {
    echo "Sorry, your file is too large.";
    $uploadOk = 0;
}
// Allow certain file formats
if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
    echo "Sorry, only JPG, JPEG, & PNG  files are allowed.";
    $uploadOk = 0;
}
// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
} else {
    $profile = Profile::getObjectById($session->getValue('profile'),Profile::class);
    if ($profile && move_uploaded_file($_FILES[$formName]["tmp_name"], $target_file)) {
        $profile->setValue('picture_file',basename($target_file),true);
        echo "The file ". basename( $_FILES[$formName]["name"]). " has been uploaded.";
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}
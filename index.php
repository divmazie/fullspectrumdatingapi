<?php
/**
 * Created by PhpStorm.
 * User: DAM
 * Date: 1/18/18
 * Time: 20:26
 */

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Origin: http://fullspectrumdating.com");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

$data = new stdClass();
$data->foo = 'bar!';
$data->test = 'me!';
//$data = ['foo' => 'bar', 'test' =>'me'];

$configs = include_once('config.php');

//echo json_encode($configs);

$servername = $configs['servername'];
$username = $configs["username"];
$password = $configs["password"];
$dbname = $configs['dbname'];

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
//echo "Connected successfully<br />";

$sql = "SELECT * FROM signup_emails";
$result = $conn->query($sql);

$array = [];

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        //echo $row['email'];
        $array[] = $row['email'];
    }
    echo json_encode($array);
} else {
    echo json_encode($result);
}

$conn->close();
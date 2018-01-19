<?php
/**
 * Created by PhpStorm.
 * User: DAM
 * Date: 1/18/18
 * Time: 20:26
 */

$data = new stdClass();
$data->foo = 'bar!';
$data->test = 'me!';
//$data = ['foo' => 'bar', 'test' =>'me'];

echo json_encode($data);
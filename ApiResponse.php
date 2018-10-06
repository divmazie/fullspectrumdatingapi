<?php
/**
 * Created by PhpStorm.
 * User: davidamackenzie
 * Date: 9/29/18
 * Time: 2:44 PM
 */

class ApiResponse {
    public $status, $errorMessage, $data;

    function __construct() {
        $this->status = 0;
        $this->errorMessage = '';
        $this->data = [];
    }

    public function getResponse() {
        $response = [
            'status'=>$this->status,
            'errorMessage'=>$this->errorMessage,
            'data'=>$this->data
        ];
        return $response;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function setErrorMessage($message) {
        $this->errorMessage = $message;
    }

    public function setData($data) {
        $this->data = $data;
    }
}
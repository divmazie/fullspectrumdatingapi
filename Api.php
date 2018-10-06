<?php
/**
 * Created by PhpStorm.
 * User: davidamackenzie
 * Date: 9/29/18
 * Time: 2:40 PM
 */

class Api {
    private $resource;
    private $passedData;
    private $response;

    function __construct($resource,$passedData) {
        $this->resource = $resource;
        $this->passedData = $passedData;
        $this->response = new ApiResponse();
    }

    public function getResponse() {
        $nextResource = array_slice($this->resource,1,null,true);
        switch ($this->resource[0]) {
            case 'signup-emails': $this->signupEmailsResource($nextResource); break;
            case 'accounts': $this->accountsResource($nextResource); break;
            default: $this->setError('Unrecognized resource'); break;
        }
        return $this->response->getResponse();
    }

    private function setError($message) {
        $this->response->setStatus(0);
        $this->response->setErrorMessage($message);
    }

    private function signupEmailsResource($resource) {
        switch ($resource[0]) {
            case 'get-all': $this->signupEmailsGetAll(); break;
            case 'save': $this->signupEmailSave(); break;
            case 'get-by-signupid': $this->signupEmailGetBySignupId(); break;
            default: $this->setError('Unrecognized resource'); break;
        }
    }

    private function accountsResource($resource) {
        switch ($resource[0]) {
            case 'create': $this->accountCreate(); break;
            case 'signin': $this->accountSignin(); break;
            default: $this->setError('Unrecognized resource'); break;
        }
    }

    private function signupEmailsGetAll() {
        $emails = SignupEmail::getAllSignupEmails();
        $return_vals = [];
        foreach ($emails as $email) {
            $return_vals[] = $email->getValues();
        }
        $this->response->setStatus(1);
        $this->response->setData($return_vals);
    }

    private function signupEmailSave() {
        $email = $this->passedData;
        $emailObj = SignupEmail::getEmailObject($email);
        if (!$emailObj) {
            $emailObj = SignupEmail::newSignupEmail($email);
            $success = $emailObj->saveToDB(true);
            $this->response->setStatus($success ? 1 : 0);
        } else {
            $this->setError('That email is already signed up!');
        }
    }

    private function signupEmailGetBySignupId() {
        $signupid = $this->passedData;
        $emailObj = SignupEmail::getEmailObjectBySignupid($signupid);
        $return_vals = $emailObj->getValues();
        $this->response->setStatus(1);
        $this->response->setData($return_vals);
    }

    private function accountCreate() {
        $email = $this->passedData->email;
        $password_hash = $this->passedData->password_hash;
        $accountObj = Account::getAccountObj($email);
        if (!$accountObj) {
            $accountObj = Account::newAccount($email,$password_hash);
            $success = $accountObj->saveToDB(true);
            $this->response->setStatus($success ? 1 : 0);
        } else {
            $this->setError('Account already exists');
        }
    }

    private function accountSignin() {
        $email = $this->passedData->email;
        $password_hash = $this->passedData->password_hash;
        $accountObj = Account::getAccountObj($email);
        if ($accountObj) {
            $authentic = $accountObj->authenticate($password_hash);
            $this->response->setStatus($authentic ? 1 : 0);
            if (!$authentic) {
                $this->setError('Incorrect password');
            }
        }
    }

}
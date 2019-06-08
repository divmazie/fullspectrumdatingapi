<?php

class SignupEmail extends DBObject {

    static $tableName = 'signup_emails';
    static $columns = [
        'id'=>['name'=>'id','primary_key'=>true],
        'email'=>['name'=>'email'],
        'signup_time'=>['name'=>'signup_time'],
        'invited_time'=>['name'=>'invited_time']
    ];

    static function getColumns() {
        return self::$columns;
    }

    static function getTableName() {
        return self::$tableName;
    }

    static function getAllSignupEmails() {
        $db = DBConnectionFactory::Instance();
        $records = $db->select(SignupEmail::$tableName);
        $emails = [];
        foreach($records as $record) {
            $emails[] = new SignupEmail($record,true);
        }
        return $emails;
    }

    static function newSignupEmail($email) {
        $email = strtolower($email);
        $values = ['email'=>$email];
        $emailObj = new SignupEmail($values,false);
        return $emailObj;
    }

    static function getEmailObject($email) {
        $db = DBConnectionFactory::Instance();
        $email = strtolower($email);
        $values = [self::$columns['email']['name'] => $email];
        $records = $db->select(SignupEmail::$tableName,$values);
        $emailObject = false;
        if (count($records)>=1) {
            $emailObject = new SignupEmail($records[0],true);
        }
        return $emailObject;
    }

    static function getEmailObjectBySignupid($signupid) {
        $db = DBConnectionFactory::Instance();
        $values = [self::$columns['id']['name'] => $signupid];
        $records = $db->select(SignupEmail::$tableName,$values);
        $emailObject = false;
        if (count($records)>=1) {
            $emailObject = new SignupEmail($records[0],true);
        }
        return $emailObject;
    }

}
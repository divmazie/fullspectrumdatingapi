<?php

class SignupEmail extends DBObject {

    static $tableName = 'signup_emails';
    static $columns = [
        'id'=>['name'=>'id','primary_key'=>true],
        'email'=>['name'=>'email'],
        'signup_time'=>['name'=>'signup_time'],
        'invited_time'=>['name'=>'invited_time'],
        'invite_code'=>['name'=>'invite_code']
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

    static function getByInviteCode($invite_code) {
        $db = DBConnectionFactory::Instance();
        $values = [self::$columns['invite_code']['name'] => '*'.$invite_code];
        $records = $db->select(SignupEmail::$tableName,$values);
        $emailObject = false;
        if (count($records)>=1) {
            $emailObject = new SignupEmail($records[0],true);
        }
        return $emailObject;
    }

    public function invite() {
        $this->setInviteCode();
        $emailer = EmailSenderFactory::Instance();
        if ($emailer->sendInviteEmail($this)) {
            $this->setValue('invited_time', date('Y-m-d G:i:s'), true);
            return true;
        } else {
            return false;
        }
    }

    public function setInviteCode() {
        $this->setValue('invite_code','*'
            .md5($this->getValue('id').$this->getValue('email').$this->getValue('signup_time')),true);
    }

}
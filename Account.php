<?php
/**
 * Created by PhpStorm.
 * User: davidamackenzie
 * Date: 8/15/18
 * Time: 7:09 PM
 */

class Account extends DBObject {

    static $tableName = 'accounts';
    static $columns = [
        'id'=>['name'=>'id','primary_key'=>true],
        'email'=>['name'=>'email'],
        'creation_time'=>['name'=>'creation_time'],
        'signupEmail_id'=>['name'=>'SignupEmail_id','foreign_key'=>true],
        'password_hash'=>['name'=>'password_hash']
    ];

    static function getColumns() {
        return self::$columns;
    }

    static function getTableName() {
        return self::$tableName;
    }

    static function getAccountObj($email) {
        $db = DBConnectionFactory::Instance();
        $email = strtolower($email);
        $values = [self::$columns['email']['name'] => $email];
        $records = $db->select(Account::$tableName,$values);
        $emailObject = false;
        if (count($records)>=1) {
            $emailObject = new Account($records[0],true);
        }
        return $emailObject;
    }

    static function newAccount($email,$password_hash,$signupEmail_id=null) {
        $email = strtolower($email);
        $values = ['email'=>$email,'password_hash'=>$password_hash,'signupEmail_id'=>$signupEmail_id];
        $emailObj = new Account($values,false);
        return $emailObj;
    }

    public function authenticate($password_hash) {
        return $password_hash == $this->getValue('password_hash');
    }

}
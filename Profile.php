<?php
/**
 * Created by PhpStorm.
 * User: davidamackenzie
 * Date: 6/29/19
 * Time: 3:21 PM
 */

class Profile extends DBObject
{
    static $tableName = 'profiles';
    static $columns = [
        'id'=>['name'=>'id','primary_key'=>true],
        'account_id'=>['name'=>'account_id', 'foreign_key'=>true, 'foreign_table' => Account::class],
        'preferred_name'=>['name'=>'preferred_name'],
        'birthday'=>['name'=>'birthday'],
        'picture_file'=>['name'=>'picture_file'],
        'contact'=>['name'=>'contact'],
        'bioline'=>['name'=>'bioline'],
        'bio1'=>['name'=>'bio1'],
        'bio2'=>['name'=>'bio2'],
        'bio3'=>['name'=>'bio3']
    ];
    static function getColumns() {
        return self::$columns;
    }
    static function getTableName() {
        return self::$tableName;
    }

    static function newProfie($account) {
        $account_id = $account->getValue('id');
        $values = [Profile::getColumns()['account_id']['name']=>$account_id];
        $obj = new Profile($values,false);
        return $obj;
    }

    static function getProfileByAccount($account) {
        $db = DBConnectionFactory::Instance();
        $values = [Profile::getColumns()['account_id']['name'] => $account->getValue('id')];
        $results = $db->select(Profile::getTableName(),$values);
        //error_log(json_encode($results));
        if (count($results)>0) {
            $profileObj = new Profile($results[0]);
            return $profileObj;
        }
        return false;
    }

    static function getMatches() {
        $db = DBConnectionFactory::Instance();
        $matches = [];
        $results = $db->select(Profile::getTableName());
        foreach ($results as $row) {
            $profileObj = new Profile($row);
            $matches[] = $profileObj;
        }
        return $matches;
    }


}
<?php
/**
 * Created by PhpStorm.
 * User: davidamackenzie
 * Date: 11/11/18
 * Time: 6:16 PM
 */

class Session extends DBObject
{

    static $tableName = 'sessions';
    static $columns = [
        'id' => ['name' => 'id', 'primary_key' => true],
        'account' => ['name' => 'account', 'foreign_key' => true, 'foreign_table' => Account::class],
        'profile' => ['name' => 'profile', 'foreign_key' => true, 'foreign_table' => Profile::class],
        'signon_time' => ['name' => 'signon_time'],
        'last_action_time' => ['name' => 'last_action_time'],
        'ip_address' => ['name' => 'ip_address'],
        'session_hash' => ['name' => 'session_hash'],
        'active' => ['name' => 'active']
    ];

    static function getColumns() {
        return self::$columns;
    }

    static function getTableName()
    {
        return self::$tableName;
    }

    static function getSessionObj($session_id) {
        $db = DBConnectionFactory::Instance();
        $values = [self::$columns['id']['name'] => $session_id];
        $records = $db->select(Session::$tableName,$values);
        $sessionObj = false;
        if (count($records)>=1) {
            $sessionObj = new Session($records[0],true);
        }
        return $sessionObj;
    }

    static function getByHash($hash) {
        $db = DBConnectionFactory::Instance();
        $values = [self::$columns['session_hash']['name'] => $hash];
        $records = $db->select(Session::$tableName,$values);
        $sessionObj = false;
        if (count($records)>=1) {
            $sessionObj = new Session($records[0],true);
        }
        return $sessionObj;
    }

    public function isValid($hash,$ip_address) {
        return ($this->getValue('session_hash') === $hash
            && $this->getValue('ip_address') === $ip_address
            && $this->getValue('active') === '1');
    }


}
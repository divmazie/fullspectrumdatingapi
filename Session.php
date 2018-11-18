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
        'account' => ['name' => 'account', 'foreign_key' => true],
        'signon_time' => ['name' => 'signon_time'],
        'last_action_time' => ['name' => 'last_action_time'],
        'ip_address' => ['name' => 'ip_address'],
        'session_hash' => ['name' => 'session_hash'],
        'active' => ['name' => 'active']
    ];

    protected function getColumnNames()
    {
        $names = [];
        foreach (self::$columns as $column) {
            $names[] = $column['name'];
        }
        return $names;
    }

    protected function getTableName()
    {
        return self::$tableName;
    }

    protected function getPrimaryKey()
    {
        foreach (self::$columns as $column) {
            if ($column['primary_key']) {
                return $column['name'];
            }
        }
        return false;
    }



}
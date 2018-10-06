<?php
/**
 * Created by PhpStorm.
 * User: davidamackenzie
 * Date: 9/29/18
 * Time: 2:17 PM
 */

class Dimension extends DBObject {

    static $tableName = 'dimensions';
    static $columns = [
        'id'=>['name'=>'id','primary_key'=>true],
        'name'=>['name'=>'name'],
        'category'=>['name'=>'category','foreign_key'=>true,'foreign_table'=>DimensionCategory::class],
        'default_order'=>['name'=>'default_order']
    ];

    protected function getColumnNames() {
        $names = [];
        foreach(self::$columns as $column) {
            $names[] = $column['name'];
        }
        return $names;
    }

    protected function getTableName() {
        return self::$tableName;
    }

    protected function getPrimaryKey() {
        foreach(self::$columns as $column) {
            if ($column['primary_key']) {
                return $column['name'];
            }
        }
        return false;
    }


}
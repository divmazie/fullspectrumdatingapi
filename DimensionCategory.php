<?php
/**
 * Created by PhpStorm.
 * User: davidamackenzie
 * Date: 9/29/18
 * Time: 2:17 PM
 */

class DimensionCategory extends DBObject {

    static $tableName = 'dimension_categories';
    static $columns = [
        'id'=>['name'=>'id','primary_key'=>true],
        'name'=>['name'=>'name'],
        'color'=>['name'=>'color'],
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

    static function getDimensionCategories() {
        $db = DBConnectionFactory::Instance();
        $records = $db->select(DimensionCategory::$tableName);
        $dimension_categories = [];
        foreach($records as $record) {
            $dimension_categories[] = new DimensionCategory($record,true);
        }
        return $dimension_categories;
    }


}
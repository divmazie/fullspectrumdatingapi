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

    static function getColumns() {
        return self::$columns;
    }

    static function getTableName() {
        return self::$tableName;
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
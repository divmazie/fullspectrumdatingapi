<?php
/**
 * Created by PhpStorm.
 * User: davidamackenzie
 * Date: 9/29/18
 * Time: 2:17 PM
 */

class Preference extends DBObject {

    static $tableName = 'preferences';
    static $columns = [
        'id'=>['name'=>'id','primary_key'=>true],
        'profile_id'=>['name'=>'profile_id','foreign_key'=>true,'foreign_table'=>Profile::class],
        'dimension_id'=>['name'=>'dimension_id','foreign_key'=>true,'foreign_table'=>Dimension::class],
        'yesNo'=>['name'=>'yesNo'],
        'slider'=>['name'=>'slider'],
        'updated'=>['name'=>'updated']
    ];

    static function getColumns() {
        return self::$columns;
    }

    static function getTableName() {
        return self::$tableName;
    }

    static function getAllPreferences($profile_id,$dimensions) {
        $db = DBConnectionFactory::Instance();
        $values = [self::$columns['profile_id']['name']=>$profile_id];
        $records = $db->select(Preference::$tableName,$values);
        $preferences = [];
        $preference_lookup = [];
        foreach($records as $record) {
            $preference = new Preference($record,true);
            $preferences[] = $preference;
            $preference_lookup[$preference->getValue('dimension_id')] = $preference;
        }
        foreach ($dimensions as $dimension) {
            $dimension_id = $dimension['id'];
            if (!array_key_exists($dimension_id,$preference_lookup)) {
                $values = ['profile_id'=>$profile_id,
                            'dimension_id'=>$dimension_id,
                            'yesNo'=>0,
                            'slider'=>1];
                $preference = new Preference($values,false);
                //error_log(json_encode($preference->getValues()));
                $preference->saveToDB();
                $preferences[] = $preference;
                $preference_lookup[$dimension_id] = $preference;
            }
        }
        return $preferences;
    }

}
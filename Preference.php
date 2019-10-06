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

    static function getAllPreferences($profile_id,$dimensions=false) {
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
        if (!$dimensions) {
            $dimensions = Dimension::getDimensions();
        }
        foreach ($dimensions as $dimension) {
            $dimension_id = $dimension->getValue('id');
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
        // THIS IS HACK! ORM should be robust enough to not create dupes.
        Preference::deleteDuplicates();
        return $preferences;
    }

    public function getDimension() {
        return Dimension::getObjectById($this->getValue('dimension_id'),Dimension::class);
    }

    public function vectorValue() {
        return $this->getValue('yesNo')*$this->getValue('slider');
    }

    private static function deleteDuplicates() {
        $db = DBConnectionFactory::Instance();
        $sql = "DELETE FROM preferences
            WHERE id NOT IN (
                SELECT *
                FROM (
                    SELECT MIN(id) id
                    FROM preferences
                    GROUP BY dimension_id, profile_id
                ) temp
            )";
        $db->runQuery($sql);
    }

}
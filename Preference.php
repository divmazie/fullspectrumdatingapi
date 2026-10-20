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
        $preferences = [];
        $existing = [];
        foreach ($db->select(Preference::$tableName,$values) as $record) {
            $preferences[] = new Preference($record,true);
            $existing[$record['dimension_id']] = true;
        }
        if (!$dimensions) {
            $dimensions = Dimension::getDimensions();
        }
        $missing = [];
        foreach ($dimensions as $dimension) {
            if (!isset($existing[$dimension->getValue('id')])) {
                $missing[] = '(' . (int)$profile_id . ',' . (int)$dimension->getValue('id') . ',0,1)';
            }
        }
        if ($missing) {
            // Unanswered rows for every new dimension. IGNORE skips rows a concurrent request created first
            // (the unique profile_id/dimension_id key refuses them), then re-read to get everyone's real ids
            $db->runQuery("INSERT IGNORE INTO preferences (profile_id, dimension_id, yesNo, slider) VALUES " . implode(',', $missing));
            $preferences = [];
            foreach ($db->select(Preference::$tableName,$values) as $record) {
                $preferences[] = new Preference($record,true);
            }
        }
        return $preferences;
    }

    public function getDimension() {
        return Dimension::getObjectById($this->getValue('dimension_id'),Dimension::class);
    }

    public function vectorValue() {
        return $this->getValue('yesNo')*$this->getValue('slider');
    }

}
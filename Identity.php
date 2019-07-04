<?php
/**
 * Created by PhpStorm.
 * User: davidamackenzie
 * Date: 9/29/18
 * Time: 2:17 PM
 */

class Identity extends DBObject {

    static $tableName = 'identities';
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

    static function getAllIdentities($profile_id,$dimensions=false) {
        $db = DBConnectionFactory::Instance();
        $values = [self::$columns['profile_id']['name']=>$profile_id];
        $records = $db->select(Identity::$tableName,$values);
        $identities = [];
        $identity_lookup = [];
        foreach($records as $record) {
            $identity = new Identity($record,true);
            $identities[] = $identity;
            $identity_lookup[$identity->getValue('dimension_id')] = $identity;
        }
        if (!$dimensions) {
            $dimensions_objs = Dimension::getDimensions();
            $dimensions = [];
            foreach ($dimensions_objs as $dim) {
                $dimensions[] = $dim->getValues();
            }
        }
        foreach ($dimensions as $dimension) {
            $dimension_id = $dimension['id'];
            if (!array_key_exists($dimension_id,$identity_lookup)) {
                $values = ['profile_id'=>$profile_id,
                            'dimension_id'=>$dimension_id,
                            'yesNo'=>0,
                            'slider'=>1];
                $identity = new Identity($values,false);
                //error_log(json_encode($identity->getValues()));
                $identity->saveToDB();
                $identities[] = $identity;
                $identity_lookup[$dimension_id] = $identity;
            }
        }
        return $identities;
    }

    public function getDimension() {
        $dim = Dimension::getObjectById($this->getValue('dimension_id'),Dimension::class);
        return $dim;
    }

}
<?php
/**
 * Created by PhpStorm.
 * User: davidamackenzie
 * Date: 9/29/18
 * Time: 2:17 PM
 */

class Identity extends DBObject {

    static $tableName = 'dimensions';
    static $columns = [
        'id'=>['name'=>'id','primary_key'=>true],
        'account_id'=>['name'=>'account_id','foreign_key'=>true,'foreign_table'=>Account::class],
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

    static function getAllIdentities($account_id,$dimensions) {
        $db = DBConnectionFactory::Instance();
        $values = [self::$columns['account_id']['name']=>$account_id];
        $records = $db->select(Identity::$tableName,$values);
        $identities = [];
        $identity_lookup = [];
        foreach($records as $record) {
            $identity = new Identity($record,true);
            $identities[] = $identity;
            $identity_lookup[$identity->getValue('dimension_id')] = $identity;
        }
        foreach ($dimensions as $dimension) {
            $dimension_id = $dimension['id'];
            if (!array_key_exists($dimension_id,$identity_lookup)) {
                $values = ['account_id'=>$account_id,
                            'dimension_id'=>$dimension_id,
                            'yesNo'=>0,
                            'slider'=>1];
                $identity = new Identity($values,false);
                $identity->saveToDB();
                $identities[] = $identity;
                $identity_lookup[$dimension_id] = $identity;
            }
        }
        return $identities;
    }

}
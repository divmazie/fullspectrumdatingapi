<?php

abstract class DBObject {

    abstract protected function getColumnNames();
    abstract protected function getTableName();
    abstract protected function getPrimaryKey();

    protected $db, $values, $inDB, $syncedToDB;

    function __construct($record,$fromDB) {
        $this->db = DBConnectionFactory::Instance();
        $this->setValues($record,$fromDB);
    }

    private function setValues($values,$fromDB) {
        $this->values = [];
        foreach($this->getColumnNames() as $column) {
            if (isset($values[$column])) {
                $this->values[$column] = $values[$column];
            } else {
                $this->values[$column] = null;
            }
        }
        $this->inDB = $fromDB;
        $this->syncedToDB = $fromDB;
    }

    private function check_column_names($values) {
        foreach($values as $key=>$val) {
            if (!in_array($key,$this->getColumnNames())) {
                return false;
            }
        }
        return true;
    }

    public function getValues() {
        return $this->values;
    }

    public function getValue($column) {
        if (isset($this->values[$column])) {
            return $this->values[$column];
        } else if (in_array($column,$this->getColumnNames())) {
            return null;
        } else {
            return false;
        }
    }

    public function setValue($column,$value,$syncToDB=false) {
        if (in_array($column,$this->getColumnNames())) {
            $this->values[$column] = $value;
            $this->syncedToDB = false;
        }
        if ($syncToDB) {
            $this->updateDB();
        }
    }

    public function saveToDB($skipUpdate=false) {
        $primarykey = $this->getPrimaryKey();
        if ($this->inDB) {
            if (!$this->syncedToDB) {
                return $this->updateDB();
            }
        } else if ($this->getValue($primarykey)==null) {
            return $this->insertIntoDB($skipUpdate);
        }
    }

    protected function insertIntoDB($skipUpdate=false) {
        if ($this->check_column_names($this->values)) {
            $this->db->insert($this->getTableName(),$this->values);
            $last_id = $this->db->lastInsertId();
            $primary_key = $this->getPrimaryKey();
            $this->setValue($primary_key,$last_id);
            if (!$skipUpdate===true) {
                $records = $this->db->select($this->getTableName(),[$primary_key => $last_id]);
                if (count($records)==1) {
                    $this->setValues($records[0],true);
                    return true;
                }
            }
            return true;
        }
        return false;
    }

    protected function updateDB() {
        $primarykey = $this->getPrimaryKey();
        if ($this->check_column_names($this->values) && $this->getValue($primarykey)) {
            $primary = ['key'=>$primarykey, 'value'=>$this->getValue($primarykey)];
            $this->db->update($this->getTableName(),$primary,$this->values);
            $this->syncedToDB = true;
        }
    }
}
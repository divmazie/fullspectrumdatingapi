<?php

final class DBConnectionFactory {

    public static function Instance() {
        static $inst = null;
        if ($inst===null) {
            $inst = new DBConnectionFactory();
        }
        return $inst;
    }

    private $conn;

    function __construct() {
        $configs = include('config.php');

        $servername = $configs['servername'];
        $username = $configs["username"];
        $password = $configs["password"];
        $dbname = $configs['dbname'];

        $this->conn = new mysqli($servername, $username, $password, $dbname);

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    function __destruct() {
        $this->conn->close();
    }

    private function escape($string) {
        return mysqli_real_escape_string($this->conn,$string);
    }

    public function select($table,$wheres=false) {
        $table = $this->escape($table);
        $sql = "SELECT * FROM $table ";
        if ($wheres) {
            $sql .= "WHERE ";
            foreach($wheres as $key=>$val) {
                $sql .= $this->escape($key)." = '".$this->escape($val)."' ";
            }
        }
        $result = $this->conn->query($sql);
        $this->logError();
        $rows = [];
        while($result && $row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function insert($table,$values) {
        $table = $this->escape($table);
        $sql = "INSERT INTO $table (";
        $first = true;
        foreach ($values as $key=>$val) {
            if (!empty($val)) {
                $sql .= ($first ? '' : ',') . $this->escape($key);
                $first = false;
            }
        }
        $sql .= ') VALUES (';
        $first = true;
        foreach ($values as $key=>$val) {
            if (!empty($val)) {
                $sql .= ($first ? '' : ',') . "'".$this->escape($val)."'";
                $first = false;
            }
        }
        $sql .= ')';
        //error_log('SQL: '.$sql);
        $result = $this->conn->query($sql);
        $this->logError();
        return $result;
    }

    public function update($table,$primary,$values) {
        $table = $this->escape($table);
        $sql = "UPDATE $table SET ";
        $first = true;
        foreach ($values as $key=>$val) {
            if (isset($val) && $key!=$primary['key']) {
                $sql .= ($first ? '' : ', ') . $this->escape($key) . "='" . $this->escape($val) . "'";
                $first = false;
            }
        }
        $sql .= " WHERE ".$this->escape($primary['key'])."=".$this->escape($primary['value']);
        $result = $this->conn->query($sql);
        $this->logError();
        return $result;
    }

    // DON'T RUN WITHOUT SANITIZING INPUTS!!!
    public function runQuery($sql) {
        // $query = $this->escape();
        $result = $this->conn->query($sql);
        $this->logError();
        return $result;
    }

    public function lastInsertId() {
        return $this->conn->insert_id;
    }

    private function logError() {
        if (mysqli_error($this->conn) !== "") {
            error_log("SQL error: " . mysqli_error($this->conn));
        }
    }

}
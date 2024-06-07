<?php
class DB extends MyView
{

    private $link;


    function __construct($isReadOnly = false)
    {
        
        try {
            $this->link = $this->connect();
        } catch (Exception $e) {
            $this->logErrors("Connect error", $e->getMessage(), array());
        }

        if (!$this->link) {
            $this->logErrors("Connect error", "mysqli_connect", array());
        }
        
    }

    function __destruct()
    {
        $this->close();
    }

    public function getDBConnection()
    {
        return $this->link;
    }

    public function connect($server = DBHOST, $username = DBUSER, $password = DBPASS, $database = DBNAME)
    {
        $this->link = mysqli_connect($server, $username, $password, $database);
        if (!$this->link) {
            $this->logErrors("DB connect error", "mysqli_connect", array($server, $username, $database));
            return false;
        }
        //$this->query("SET time_zone='" . TIMEZONE_SQL . "'", array());
        //$this->query("SET NAMES utf8mb4", array());
        return $this->link;
    }
    //i-integer d=double s=string b=blog
    public function query_to_array($query, $params = array())
    {

        $ret = array();
        try {
            $stmt = $this->query($query, $params);
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                array_push($ret, $row);
            }

            $stmt->close();
        } catch (Exception $e) {
            $this->logErrors($this->link->error, $query, $params);
        }

        return $ret;
    }

    public function get_row($query, $params = array())
    {
        $ret = null;
        try {
            $stmt = $this->query($query, $params);
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $ret = $row;
        } catch (Exception $e) {
            $this->logErrors($this->link->error, $query, $params);
        }
        return $ret;
    }

    public function get_scalar($query, $params = array())
    {
        $ret = null;
        try {
            $stmt = $this->query($query, $params);
            if (!$stmt) {
                $this->logErrors($this->link->error, $query, $params);
            }
            $result = $stmt->get_result();
            $row = $result->fetch_array();
            $ret = isset($row[0]) ? $row[0] : null;
        } catch (Exception $e) {
            $this->logErrors($this->link->error, $query, $params);
        }
        return $ret;
    }

    public function query($query, $params = array())
    {
        $stmt = false;
        try {
            $stmt = $this->link->prepare($query);
            if ($stmt === false) {
                $this->logErrors($this->link->error, $query, $params);
            }
            if (!empty($params)) {

                $rslt = $stmt->bind_param(...$params);
                if ($rslt === false) {
                    $this->logErrors($this->link->error, $query, $params);
                }
            }
            $exec = $stmt->execute();
            if ($exec === false) {
                $this->logErrors($this->link->error, $query, $params);
            }
        } catch (Exception $e) {
            $this->logErrors($this->link->error, $query, $params);
        }
        return $stmt;
    }

    public function insert_id()
    {
        return mysqli_insert_id($this->link);
    }

    public function affected_rows()
    {
        return $this->link->affected_rows;
        return mysqli_affected_rows($this->link);
    }

    public function close()
    {
        if ($this->link) {
            return mysqli_close($this->link);
        } else {
            return true;
        }
    }

    function logErrors($err, $query, $params = array())
    {
        $return = array('error' => $err, 'query' => $query, 'params' => $params);
        echo "<!--";
        p($return,false);
        echo "-->";
    }
}

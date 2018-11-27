<?php

require_once(dirname(__FILE__)."/config.class.php");
require_once(dirname(__FILE__)."/lists.class.php");

interface iSqlDataBase {

    /**
     * @return mixed
     */
    public function getConnect();

}

class DBException extends Exception {
    public function __construct($mess) {
        parent::__construct($mess);
        error_log($this->__toString(), 0);
    }
}

class connectDBException extends DBException {

    /**
     * ���������� �������� ������ ����������� � ���� � ���� html ��� ������ �� ��������
     * @return string
     */
    public function getErrorInfoHTML() {
        $txt = "<h1>���� " . $_SERVER['SERVER_NAME'] . "</h1>";
        $txt .= "<h2>�� ���� ������������ � ���� �����.</h2>";
        $txt .= "<h3>" . $this->GetMessage() . "</h3>";
        return $txt;
    }
}

class querySelectDBException extends DBException {
    /**
     * ���������� �������� ������ ���������� SELECT ������� � ���� html ��� ������ �� ��������
     * @return string
     */
    public function getErrorInfoHTML() {
        $txt = "<h1>�� ���� �������� ������ �� ������ ���� ������.</h1>";
        $txt .= "<h2>".$this->GetMessage()."</h2>";
        return $txt;
    }
}

class otherDBException extends DBException {
    /**
     * ���������� �������� ������ ������ ���� html ��� ������ �� ��������
     * @return string
     */
    public function getErrorInfoHTML() {
        $txt = "<h1>������ ��� ������ � ����� ������</h1>";
        $txt .= "<h2>".$this->GetMessage()."</h2>";
        return $txt;
    }
}

class sqlDataBase implements iSqlDataBase {
	
	private static $db = null;
	private $dbConnect;

    /**
     * sqlDataBase constructor.
     * @param iConfigDB $configDB
     * @throws connectDBException
     */
    private function __construct(iConfigDB $configDB) {
		//error_reporting(sqlConfig::err_rep);
		$this->dbConnect=new mysqli($configDB->getHost(),
                                    $configDB->getUser(),
                                    $configDB->getPassword(),
                                    $configDB->getNameDB(),
                                    $configDB->getPort());
		if($this->dbConnect->connect_errno)
            throw new connectDBException($this->dbConnect->connect_error);
	}

    /**
     * �������� ���������� mysqli
     * @return mixed|mysqli
     */
    public function getConnect() {
        return $this->dbConnect;
    }

    /**
     * ������������ � ���� ������
     * @return null|sqlDataBase
     * @throws connectDBException
     */
    public static function Connect() {
		if (self::$db == null) {
		    $config = new sqlConfig();
		    self::$db = new sqlDataBase($config);
            unset($config);
        }
		return self::$db;
	}

    public function __destruct() {
        if ($this->dbConnect) $this->dbConnect->close();
    }

}

class queryDataBase {

    /**
     * ���������� ��������� ������� � ���� 2-� ������� �������������� �������
     * @param iSqlDataBase $conn
     * @param $query
     * @return array
     * @throws querySelectDBException
     */
    public static function getAll(iSqlDataBase $conn, $query) {
        $res = array();
        if ($resQ = self::getRaw($conn, $query)) {
            while ($row = $resQ->fetch_assoc()) {
                $res[] = $row;
            }
            $resQ->free();
        }
        return $res;
    }

    /**
     * ���������� ������ ������ ���������� ������� � ���� �������������� �������
     * @param iSqlDataBase $conn
     * @param $query
     * @return array
     * @throws otherDBException
     * @throws querySelectDBException
     */
    public static function getOne(iSqlDataBase $conn, $query) {
        $row = array();
        if ($resQ = self::getRaw($conn, $query)) {
            $row = $resQ->fetch_assoc();
            if (!is_array($row)) {
                throw new otherDBException("�� ���� ��������� ������� ������������� � ������");
            }
            $resQ->free();
        }
        return $row;
    }

    /**
     * ��������� "�����" ��������� ������� SELECT
     * @param iSqlDataBase $conn
     * @param $query
     * @return mixed
     * @throws querySelectDBException
     */
    private static function getRaw(iSqlDataBase $conn, $query) {
        $res = $conn->getConnect()->query($query, MYSQLI_USE_RESULT);
        if (!$res) {
            throw new querySelectDBException($conn->getConnect()->error);
        }
        return $res;
    }

}

class DB {

    /**
     * �������� ������ ���. ��������� � ���� �������������� ������� � ������������ � �������
     * @param Iterator|null $sel
     * @return array
     * @throws connectDBException
     * @throws querySelectDBException
     */
    static public function getListDevices(Iterator $sel = null){

        /**
        $query = "SELECT a.DeviceID, a.Adress, a.set_alarm, b.Title NetTitle, c.Title SensorType
				FROM tdevice a
				LEFT JOIN tnettype b ON a.NetTypeID = b.NetTypeID
				LEFT JOIN tsensortype c ON a.SensorTypeID = c.SensorTypeID";
        */

        $query = "SELECT * FROM tdevice";

        $con = sqlDataBase::Connect();

        if (!is_null($sel)) {
            if ($sel instanceof selectOption) {

                $w = "";

                foreach ($sel as $key => $value) {

                    if (!empty($w)) {
                        $w = $w." AND";
                    }

                    $realValue = $con->getConnect()->real_escape_string($value);
                    if (is_int($value)) {
                        $w = $w . " a.$key = $realValue";
                    }
                    else {
                        $w = $w . " a.$key = '$realValue'";
                    }

                }

                if (!empty($w)) {
                    $query = $query . " WHERE" . $w;
                }
            }
        }

        $aDevices = queryDataBase::getAll($con, $query);

        return $aDevices;

    }

}
?>

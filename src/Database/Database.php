<?php

namespace qpost\Database;

use mysqli;

class Database {
	/**
	 * @access private
	 * @var mysqli $db The mysqli object
	 */
	private $db = null;

	/**
	 * @access private
	 * @var bool $tried Has an attempt been made to connect?
	 */
	private $tried = false;

	/**
	 * @access private
	 * @var bool $connected Are we connected to the database?
	 */
	private $connected = false;

	/**
	 * Gets the current Database class instance
	 *
	 * @access public
	 * @return Database
	 */
	public static function Instance(){
		static $inst = null;
		if($inst == null){
			$inst = new self(MYSQL_HOST,MYSQL_USER,MYSQL_PASSWORD,MYSQL_DATABASE,MYSQL_PORT);
		}

		return $inst;
	}

	/**
	 * Constructor
	 *
	 * @access protected
	 * @param string $host The mysql host
	 * @param string $user The mysql user
	 * @param string $password The mysql password
	 * @param string $database The main mysql database
	 * @param int $port The mysql port
	 */
	protected function __construct($host,$user,$password,$database,$port = 3306){
		if($this->tried == false){
			$this->tried = true;
			$this->db = @new mysqli($host,$user,$password,$database,$port);
			mysqli_report(MYSQLI_REPORT_ERROR);

			if($this->db->connect_error){
				die("Connect Error: " . $this->db->connect_error);
			} else {
				$this->db->set_charset("utf8mb4");

				if(null !== TIMEZONE){
					$t = TIMEZONE;

					$s = $this->db->prepare("SET @@session.time_zone = ?");
					$s->bind_param("s",$t);
					$s->execute();
					$s->close();
				}

				$connected = true;
			}
		}
	}

	/**
	 * Destructor
	 *
	 * @access protected
	 */
	public function __destruct(){
		$this->shutdown();
	}

	/**
	 * Returns the mysqli object
	 *
	 * @return mysqli
	 */
	public function get(){
		return $this->db;
	}

	/**
	 * Returns whether there is currently a connection
	 *
	 * @return bool
	 */
	public function connected(){
		return $this->connected;
	}


	public function fetchAll(){
		if(func_num_args() >= 1){
			$query = func_get_arg(0);
			$arguments = array();

			for($i = 0; $i < func_num_args(); $i++){
				if($i == 0) continue;
				array_push($arguments,func_get_arg($i));
			}

			$mysqli = $this->db;
			$stmt = $mysqli->prepare($query);

			if(count($arguments) > 0){
				$s = "";

				foreach($arguments as $var){
					if(is_int($var)){
						$s .= "i";
					} else if(is_numeric($var)){
						$s .= "d";
					} else if(is_string($var)){
						$s .= "s";
					} else {
						$s .= "b";
					}
				}

				// TODO
				//$stmt->bind_param($s,)
			}

			if($stmt->execute()){
				$result = $stmt->get_result();

				if($result->num_rows){
					$rows = [];

					while($row = $result->fetch_assoc()){
						array_push($rows,$row);
					}

					return $rows;
				} else {
					return [];
				}
			} else {
				throw new mysqli_sql_exception($stmt->err);
				$stmt->close();
			}
		} else {
			throw new InvalidArgumentException("Missing query");
		}
	}

	/**
	 * Shuts the mysql connection down
	 *
	 * @return bool Could the connection be shut down?
	 */
	public function shutdown(){
		if($this->connected == true){
			$this->connected = false;

			if(self::$instance == $this){
				self::$instance = null;
			}

			return $this->db->close();
		} else {
			return true;
		}
	}
}
Database::Instance();
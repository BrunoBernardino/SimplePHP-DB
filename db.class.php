<?php
namespace Utilities;
use \PDO;
use \Exception;
/**
 * SimplePHP-DB - Simple Database PHP Class.
 *
 * @author Bruno Bernardino | http://brunobernardino.com
 * @package SimplePHP-DB - Simple Database PHP Class
 * @license CC BY-SA 3.0 | http://creativecommons.org/licenses/by-sa/3.0/deed.en_US
 * @version 2.0.0
 * @uses PDO
 * @uses Exception
 * @uses PDOException
 */
class DB {
	/**
	 * Protected String Database Engine/Driver (usually 'mysql', 'pgsql', 'sqlite', 'odbc', 'sqlsrv', or 'ibm')
	 */
	protected $engine;

	/**
	 * Protected String Database Hostname (usually localhost)
	 */
	protected $host;

	/**
	 * Protected String Database Username
	 */
	protected $user;

	/**
	 * Protected String Database Password
	 */
	protected $pass;

	/**
	 * Protected String Database Name
	 */
	protected $database;

	/**
	 * Protected String Database Link Identifier
	 */
	protected $db;

	/**
	 * Protected $lastStatement PDOStatement last successful statement used
	 */
	protected $lastStatement;

	/**
	 * Public __construct() method. Loads the $defaults object and starts the DB class
	 *
	 * @uses DB::start()
	 * @param $host String Database Hostname
	 * @param $db String Database Name
	 * @param $user String Database Username
	 * @param $pwd String Database Password
	 * @param $engine String (optional) Database Engine/Driver
	 */
	public function __construct( $host, $db, $user, $pwd, $engine = 'mysql' ) {
		$this->engine = $engine;
		$this->host = $host;
		$this->database = $db;
		$this->user = $user;
		$this->pass = $pwd;

		$this->start();
	}

	/**
	 * Public __call() method. Calls any method on DB's PDO ($db), and its PDOStatement, that doesn't exist in DB.
	 *
	 * @param $name String PDO method
	 * @param $arguments Array method arguments
	 */
	public function __call( $name, $arguments ) {
		if ( method_exists($this->db, $name) ) {
			return call_user_func_array( array($this->db, $name), $arguments );
		} elseif ( method_exists($this->lastStatement, $name) ) {
			return call_user_func_array( array($this->lastStatement, $name), $arguments );
		} else {
			throw new Exception( 'Method not found: ' . $name . '.' );
		}
	}

	/**
	 * Protected start() method. Initializes the Database Connection. Uses die() on failure.
	 * Also sets the database names as UTF-8, when using MySQL.
	 *
	 * @uses PDO
	 * @uses PDOException
	 */
	protected function start() {
		$dns = $this->engine . ':dbname=' . $this->database . ';host=' . $this->host;

		try {
			$this->db = new PDO( $dns, $this->user, $this->pass );
			//$this->db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );// If you need to debug something in the connection/queries, you may want to uncomment this line

			if ( $this->engine === 'mysql' ) {
				$sql = "SET NAMES 'utf8'";
				$this->db->exec( $sql );
			}
		} catch ( PDOException $e ) {
			die( 'The website is temporarily unavailable. (E#002 :: ' . $e->getMessage() . ').' );
		}
	}

	/**
	 * Public query() method. Executes a query.
	 *
	 * @uses PDO::query()
	 *
	 * @param $sql String Unprepared SQL statement/query.
	 * @param $values Associative Array with SQL statement variables.
	 * @return PDOStatement object, or FALSE on failure.
	 */
	public function query( $sql, $values = array() ) {
		try {
			if ( $this->lastStatement ) {
				$this->lastStatement->closeCursor();
			}

			$statement = $this->db->prepare( $sql );
			$statement->execute( $values );
			$this->lastStatement = $statement;

			return $statement;
		} catch ( PDOException $e ) {
			die( 'The website is temporarily unavailable. (E#003 :: ' . $e->getMessage() . ').' );
		}
	}

	/**
	 * Public queryId() method. Executes an Insert query and returns the generated ID.
	 * If there is an error with the query, returns FALSE.
	 *
	 * @uses DB::query()
	 * @uses DB::lastId()
	 *
	 * @param $sql String Unprepared SQL statement/query.
	 * @param $values Associative Array with SQL statement variables.
	 * @param $idColumn String Name of the ID column, if required.
	 * @return $result Integer the ID of Database::lastid() or 0 by default.
	 */
	public function queryId( $sql, $values = array(), $idColumn = null ) {
		$statement = $this->query( $sql, $values );
		return $this->lastId( $idColumn );
	}

	/**
	 * Public rows() method. Fetches the number of affected rows from the last query.
	 *
	 * @uses PDOStatement::rowCount()
	 *
	 * @return Integer number of affected rows from the last query.
	 */
	public function rows() {
		if ( $this->lastStatement ) {
			return $this->lastStatement->rowCount();
		} else {
			return false;
		}
	}

	/**
	 * Public execute() method. Executes and fetches all rows from the query.
	 *
	 * @uses DB::query()
	 * @uses PDOStatement::fetchAll()
	 *
	 * @param $sql String Unprepared SQL statement/query.
	 * @param $values Associative Array with SQL statement variables.
	 * @return Mixed, depending on what is fetched, or FALSE on failure.
	 */
	public function execute( $sql, $values = array() ) {
		$statement = $this->query( $sql, $values );
		if ( $statement ) {
			return $statement->fetchAll( PDO::FETCH_OBJ );
		} else {
			return false;
		}
	}

	/**
	 * Public sexecute() method. Executes and fetches first row from the query.
	 *
	 * @uses DB::query()
	 * @uses PDOStatement::fetch()
	 *
	 * @param $sql String Unprepared SQL statement/query.
	 * @param $values Associative Array with SQL statement variables.
	 * @return Mixed, depending on what is fetched, or FALSE on failure.
	 */
	public function sexecute( $sql, $values = array() ) {
		$statement = $this->query( $sql, $values );
		if ( $statement ) {
			return $statement->fetch( PDO::FETCH_OBJ );
		} else {
			return false;
		}
	}

	/**
	 * Public get() method. Executes and fetches the first return var from the first row from the query.
	 *
	 * @uses DB::query()
	 * @uses PDOStatement::fetchColumn
	 *
	 * @param $sql String Unprepared SQL statement/query.
	 * @param $values Associative Array with SQL statement variables.
	 * @param $columnIndex Integer column index to fetch.
	 * @return Mixed, depending on what is fetched, or FALSE on failure.
	 */
	public function get( $sql, $values = array(), $columnIndex = 0 ) {
		$statement = $this->query( $sql, $values );

		if ( $statement ) {
			return $statement->fetchColumn( $columnIndex );
		} else {
			return false;
		}
	}

	/**
	 * Public lastId() method. Gets the generated ID from the last query.
	 *
	 * @uses PDO::lastInsertId()
	 *
	 * @param $idColumn String Name of the ID column, if required.
	 * @return Integer Last query item ID.
	 */
	public function lastId( $idColumn = null ) {
		return $this->db->lastInsertId( $idColumn );
	}

	/**
	 * Public end() method. Closes the DB connection.
	 */
	public function end() {
		$this->db = null;
	}
}
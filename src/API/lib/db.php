<?php

/**
 * Custom database class
 * @author Dustin Newbold
 */

namespace API\lib;

class DB {
	protected $connection = null;
	private $count = 0;

	public function __construct() {
		$this->initConnection();
	}

	/**
	 * Queries against the database, returning a single item rather than a
	 * collection of items. Works very well when only expecting a single item.
	 *
	 * @param $query (string) -
	 * @param $binds (array)  -
	 *
	 * @return (object) - Returns object associated with query/data
	 */
	public function single($query, $binds = array()) {
		$this->count++;
		try {
			$statement = $this->connection->prepare($query);
			$statement->execute($binds);
			$row = $statement->fetch(\PDO::FETCH_OBJ);
		} catch ( \PDOException $e ) {
			dd($e);
		} catch ( Exception $e ) {
			dd($e);
		}

		if ( !$row ) {
			$row = (object)array();
		}

		return Response::keys_to_lower($row);
	}

	/**
	 * Queries against the database, returning a collection of items.
	 *
	 * @param $query (string) -
	 * @param $binds (array)  -
	 *
	 * @return (object) - Returns a collection of objects based on query/data
	 * @return (array) - Returns an empty array if no records are found
	 */
	public function query($query, $binds = array()) {
		$this->count++;
		try {
			$statement = $this->connection->prepare($query);
			$statement->execute($binds);
			$row = $statement->fetchAll(\PDO::FETCH_OBJ);
		} catch ( \PDOException $e ) {
			dd($e);
		} catch (Exception $e) {
			dd($e);
		}

		if ( empty($row) ) {
			return array();
		}

		return Response::keys_to_lower($row);
	}

	/**
	 * Removes a row from the database
	 *
	 * @param $table (string)
	 * @param $id (int) - The ID of the item in the table to remove
	 *
	 * @return (bool)   - Returns true if removed successfully, false otherwise
	 */
	public function delete($table, $id) {
		$this->count++;
		try {
			if ( $id > 0 ) {
				$statement = $this->connection->prepare('DELETE FROM `' . $table . '` WHERE ID = :id');
				$return = $statement->execute(array(':id' => $id));
			} else {
				$statement = $this->connection->prepare('DELETE FROM `' . $table . '` WHERE ' . $id);
				$return = $statement->execute(array(':id' => $id));
			}
		} catch ( \PDOException $e ) {
			dd($e);
			return false;
		} catch (Exception $e) {
			dd($e);
			return false;
		}

		return true;
	}

	/**
	 * Inserts data into the database.
	 *
	 * @param $table (string) - Name of the table to insert data into
	 * @param $binds (array)  - Associative array wherein keys are column names and values are data
	 *							to insert into the columns
	 * @return (int)          - If successful, it returns the ID of the inserted value.
	 *						  - If not successful, it will error out currently
	 *
	 * @todo Remove the die/dumps and add graceful failing
	 */
	public function insert($table, $binds) {
		$this->count++;
		try {
			$columns = array_keys($binds);

			// Fix binds to work as binds
			foreach ( $binds as $key => $value ) {
				$binds[':' . strtolower($key)] = $value;
				unset($binds[$key]);
			}

			$statement = $this->connection->prepare('INSERT INTO `' . $table . '` (`' . implode('`,`', $columns) . '`) VALUES (' . implode(',', array_keys($binds)) . ');');
			$return = $statement->execute($binds);
		} catch (\PDOException $e) {
			dd($e);
		} catch (Exception $e) {
			dd($e);
		}

		if ( $return === false ) {
			return false;
		}

		return $this->connection->lastInsertID();
	}

	/**
	 * Updates data in the database
	 *
	 * @param $table (string) -
	 * @param $id (int)       -
	 * @param $binds (array) -
	 *
	 * @return (bool) -
	 *
	 * @todo Update function to use array binds and bind them in PDO
	 */
	public function update($table, $id, $binds) {
		$this->count++;
		try {
			if ( gettype($binds) === 'array' ) {
				$insert = '';
				foreach ( $binds as $key => $value ) {
					$insert .= ', `' . $key . '` = :' . strtolower($key);
					$binds[':' . strtolower($key)] = $value;
					unset($binds[$key]);

					$this->count++;
					$from = $this->single('SELECT `' . $key . '` FROM `' . $table . '` WHERE ID = :id', array(':id' => $id));
					$lowertable = strtolower($table);
					$lowerkey = strtolower($key);
					$from = $from->$lowerkey;
					$to = $value;
					$data = $lowertable . '.' . $lowerkey;
					$ignore = array(
						'user.lastsignin'
					);

					if ( in_array($data, $ignore) ) {
						continue;
					}

					if ( $data === 'user.password' ) {
						$from = '-';
						$to = '-';
					}

					global $userid;
					$this->count++;
					$this->insert('Audit', array(
						'Type'   	=> 'update',
						'Data'   	=> $lowertable . '.' . $lowerkey,
						'DataID' 	=> $id,
						'From'   	=> $from,
						'To'     	=> $to,
						'UserID'    => $userid,
						'Timestamp' => time()
					));
				}
				$insert = substr($insert, 2);
			} else {
				$insert = $binds;
				$binds = array();
			}

			$statement = $this->connection->prepare('UPDATE `' . $table . '` SET ' . $insert . ' WHERE ID = ' . $id);
			$return = $statement->execute($binds);
		} catch (\PDOException $e) {
			dd($e);
		} catch (Exception $e) {
			dd($e);
		}

		if ( $return === false ) {
			return false;
		}

		return true;
	}

	/**
	 * Queries against the database based on an ID and table
	 *
	 * @param $table (string) -
	 * @param $id (int)       -
	 *
	 * @return (object) - Returns object based on query/data
	 */
	public function find($table, $id) {
		$this->count++;
		try {
			if ( gettype($id) === 'integer' && $id > 0 ) {
				$statement = $this->connection->prepare('SELECT * FROM `' . $table . '` WHERE ID = :id');
				$statement->execute(array(':id' => $id));
			} else if ( gettype($id) === 'array' ) {
				$query = 'SELECT * FROM `' . $table . '` WHERE';
				$binds = array();
				foreach ( $id as $key => $value ) {
					$query .= ' `' . $key . '` = :' . strtolower($key) . ' AND';
					$binds[':' . strtolower($key)] = $value;
				}
				$query = substr($query, 0, strlen($query) - 4);
				$statement = $this->connection->prepare($query);
				$statement->execute($binds);
			}
			$row = $statement->fetch(\PDO::FETCH_OBJ);
		} catch (\PDOException $e) {
			dd($e);
		} catch (Exception $e) {
			dd($e);
		}

		if ( !$row ) {
			$row = (object)array();
		}

		return Response::keys_to_lower($row);
	}

	/**
	 * Upcert will insert if the row cannot be matched automatically
	 *
	 * NOT YET IMPLEMENTED
	 */
	public function upcert($table, $binds) { }

	/**
	 * Used as a debugging tool to see how many queries are being processed
	 * on a single flow
	 */
	public function count() {
		return $this->count;
	}

	/**
	 * Initializes the database connection
	 */
	protected function initConnection() {
		$hostname = Config::get('hostname');
		$database = Config::get('database');
		$username = Config::get('username');
		$password = Config::get('password');
		$this->connection = $this->connect($hostname, $database, $username, $password);
		$this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	}

	/**
	 * Connects to the database
	 */
	protected function connect($hostname, $database, $username, $password) {
		return new \PDO('mysql:host=' . $hostname . ';dbname=' . $database, $username, $password);
	}
}

<?php

namespace Agnate\LazyQuest;

use \Exception;
use \PDO;

class Database {

  public $host;
  public $name;
  public $user;
  public $pass;

  protected $connection;

  /**
   * Create a Database connection using credentials.
   * @param $host Name of the host to connect to.
   * @param $database_name Name of the database to connect to.
   * @param $username Database username credentials.
   * @param $password Password for username specified.
   * @param $connect Initialize the connection automatically.
   */
  function __construct($host, $database_name, $username, $password, $connect = TRUE) {
    $this->host = $host;
    $this->name = $database_name;
    $this->user = $username;
    $this->password = $password;

    if ($connect) {
      $this->connect();
    }
  }

  /**
   * Create the connection to PDO.
   */
  public function connect() {
    // If we don't have a connection yet, connect to database.
    if (empty($this->connection)) {
      $this->connection = new PDO ("mysql:host=" . $this->host . ";dbname=" . $this->name . ";charset=utf8", $this->user, $this->password, array(PDO::ATTR_PERSISTENT => TRUE));
    }

    return $this->connection;
  }

  /**
   * Get connection to PDO db.
   */
  public function connection() {
    // If there's no connection, create one.
    if (empty($this->connection)) {
      $this->connect();
    }

    return $this->connection;
  }

  /**
   * Prepare a query statement to use in PDO.
   * @param $statement String query to pass to PDO's prepare() function.
   */
  public function prepare($statement) {
    // If there's no connection, create one.
    if (empty($this->connection)) {
      $this->connect();
    }

    // If there's still no connection, we can't do anything, so throw an error.
    if (empty($this->connection)) {
      throw new Exception("Could not initialize connection to PDO database.");
    }

    return $this->connection->prepare($statement);
  }

  /**
   * Create a database table of fields.
   * @param $table_name Name of the table to create as a string.
   * @param $primary_key Name of the primary key to assign to the table.
   * @param $fields Array of field definitions. Example: "uid INT(11) UNSIGNED AUTO_INCREMENT"
   * @return Boolean Whether or not the query was successful.
   */
  public function createTable($table_name, $primary_key, Array $fields) {
    $fields[] = "PRIMARY KEY ( " . $primary_key . " )";
    $statement = "CREATE TABLE IF NOT EXISTS " . $table_name . " (" . implode(', ', $fields) . ")";
    $query = $this->prepare($statement);
    $success = $query->execute();
    // If there was an error, throw it.
    if (!$success) throw new Exception(var_export($query->errorInfo(), true));
    return $success;
  }

  /**
   * Check if a database table exists.
   * @param $table_name Name of the table to check.
   * @return Boolean Whether or not the database table exists.
   */
  public function tableExists($table_name) {
    $tokens = array(':table_name' => $table_name);
    $statement = "SHOW TABLES LIKE :table_name";
    $query = $this->prepare($statement);
    $query->execute($tokens);
    return ($query->rowCount() > 0);
  }
}
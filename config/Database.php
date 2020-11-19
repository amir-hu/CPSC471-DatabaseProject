<?php

class Database {

	private $servername = 'localhost';
	private $username = 'root';
	private $password = 'root';
	private $database;
	private $connection;

	public function connect() {

		// Get the database name from the sql script.
		// Reads the first line which will contain the database name.
        $f = fopen('database.sql', 'r');
        $string = trim(fgets($f));
        fclose($f);

        $pieces = explode(' ', $string);
        $database = array_pop($pieces);
		$database = substr($database, 0, -1); // Takes everything but the last char. Remove ; from the string.

		$this->connection = null;

		try {

            // Create connection
			$this->connection = new PDO('mysql:host=' . $this->servername . ';dbname=' . $this->database, $this->username, $this->password);
			// Set error mode. Get exception when we create queries in case something goes wrong
			$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			echo 'Successfully connected to the database.';

		}
        catch(PDOException $exception) {

			echo 'Connection Error: ' . $exception->getMessage();
		}

		return $this->connection;
	}
}

// testing
//$a = new Database();
//$a->connect();

?>


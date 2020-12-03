<?php

include_once __DIR__ . '/Database.php'; // Bring in database

// Execute the sql script to set up the database (one time).

class Install extends Database {

    private $connection;

    private function createDatabaseTables(){

        try {

            // Create connection
            // The first time we connect, we don't connect to any specific database
            // So we can set up the database and tables.

            $connection = new PDO('mysql:host=' . $this->servername . ';dbname=' . $this->database, $this->username, $this->password);
            // Set error mode. Get exception when we create queries in case something goes wrong
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo 'Successfully connected to the database.';

            // Create the database tables.
            $sql = file_get_contents(__DIR__ . '/database.sql');

            $stmt = $connection->prepare($sql);

            if ($stmt->execute()) echo 'Database and tables created successfully.';
            else echo 'Install.php: Could not create database.';

        }
        catch(PDOException $exception) {

            echo 'Install.php: Failed to connect to MySQL: ' . $exception->getMessage();
            die();

        }

    }

    private function createDatabaseStoredProc(){

        try {

            $this->setDatabaseName();

            // Create connection with a specific database name
            $connection = new PDO('mysql:host=' . $this->servername . ';dbname=' . $this->database, $this->username, $this->password);
            // Set error mode. Get exception when we create queries in case something goes wrong
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Execute the create stored procedures script.
            $sql = file_get_contents(__DIR__ . '/databaseStoredProc.sql');

            // Error opening file.
            if(!$sql) {
                $error = error_get_last();
                echo "Error: " . $error['message'];

            }else {

                $stmt = $connection->prepare($sql);

                if ($stmt->execute()) echo 'Stored procedures created successfully.';
                else echo 'Install.php: Could not create stored procedures.';
            }
        }
        catch(PDOException $exception) {

            echo 'Install.php: Failed to connect to MySQL: ' . $exception->getMessage();
            die();

        }

    }

    function __construct() {

        $this->createDatabaseTables();
        $this->createDatabaseStoredProc();

    }
}

new Install();

?>

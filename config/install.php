<?php

// Execute the sql script to set up the database (one time).
// The first time we connect, we don't connect to any specific database
// So we can set up the database and tables.

$servername = 'localhost';
$username = 'root';
$password = 'root';
$database = "";
$connection;

try {

    // Create connection
    $connection = new PDO('mysql:host=' . $servername . ';dbname=' . $database, $username, $password);
    // Set error mode. Get exception when we create queries in case something goes wrong
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo 'Successfully connected to the database.';

}catch(PDOException $exception) {

    echo 'install.php: Failed to connect to MySQL: ' . $exception->getMessage();

}

// Create the database tables.
$sql = file_get_contents(__DIR__ . '/database.sql');

$stmt = $connection->prepare($sql);

if ($stmt->execute()) echo 'Database and tables created successfully.';
else echo 'install.php: Could not create database.';


// Execture the create stored procedures script.
$sql = file_get_contents(__DIR__ . '/databaseStoredProc.sql');

// Error opening file.
if(!$sql) {
    $error = error_get_last();
    echo "Error: " . $error['message'];

}else {

    $stmt = $connection->prepare($sql);

    if ($stmt->execute()) echo 'Stored procedures created successfully.';
    else echo 'install.php: Could not create stored procedures.';
}

?>

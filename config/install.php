<?php

// Execute the sql script to set up the database (one time).

include_once './Database.php';

try{

    $database = new Database();
    $connection = $database->connect();
    $sql = file_get_contents('database.sql');
    $connection->exec($sql);
    echo 'Database and tables created successfully.';

}
catch(PDOException $exception) {

    echo $exception->getMessage();
}

?>
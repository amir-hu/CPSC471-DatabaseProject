<?php

// Required HTTP headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/Database.php'; // Bring in database

if ($_SERVER["REQUEST_METHOD"] != "GET") {
    // Set response code - 405 Method not allowed
    http_response_code(405);
    echo 'Request method ' . $_SERVER["REQUEST_METHOD"] . ' not allowed';
    exit();
}

// Check if any paramters were passed and return that else return an empty string.

// Instantiate DB and connect
$database = new Database();
$db = $database->connect();

// SQL statement to call the stored proc. Positional paramaters - act as placeholders.
$sql = 'CALL SelectCaretaker()';

// Prepare for execution of stored procedure
$stmt = $db->prepare($sql);

    // Execute stored procedure
    try {
        $stmt->execute();

        $caretakerRows = $stmt->fetchAll(PDO::FETCH_OBJ);
        echo json_encode($caretakerRows);
       
        // Set response code - 200 ok
        http_response_code(200);
        
    }
    catch(PDOException $exception) {
        // Set response code - 400 bad request
        // Show error if something goes wrong.
        http_response_code(400);
        echo "Unable to retrieve list. " . $exception->getMessage();
    }

?>
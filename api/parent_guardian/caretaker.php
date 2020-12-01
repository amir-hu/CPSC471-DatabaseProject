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
    $message = array('Message' => 'Request method ' . $_SERVER["REQUEST_METHOD"] . ' not allowed.');
    echo json_encode($message);
    exit();
}

// Instantiate DB and connect
$database = new Database();
$db = $database->connect();
$database->authenticate();

// SQL statement to call the stored proc. Positional paramaters - act as placeholders.
$sql = 'CALL SelectCaretaker()';

// Prepare for execution of stored procedure
$stmt = $db->prepare($sql);

// Execute stored procedure
try {
    $stmt->execute();

    // Get row count
    $numOfRecords = $stmt->rowCount();
    if ($numOfRecords == 0) {
        $message = array('Message' => 'No caretakers.');
        echo json_encode($message);
    }
    else {
        // Set response code - 200 ok
        http_response_code(200);

        // Returns all rows as an object
        $caretakerRows = $stmt->fetchAll(PDO::FETCH_OBJ);

        // Turn to JSON & output
        echo json_encode($caretakerRows);
    }

    $stmt->closeCursor();

}
catch(PDOException $exception) {
    // Set response code - 400 bad request
    // Show error if something goes wrong.
    http_response_code(400);
    $message = array('Message' => 'Unable to retrieve list.' . $exception->getMessage());
    echo json_encode($message);
}

?>
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
$database->authenticate("med");

// Get data that is gonna be in JSON format.
$data = json_decode(file_get_contents("php://input"));

// Check if any paramters were passed and return that else return an empty string.
$parentSIN = !empty($data->ParentSIN) ? $data->ParentSIN : '';

// SQL statement to call the stored proc. Positional paramaters - act as placeholders.
$sql = 'CALL ViewBill(:parentSIN)';

// Prepare for execution of stored procedure
$stmt = $db->prepare($sql);

// Clean up and sanitize data: remove html characters and strip any tags
$parentSIN = htmlspecialchars(strip_tags($parentSIN));

// Bind data
$stmt->bindParam(':parentSIN', $parentSIN);

// Validate request:

// Check if the data is empty
if (empty($parentSIN)) {

    // Set response code - 400 bad request
    http_response_code(400);

    $message = array('Message' => 'Unable to get bill. Data is incomplete.');
    echo json_encode($message);

    // Check data type
}else if (!is_numeric($parentSIN)) {

    // Set response code - 400 bad request
    http_response_code(400);

    $message = array('Message' => 'Unable to get bill. Data type is not correct.');
    echo json_encode($message);

    // Make sure that the input length matches model
}else if (strlen($parentSIN) > 8) {

    // Set response code - 400 bad request
    http_response_code(400);

    $message = array('Message' => 'Unable to get bill. Data length does not match the defined model.');
    echo json_encode($message);

}else {

    // Execute stored procedure
    try {
        $stmt->execute();

        // Get row count
        $numOfRecords = $stmt->rowCount();
        if ($numOfRecords == 0) {
            $message = array('Message' => 'No bill for this parent.');
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
        $message = array('Message' => "Unable to get bill. " . $exception->getMessage());
        echo json_encode($message);
    }
}
?>
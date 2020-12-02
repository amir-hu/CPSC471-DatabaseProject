<?php

// Required HTTP headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/Database.php'; // Bring in database

if ($_SERVER["REQUEST_METHOD"] != "DELETE") {
    // Set response code - 405 Method not allowed
    http_response_code(405);
    $message = array('Message' => 'Request method ' . $_SERVER["REQUEST_METHOD"] . ' not allowed.');
    echo json_encode($message);
    exit();
}

// Get data in JSON format.
$data = json_decode(file_get_contents("php://input"));

// Check if any paramters were passed and return that else return an empty string.
$prsnSIN = !empty($data->SIN) ? $data->SIN : '';

// Instantiate DB and connect
$database = new Database();
$db = $database->connect();
$database->authenticate("high");

// SQL statement to call the stored proc. Positional paramaters - act as placeholders.
$sql = 'CALL RemovePerson(:prsnSIN)';

// Prepare for execution of stored procedure
$stmt = $db->prepare($sql);

// Clean up and sanitize data: remove html characters and strip any tags
$prsnSIN = htmlspecialchars(strip_tags($prsnSIN));

// Bind data
$stmt->bindParam('prsnSIN', $prsnSIN);

// Validate request:

// Check if the data is empty
if (empty($prsnSIN)) {

    // Set response code - 400 bad request
    http_response_code(400);

    $message = array('Message' => 'Unable to remove person. Data is incomplete.');
    echo json_encode($message);

    // Check data type
}else if (!(is_numeric($prsnSIN)) ) {

    // Set response code - 400 bad request
    http_response_code(400);

    $message = array('Message' => 'Unable to remove person. Data type is not correct.');
    echo json_encode($message);

    // Make sure that the input data types (field type, length, etc.) matches model
}else if (strlen($prsnSIN) > 8) {

    // Set response code - 400 bad request
    http_response_code(400);

    $message = array('Message' => 'Unable to remove person. Data length does not match the defined model.');
    echo json_encode($message);

}else {

    // Execute stored procedure
    try {
        $stmt->execute();

        // Get row count
        $numOfRecords = $stmt->rowCount();
        if ($numOfRecords == 0) {
            $message = array('Message' => 'No person with that SIN. Nothing was removed.');
            echo json_encode($message);
        }
        else {
            // Set response code - 200 ok
            http_response_code(200);
            $message = array('Message' => "Person has been removed.");
            echo json_encode($message);
        }
    }
    catch(PDOException $exception) {
        // Set response code - 400 bad request
        // Show error if something goes wrong.
        http_response_code(400);
        $message = array('Message' => "Unable to remove person. " . $exception->getMessage());
        echo json_encode($message);
    }
}
?>
<?php

// Required HTTP headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/Database.php'; // Bring in database

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    // Set response code - 405 Method not allowed
    http_response_code(405);
    $message = array('Message' => 'Request method ' . $_SERVER["REQUEST_METHOD"] . ' not allowed.');
    echo json_encode($message);
    exit();
}

// Get data in JSON format.
$data = json_decode(file_get_contents("php://input"));

// Check if any paramters were passed and return that else return an empty string.
$childFirstName = !empty($data->ChildFirstName) ? $data->ChildFirstName : '';
$childLastName = !empty($data->ChildLastName) ? $data->ChildLastName : '';
$adminEmpId = !empty($data->SubmittedById) ? $data->SubmittedById : '';

// Instantiate DB and connect
$database = new Database();
$db = $database->connect();
$database->authenticate("high");

// SQL statement to call the stored proc. Positional paramaters - act as placeholders.
$sql = 'CALL AddToWaitlist(:childFirstName, :childLastName, :adminEmpId)';

// Prepare for execution of stored procedure
$stmt = $db->prepare($sql);

// Clean up and sanitize data: remove html characters and strip any tags
$childFirstName = htmlspecialchars(strip_tags($childFirstName));
$childLastName = htmlspecialchars(strip_tags($childLastName));
$adminEmpId = htmlspecialchars(strip_tags($adminEmpId));

// Bind data
$stmt->bindParam(':childFirstName', $childFirstName);
$stmt->bindParam(':childLastName', $childLastName);
$stmt->bindParam(':adminEmpId', $adminEmpId);

// Validate request:

// Check if the data is empty
if (empty($childFirstName) || empty($childLastName) || empty($adminEmpId)) {

    // Set response code - 400 bad request
    http_response_code(400);

    $message = array('Message' => 'Unable to add to waitlist. Data is incomplete.');
    echo json_encode($message);

    // Check data type
}else if (ctype_digit($childFirstName) || ctype_digit($childLastName) || !(is_numeric($adminEmpId)) ) {

    // Set response code - 400 bad request
    http_response_code(400);

    $message = array('Message' => 'Unable to add to waitlist. Data type is not correct.');
    echo json_encode($message);

    // Make sure that the input length matches model
}else if (strlen($childFirstName) > 30 || strlen($childLastName) > 30 || strlen($adminEmpId) > 11) {

    // Set response code - 400 bad request
    http_response_code(400);

    $message = array('Message' => 'Unable to add to waitlist. Data length does not match the defined model.');
    echo json_encode($message);

}else {

    // Execute stored procedure
    try {
        $stmt->execute();
        // Set response code - 201 created
        http_response_code(201);

        $message = array('Message' => 'Child added to waitlist.');
        echo json_encode($message);
    }
    catch(PDOException $exception) {
        // Set response code - 400 bad request
        // Show error if something goes wrong.
        http_response_code(400);
        $message = array('Message' => 'Unable to add child to waitlist. ' . $exception->getMessage());
        echo json_encode($message);
    }
}
?>
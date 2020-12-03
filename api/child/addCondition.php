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

// Get data in JSON format.
$data = json_decode(file_get_contents("php://input"));

// Check if any paramters were passed and return that else return an empty string.
$childSIN = !empty($data->ChildSIN) ? $data->ChildSIN : '';
$condName = !empty($data->ConditionName) ? $data->ConditionName : '';
$condTreatment = !empty($data->ConditionTreatment) ? $data->ConditionTreatment : '';

// Instantiate DB and connect
$database = new Database();
$db = $database->connect();
$database->authenticate("low");

// SQL statement to call the stored proc. Positional paramaters - act as placeholders.
$sql = 'CALL AddMedicalCondition(:childSIN, :conditionName, :conditionTreatment)';

// Prepare for execution of stored procedure
$stmt = $db->prepare($sql);

// Clean up and sanitize data: remove html characters and strip any tags
$childSIN = htmlspecialchars(strip_tags($childSIN));
$condName = htmlspecialchars(strip_tags($condName));
$condTreatment = htmlspecialchars(strip_tags($condTreatment));

// Bind data
$stmt->bindParam(':childSIN', $childSIN);
$stmt->bindParam(':conditionName', $condName);
$stmt->bindParam(':conditionTreatment', $condTreatment);

// Validate request:

// Check if the data is empty
if (empty($childSIN) || empty($condName) || empty($condTreatment)) {

    // Set response code - 400 bad request
    http_response_code(400);

    $message = array('Message' => 'Unable to add conditions. Data is incomplete.');
    echo json_encode($message);

    // Check data type
}else if (!is_numeric($childSIN)) {

    // Set response code - 400 bad request
    http_response_code(400);

    $message = array('Message' => 'Unable to add conditions. Data type is not correct.');
    echo json_encode($message);

    // Make sure that the input length matches model
}else if (strlen($childSIN) > 8 ) {

    // Set response code - 400 bad request
    http_response_code(400);

    $message = array('Message' => 'Unable to add condtions. Data length does not match the defined model.');
    echo json_encode($message);

}else {
    // Execute stored procedure
    try {
        $stmt->execute();
        // Set response code - 201 created
        http_response_code(201);

        $message = array('Message' => "Child condition has been added.");
        echo json_encode($message);
    }
    catch(PDOException $exception) {
        // Set response code - 400 bad request
        // Show error if something goes wrong.
        http_response_code(400);
        $message = array('Message' => "Unable to add condition. " . $exception->getMessage());
        echo json_encode($message);
    }
}
?>
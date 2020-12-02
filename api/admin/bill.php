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
$billId = !empty($data->BillId) ? $data->BillId : '';
$empId = !empty($data->CreatedById) ? $data->CreatedById : '';
$pmntMethod = !empty($data->PaymentMethod) ? $data->PaymentMethod : NULL;
$amtPending = !empty($data->AmountPending) ? $data->AmountPending : '';

// Instantiate DB and connect
$database = new Database();
$db = $database->connect();
$database->authenticate();

// SQL statement to call the stored proc. Positional paramaters - act as placeholders.
$sql = 'CALL CreateBill(:billId, :empId, :pmntMethod, :amtPending)';

// Prepare for execution of stored procedure
$stmt = $db->prepare($sql);

// Clean up and sanitize data: remove html characters and strip any tags
$billId = htmlspecialchars(strip_tags($billId));
$empId = htmlspecialchars(strip_tags($empId));
$pmntMethod = !is_null($pmntMethod) ? htmlspecialchars(strip_tags($pmntMethod)) : NULL;
$amtPending = htmlspecialchars(strip_tags($amtPending));

// Bind data
$stmt->bindParam(':billId', $billId);
$stmt->bindParam(':empId', $empId);
$stmt->bindParam(':pmntMethod', $pmntMethod);
$stmt->bindParam(':amtPending', $amtPending);

// Validate request:

// Check if the data is empty
if (empty($billId) || empty($empId) || empty($amtPending)) {

    // Set response code - 400 bad request
    http_response_code(400);

    $message = array('Message' => 'Unable to create bill. Data is incomplete.');
    echo json_encode($message);

    // Check data type
}else if ( !(is_numeric($billId) && is_numeric($empId) & is_numeric($amtPending)) ) {

    // Set response code - 400 bad request
    http_response_code(400);

    $message = array('Message' => 'Unable to create bill. Data type is not correct.');
    echo json_encode($message);

    // Make sure that the input length matches model
}else if (strlen($billId) > 11 || strlen($empId) > 11) {

    // Set response code - 400 bad request
    http_response_code(400);

    $message = array('Message' => 'Unable to create bill. Data length does not match the defined model.');
    echo json_encode($message);

}else {

    // Execute stored procedure
    try {
        $stmt->execute();
        // Set response code - 201 created
        http_response_code(201);

        $message = array('Message' => 'Bill has been created.');
        echo json_encode($message);
    }
    catch(PDOException $exception) {
        // Set response code - 400 bad request
        // Show error if something goes wrong.
        http_response_code(400);
        $message = array('Message' => "Unable to create bill. " . $exception->getMessage());
        echo json_encode($message);
    }
}
?>
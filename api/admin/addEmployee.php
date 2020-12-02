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
$daycareName = !empty($data->DaycareName) ? $data->DaycareName : '';
$daycareAddress = !empty($data->DaycareAddress) ? $data->DaycareAddress : '';
$empSIN= !empty($data->SIN) ? $data->SIN : '';
$empId= !empty($data->EmployeeId) ? $data->EmployeeId : '';
$workHours = !empty($data->WorkHours) ? $data->WorkHours : '';

// Instantiate DB and connect
$database = new Database();
$db = $database->connect();
$database->authenticate();

// SQL statement to call the stored proc. Positional paramaters - act as placeholders.
$sql = 'CALL AddEmployee(:daycareName, :daycareAddress, :empSIN, :empId, :workHours)';

// Prepare for execution of stored procedure
$stmt = $db->prepare($sql);

// Clean up and sanitize data: remove html characters and strip any tags
$daycareName = htmlspecialchars(strip_tags($daycareName));
$daycareAddress = htmlspecialchars(strip_tags($daycareAddress));
$empSIN = htmlspecialchars(strip_tags($empSIN));
$empId = htmlspecialchars(strip_tags($empId));
$workHours =htmlspecialchars(strip_tags($workHours));

// Bind data
$stmt->bindParam(':daycareName', $daycareName);
$stmt->bindParam(':daycareAddress', $daycareAddress);
$stmt->bindParam(':empSIN', $empSIN);
$stmt->bindParam(':empId', $empId);
$stmt->bindParam(':workHours', $workHours);

// Validate request:

// Check if the data is empty
if (empty($daycareName) || empty($daycareAddress) || empty($empSIN) || empty($empId) || empty($workHours)) {

    // Set response code - 400 bad request
    http_response_code(400);

    $message = array('Message' => 'Unable to add employee. Data is incomplete.');
    echo json_encode($message);

    // Check data type
}else if (ctype_digit($daycareName) || !(is_numeric($empSIN)) || !(is_numeric($empId)) || !(is_numeric($workHours)) ) {

    // Set response code - 400 bad request
    http_response_code(400);

    $message = array('Message' => 'Unable to add employee. Data type is not correct.');
    echo json_encode($message);

    // Make sure that the input length matches model
}else if (strlen($daycareName) > 100 || strlen($daycareAddress) > 100 || strlen($empSIN) > 8 || strlen($empId) > 11) {

    // Set response code - 400 bad request
    http_response_code(400);

    $message = array('Message' => 'Unable to add employee. Data length does not match the defined model.');
    echo json_encode($message);

}else {

    // Execute stored procedure
    try {
        $stmt->execute();
        // Set response code - 201 created
        http_response_code(201);

        $message = array('Message' => 'Employee has been added.');
        echo json_encode($message);
    }
    catch(PDOException $exception) {
        // Set response code - 400 bad request
        // Show error if something goes wrong.
        http_response_code(400);
        $message = array('Message' => "Unable to add employee. " . $exception->getMessage());
        echo json_encode($message);
    }
}
?>
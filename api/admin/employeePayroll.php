<?php

// Required HTTP headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/Database.php'; // Bring in database

if ($_SERVER["REQUEST_METHOD"] != "PUT") {
    // Set response code - 405 Method not allowed
    http_response_code(405);
    $message = array('Message' => 'Request method ' . $_SERVER["REQUEST_METHOD"] . ' not allowed.');
    echo json_encode($message);
    exit();
}

// Get data in JSON format.
$data = json_decode(file_get_contents("php://input"));

// Check if any paramters were passed and return that else return an empty string.
$empSIN = !empty($data->EmployeeSIN) ? $data->EmployeeSIN : '';
$workHours = !empty($data->WorkHours) ? $data->WorkHours : 0;
$hourlyPay = !empty($data->HourlyPay) ? $data->HourlyPay : 0;

// Instantiate DB and connect
$database = new Database();
$db = $database->connect();
$database->authenticate("high");

// SQL statement to call the stored proc. Positional paramaters - act as placeholders.
$sql = 'CALL UpdateEmployeePay(:empSIN, :workHours, :hourlyPay)';

// Prepare for execution of stored procedure
$stmt = $db->prepare($sql);

// Clean up and sanitize data: remove html characters and strip any tags
$empSIN = htmlspecialchars(strip_tags($empSIN));
$workHours = htmlspecialchars(strip_tags($workHours));
$hourlyPay = htmlspecialchars(strip_tags($hourlyPay));

// Bind data
$stmt->bindParam(':empSIN', $empSIN);
$stmt->bindParam(':workHours', $workHours);
$stmt->bindParam(':hourlyPay', $hourlyPay);

// Validate request:

// Check if the data is empty
if (empty($empSIN)) {

    // Set response code - 400 bad request
    http_response_code(400);

    $message = array('Message' => 'Unable to update employee payroll info. Data is incomplete.');
    echo json_encode($message);

    // Check data type
}else if ( !(is_numeric($empSIN) & is_numeric($workHours) & is_numeric($hourlyPay)) ) {

    // Set response code - 400 bad request
    http_response_code(400);

    $message = array('Message' => 'Unable to update employee payroll info. Data type is not correct.');
    echo json_encode($message);

    // Make sure that the input length matches model
}else if (strlen($empSIN) > 8) {

    // Set response code - 400 bad request
    http_response_code(400);

    $message = array('Message' => 'Unable to update employee payroll info. Data length does not match the defined model.');
    echo json_encode($message);

}else {

    // Execute stored procedure
    try {
        $stmt->execute();
        // Set response code - 200 ok
        http_response_code(200);

        $message = array('Message' => 'Employee payroll info has been updated.');
        echo json_encode($message);
    }
    catch(PDOException $exception) {
        // Set response code - 400 bad request
        // Show error if something goes wrong.
        http_response_code(400);
        $message = array('Message' => "Unable to update employee payroll info. " . $exception->getMessage());
        echo json_encode($message);
    }
}
?>
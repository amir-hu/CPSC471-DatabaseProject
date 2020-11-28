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
    echo 'Request method ' . $_SERVER["REQUEST_METHOD"] . ' not allowed';
    exit();
}

// Check if any paramters were passed and return that else return an empty string.
$daycareName = isset($_GET['DaycareName']) ? $_GET['DaycareName'] : '';
$daycareAddress = isset($_GET['DaycareAddress']) ? $_GET['DaycareAddress'] : '';
$empSIN= isset($_GET['SIN']) ? $_GET['SIN'] : '';
$empId= isset($_GET['EmployeeId']) ? $_GET['EmployeeId'] : '';
$workHours = isset($_GET['WorkHours']) ? $_GET['WorkHours'] : '';

// Instantiate DB and connect
$database = new Database();
$db = $database->connect();

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

    echo 'Unable to add employee. Data is incomplete.';

    // Check data type
}else if (ctype_digit($daycareName) || !(is_numeric($empSIN)) || !(is_numeric($empId)) || !(is_numeric($workHours)) ) {

    // Set response code - 400 bad request
    http_response_code(400);

    echo 'Unable to add employee. Data type is not correct.';

    // Make sure that the input length matches model
}else if (strlen($daycareName) > 100 || strlen($daycareAddress) > 100 || strlen($empSIN) > 8 || strlen($empId) > 11) {

    // Set response code - 400 bad request
    http_response_code(400);

    echo 'Unable to add employee. Data does not match the defined model.';

}else {

    // Execute stored procedure
    try {
        $stmt->execute();
        // Set response code - 201 created
        http_response_code(201);

        echo "Employee has been added.";
    }
    catch(PDOException $exception) {
        // Set response code - 400 bad request
        // Show error if something goes wrong.
        http_response_code(400);
        echo "Unable to add employee. " . $exception->getMessage();
    }
}
?>
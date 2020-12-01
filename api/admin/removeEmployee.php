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
    echo 'Request method ' . $_SERVER["REQUEST_METHOD"] . ' not allowed';
    exit();
}

// Get data in JSON format.
$data = json_decode(file_get_contents("php://input"));

// Check if any paramters were passed and return that else return an empty string.
$empId = !empty($data->EmployeeId) ? $data->EmployeeId : '';

// Instantiate DB and connect
$database = new Database();
$db = $database->connect();

// SQL statement to call the stored proc. Positional paramaters - act as placeholders.
$sql = 'CALL RemoveEmployee(:empId)';

// Prepare for execution of stored procedure
$stmt = $db->prepare($sql);

// Clean up and sanitize data: remove html characters and strip any tags
$empId = htmlspecialchars(strip_tags($empId));

// Bind data
$stmt->bindParam('empId', $empId);

// Validate request:

// Check if the data is empty
if (empty($empId)) {

    // Set response code - 400 bad request
    http_response_code(400);

    echo 'Unable to remove employee. Data is incomplete.';

// Check data type
}else if (!(is_numeric($empId)) ) {

    // Set response code - 400 bad request
    http_response_code(400);

    echo 'Unable to remove employee. Data type is not correct.';

 // Make sure that the input data types (field type, length, etc.) matches model
}else if (strlen($empId) > 11) {

    // Set response code - 400 bad request
    http_response_code(400);

    echo 'Unable to remove employee. Data does not match the defined model.';

}else {

    // Execute stored procedure
    try {
        $stmt->execute();

        // Get row count
        $numOfRecords = $stmt->rowCount();
        if ($numOfRecords == 0) {
            echo 'No employee with that id. Nothing was removed.';
        }
        else {
            // Set response code - 200 ok
            http_response_code(200);
            echo "Employee has been removed.";
        }
    }
    catch(PDOException $exception) {
        // Set response code - 400 bad request
        // Show error if something goes wrong.
        http_response_code(400);
        echo "Unable to remove employee. " . $exception->getMessage();
    }
}
?>
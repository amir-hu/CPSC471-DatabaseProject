<?php
//CREATE PROCEDURE AddReport(
//    IN chldSIN VARCHAR(8)
//    , IN rptID INT
//    , IN empId INT
//    , IN rptDte DATE
//   , IN rptCmmnt VARCHAR(1000)
//    )
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

// Get data in JSON format.
$data = json_decode(file_get_contents("php://input"));

// Check if any paramters were passed and return that else return an empty string.
$chldSIN = !empty($data->ChildSIN) ? $data->ChildSIN : '';
$rptID = !empty($data->ReportId) ? $data->ReportId : '';
$empId = !empty($data->EmployeeId) ? $data->EmployeeId : '';
$rptDte = !empty($data->ReportDate) ? $data->ReportDate : '';
$rptCmmnt = !empty($data->ReportComment) ? $data->ReportComment : NULL;

// Instantiate DB and connect
$database = new Database();
$db = $database->connect();

// SQL statement to call the stored proc. Positional paramaters - act as placeholders.
$sql = 'CALL AddReport(:chldSIN, :rptID, :empId, :rptDte, :rptCmmnt)';

// Prepare for execution of stored procedure
$stmt = $db->prepare($sql);

// Clean up and sanitize data: remove html characters and strip any tags
$chldSIN = htmlspecialchars(strip_tags($chldSIN));
$rptID = htmlspecialchars(strip_tags($rptID));
$empId = htmlspecialchars(strip_tags($empId));
$rptDte = htmlspecialchars(strip_tags($rptDte));
$rptCmmnt = $rptCmmnt == NULL ? htmlspecialchars(strip_tags($rptCmmnt)) : NULL;

// Bind data
$stmt->bindParam(':chldSIN', $chldSIN);
$stmt->bindParam(':rptID', $rptID);
$stmt->bindParam(':empId', $empId);
$stmt->bindParam(':rptDte', $rptDte);
$stmt->bindParam(':rptCmmnt', $rptCmmnt);

// Validate request:

// Check if the data is empty
if (empty($chldSIN) || empty($rptID) || empty($empId) || empty($rptDte)) {

    // Set response code - 400 bad request
    http_response_code(400);

    echo "\nUnable to add report. Data is incomplete.";

    // Check data type
}else if ( !(is_numeric($chldSIN) & is_numeric($rptID) & is_numeric($empId) & is_string($rptDte)) ) {

    // Set response code - 400 bad request
    http_response_code(400);

    echo "\nUnable to add report. Data type is not correct.";

    // Make sure that the input length matches model
}else if (strlen($chldSIN) > 11 || strlen($rptID) > 8 || strlen($empId) > 8 || strlen($rptDte) > 10 || strlen($rptCmmnt) > 100) {

    // Set response code - 400 bad request
    http_response_code(400);

    echo "\nUnable to add report. Data does not match the defined model.";

}else {

    // Execute stored procedure
    try {
        $stmt->execute();
        // Set response code - 201 Created
        http_response_code(201);

        echo "\nReport has been added.";
    }
    catch(PDOException $exception) {
        // Set response code - 400 bad request
        // Show error if something goes wrong.
        http_response_code(400);
        echo "\nUnable to add report. " . $exception->getMessage();
    }
}
?>
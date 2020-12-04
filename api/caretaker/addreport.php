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
    $message = array('Message' => 'Request method ' . $_SERVER["REQUEST_METHOD"] . ' not allowed.');
    echo json_encode($message);
    exit();
}

// Get data in JSON format.
$data = json_decode(file_get_contents("php://input"));

// Check if any paramters were passed and return that else return an empty string.
$chldSIN = !empty($data->ChildSIN) ? $data->ChildSIN : '';
$rptID = !empty($data->ReportId) ? $data->ReportId : '';
$empId = !empty($data->EmployeeId) ? $data->EmployeeId : '';
$rptDte = !empty($data->ReportDate) ? $data->ReportDate : '';
$startTime = !empty($data->ScheduleStartTime) ? $data->ScheduleStartTime : '';
$endTime = !empty($data->ScheduleEndTime) ? $data->ScheduleEndTime : '';
$rptCmmnt = !empty($data->ReportComment) ? $data->ReportComment : NULL;
$lessonsLearned = !empty($data->LessonsLearned) ? $data->LessonsLearned : NULL;
$actionRequired = !empty($data->ActionRequired) ? $data->ActionRequired : NULL;

// Instantiate DB and connect
$database = new Database();
$db = $database->connect();
$database->authenticate("med");

// SQL statement to call the stored proc. Positional paramaters - act as placeholders.
$sql = 'CALL AddReport(:chldSIN, :rptID, :empId, :rptDte, :startTime, :endTime, :rptCmmnt, :lessonsLearned, :actionRequired)';

// Prepare for execution of stored procedure
$stmt = $db->prepare($sql);

// Clean up and sanitize data: remove html characters and strip any tags
$chldSIN = htmlspecialchars(strip_tags($chldSIN));
$rptID = htmlspecialchars(strip_tags($rptID));
$empId = htmlspecialchars(strip_tags($empId));
$rptDte = htmlspecialchars(strip_tags($rptDte));
$startTime = htmlspecialchars(strip_tags($startTime));
$endTime = htmlspecialchars(strip_tags($endTime));
$rptCmmnt = !is_null($rptCmmnt) ? htmlspecialchars(strip_tags($rptCmmnt)) : NULL;
$lessonsLearned = !is_null($lessonsLearned) ? htmlspecialchars(strip_tags($lessonsLearned)) : NULL;
$actionRequired = !is_null($actionRequired) ? htmlspecialchars(strip_tags($actionRequired)) : NULL;

// Bind data
$stmt->bindParam(':chldSIN', $chldSIN);
$stmt->bindParam(':rptID', $rptID);
$stmt->bindParam(':empId', $empId);
$stmt->bindParam(':rptDte', $rptDte);
$stmt->bindParam(':startTime', $startTime);
$stmt->bindParam(':endTime', $endTime);
$stmt->bindParam(':rptCmmnt', $rptCmmnt);
$stmt->bindParam(':lessonsLearned', $lessonsLearned);
$stmt->bindParam(':actionRequired', $actionRequired);

// Validate request:

// Check if the data is empty
if (empty($chldSIN) || empty($rptID) || empty($empId) || empty($rptDte) || empty($startTime) || empty($endTime)) {

    // Set response code - 400 bad request
    http_response_code(400);

    $message = array('Message' => "Unable to add report. Data is incomplete.");
    echo json_encode($message);

    // Check data type
}else if ( !(is_numeric($chldSIN) & is_numeric($rptID) & is_numeric($empId) & is_string($rptDte) & strtotime($startTime) != False & strtotime($endTime) != False) ) {

    // Set response code - 400 bad request
    http_response_code(400);

    $message = array('Message' => "Unable to add report. Data type is not correct.");
    echo json_encode($message);

    // Make sure that the input length matches model
}else if (strlen($chldSIN) > 11 || strlen($rptID) > 8 || strlen($empId) > 8 || strlen($rptDte) > 10 || strlen($rptCmmnt) > 1000
            || strlen($lessonsLearned) > 100 || strlen($actionRequired) > 100) {

    // Set response code - 400 bad request
    http_response_code(400);

    $message = array('Message' => "Unable to add report. Data length does not match the defined model.");
    echo json_encode($message);

}else {

    // Execute stored procedure
    try {
        $stmt->execute();
        // Set response code - 201 Created
        http_response_code(201);

        $message = array('Message' => "Report has been added.");
        echo json_encode($message);
    }
    catch(PDOException $exception) {
        // Set response code - 400 bad request
        // Show error if something goes wrong.
        http_response_code(400);
        $message = array('Message' => "Unable to add report. " . $exception->getMessage());
        echo json_encode($message);
    }
}
?>
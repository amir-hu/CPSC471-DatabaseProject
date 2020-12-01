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
    echo 'Request method ' . $_SERVER["REQUEST_METHOD"] . ' not allowed';
    exit();
}

$data = json_decode(file_get_contents("php://input"));

// Check if any paramters were passed and return that else return an empty string.
$crtkrId = !empty($data->crtkrId) ? $data->crtkrId : '';
$chldSIN = !empty($data->chldSIN) ? $data->chldSIN : '';
$rptdate = !empty($data->rptdate) ? $data->rptdate : '';

// Instantiate DB and connect
$database = new Database();
$db = $database->connect();

// SQL statement to call the stored proc. Positional paramaters - act as placeholders.
$sql = 'CALL CaretakerGetDailyReport(:crtkrId, :chldSIN, :rptdate)';

// Prepare for execution of stored procedure
$stmt = $db->prepare($sql);

// Clean up and sanitize data: remove html characters and strip any tags
$crtkrId = htmlspecialchars(strip_tags($crtkrId));
$chldSIN = htmlspecialchars(strip_tags($chldSIN));
$rptdate = htmlspecialchars(strip_tags($rptdate));

// Bind data
$stmt->bindParam(':crtkrId', $crtkrId);
$stmt->bindParam(':chldSIN', $chldSIN);
$stmt->bindParam(':rptdate', $rptdate);

// Validate request:

// Check if the data is empty
if ( empty($crtkrId) || empty($chldSIN)|| empty($rptdate)) {

    // Set response code - 400 bad request
    http_response_code(400);

    echo "\nUnable to get reports. Data is incomplete.";

// Check data type
}else if ( !(is_numeric($crtkrId)) || !(is_string($chldSIN)) || !(is_string($rptdate))) {

    // Set response code - 400 bad request
    http_response_code(400);

    echo "\nUnable to get reports. Data type is not correct.";

 // Make sure that the input length matches model
}else if (strlen($chldSIN) > 8 || strlen($crtkrId) > 11 || strlen($rptdate) > 10 ) {

    // Set response code - 400 bad request
    http_response_code(400);

    echo "\nUnable to get reports. Data does not match the defined model.";

}else {

    // Execute stored procedure
    try {

        $stmt->execute();

        // Set response code - 200 OK     
        http_response_code(200);
        // Returns all rows as an object
        $numOfRecords = $stmt->rowCount();
        if ($numOfRecords ==0){
            echo "\nNo reports for the specified parameters.";
        }
        else {
            $reportRows = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            // Turn to JSON & output
            echo json_encode($reportRows);
        }
        
        $stmt->closeCursor();

    }
    catch(PDOException $exception) {
        // Set response code - 400 bad request
        // Show error if something goes wrong.
        http_response_code(400);
        echo "Unable to get reports. Error:\n" . $exception->getMessage();
    }
}
?>
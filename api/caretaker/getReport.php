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
$crtkrId = !empty($data->CaretakerEmployeeId) ? $data->CaretakerEmployeeId : '';
$chldSIN = !empty($data->ChildSIN) ? $data->ChildSIN : '';
$rptdate = !empty($data->ReportDate) ? $data->ReportDate : '';
$limit = isset($_GET['limit']) ? $_GET['limit'] : '100';

// Instantiate DB and connect
$database = new Database();
$db = $database->connect();
$database->authenticate("med");

// SQL statement to call the stored proc. Positional paramaters - act as placeholders.
$sql = 'CALL CaretakerGetDailyReport(:crtkrId, :chldSIN, :rptdate)';

// Prepare for execution of stored procedure
$stmt = $db->prepare($sql);

// Clean up and sanitize data: remove html characters and strip any tags
$crtkrId = htmlspecialchars(strip_tags($crtkrId));
$chldSIN = htmlspecialchars(strip_tags($chldSIN));
$rptdate = htmlspecialchars(strip_tags($rptdate));
$limit = htmlspecialchars(strip_tags($limit));

// Bind data
$stmt->bindParam(':crtkrId', $crtkrId);
$stmt->bindParam(':chldSIN', $chldSIN);
$stmt->bindParam(':rptdate', $rptdate);

// Validate request:

// Check if the data is empty
if ( empty($crtkrId) || empty($chldSIN)|| empty($rptdate)) {

    // Set response code - 400 bad request
    http_response_code(400);

    $message = array('Message' => "Unable to get reports. Data is incomplete.");
    echo json_encode($message);

// Check data type
}else if ( !(is_numeric($crtkrId)) || !(is_string($chldSIN)) || !(is_string($rptdate)) || !(is_numeric($limit))) {

    // Set response code - 400 bad request
    http_response_code(400);

    $message = array('Message' => "Unable to get reports. Data type is not correct.");
    echo json_encode($message);

 // Make sure that the input length matches model
}else if (strlen($chldSIN) > 8 || strlen($crtkrId) > 11 || strlen($rptdate) > 10 ) {

    // Set response code - 400 bad request
    http_response_code(400);

    $message = array('Message' => "Unable to get reports. Data length does not match the defined model.");
    echo json_encode($message);

}else {

    // Execute stored procedure
    try {

        $stmt->execute();

        // Set response code - 200 OK
        http_response_code(200);

        // Returns all rows as an object
        $numOfRecords = $stmt->rowCount();

        if ($numOfRecords <= 0 || $limit <= 0) {
            $message = array('Message' => 'No reports available.');
            echo json_encode($message);
        }
        else if ($numOfRecords >= $limit) {
            // Set response code - 200 ok
            
            for ($x = 0; $x < $limit; $x++) {
                // Returns all rows as an object
                $conditionRows = $stmt->fetch(PDO::FETCH_OBJ);
                
                // Turn to JSON & output
                echo json_encode($conditionRows);                
            }
        }
        
        else 
        {
            // Returns all rows as an object
            $conditionRows = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            // Turn to JSON & output
            echo json_encode($conditionRows);
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
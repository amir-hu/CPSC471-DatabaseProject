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
$prntSIN = !empty($data->ParentSIN) ? $data->ParentSIN : '';
$limit = isset($_GET['limit']) ? $_GET['limit'] : '100';
// Instantiate DB and connect
$database = new Database();
$db = $database->connect();
$database->authenticate("low");

// SQL statement to call the stored proc. Positional paramaters - act as placeholders.
$sql = 'CALL GetChild(:prntSIN)';

$stmt = $db->prepare($sql);
// Clean up and sanitize data: remove html characters and strip any tags
$prntSIN = htmlspecialchars(strip_tags($prntSIN));
$limit = htmlspecialchars(strip_tags($limit));

// Bind data
$stmt->bindParam('prntSIN', $prntSIN);


if (empty($prntSIN)) {

    // Set response code - 400 bad request
    http_response_code(400);

    $message = array('Message' => 'Unable to retrive child. Data is incomplete.');
    echo json_encode($message);

    // Check data type
}else if (!(is_numeric($prntSIN)) || !(is_numeric($limit))) {

    // Set response code - 400 bad request
    http_response_code(400);

    $message = array('Message' => 'Unable to retrive child. Data type is not correct.');
    echo json_encode($message);

    // Make sure that the input data types (field type, length, etc.) matches model
}else if (strlen($prntSIN) > 8) {

    // Set response code - 400 bad request
    http_response_code(400);

    $message = array('Message' => 'Unable to retrive child. Data length does not match the defined model.');
    echo json_encode($message);

} else {

    // Prepare for execution of stored procedure


    // Execute stored procedure
    try {
        
        $stmt->execute();
        
        // Set response code - 200 OK
        http_response_code(200);
        
        // Returns all rows as an object
        $numOfRecords = $stmt->rowCount();
        
        if ($numOfRecords == 0 || $limit <= 0) {
            $message = array('Message' => 'No children.');
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
        $message = array('Message' => "Unable to retrieve list. " . $exception->getMessage());
        echo json_encode($message);
    }
}
?>
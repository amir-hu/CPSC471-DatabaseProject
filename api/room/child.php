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

// Get data in JSON format.
$data = json_decode(file_get_contents("php://input"));

// Check if any paramters were passed and return that else return an empty string.
$childSIN = !empty($data->ChildSIN) ? $data->ChildSIN : '';
$roomId = !empty($data->RoomId) ? $data->RoomId : '';

// Instantiate DB and connect
$database = new Database();
$db = $database->connect();

// SQL statement to call the stored proc. Positional paramaters - act as placeholders.
$sql = 'CALL AssignChild(:childSIN, :roomId)';

// Prepare for execution of stored procedure
$stmt = $db->prepare($sql);

// Clean up and sanitize data: remove html characters and strip any tags
$childSIN = htmlspecialchars(strip_tags($childSIN));
$roomId = htmlspecialchars(strip_tags($roomId));

// Bind data
$stmt->bindParam(':childSIN', $childSIN);
$stmt->bindParam(':roomId', $roomId);

// Validate request:

// Check if the data is empty
if (empty($childSIN) || empty($roomId)) {

    // Set response code - 400 bad request
    http_response_code(400);

    echo 'Unable to assign child to room. Data is incomplete.';

    // Check data type
}else if ( !(is_numeric($childSIN) & is_numeric($roomId)) ) {

    // Set response code - 400 bad request
    http_response_code(400);

    echo 'Unable to assign child to room. Data type is not correct.';

    // Make sure that the input length matches model
}else if (strlen($childSIN) > 11 || strlen($roomId) > 8) {

    // Set response code - 400 bad request
    http_response_code(400);

    echo 'Unable to assign child to room. Data does not match the defined model.';

}else {

    // Execute stored procedure
    try {
        $stmt->execute();
        // Set response code - 200 ok
        http_response_code(200);

        echo "Child has been assigned to room.";
    }
    catch(PDOException $exception) {
        // Set response code - 400 bad request
        // Show error if something goes wrong.
        http_response_code(400);
        echo "Unable to assign child to room. " . $exception->getMessage();
    }
}
?>
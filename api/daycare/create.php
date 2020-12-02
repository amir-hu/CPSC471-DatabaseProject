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

// Instantiate DB and connect
$database = new Database();
$db = $database->connect();
$database->authenticate("high");

// ---------------------------------------------------------- CONVERT TO JSON FORMAT ---------------------------------------------------------- //
// Get data that is gonna be in JSON format.
$data = json_decode(file_get_contents("php://input"));

// Check if any paramters were passed and return that else return an empty string.
$daycareName = !empty($data->DaycareName) ? $data->DaycareName : '';
$daycareAddress = !empty($data->DaycareAddress) ? $data->DaycareAddress : '';
$totalNumOfCaretakers = !empty($data->TotalNumOfCaretakers) ? $data->TotalNumOfCaretakers : '';

// In Postman, go to body tab and paste your input in json format ex)
/*
    {
        "DaycareName": "hakuna",
        "DaycareAddress": "matata",
        "TotalNumOfCaretakers": 16
    }
 */
// ---------------------------------------------------------- CONVERT TO JSON FORMAT ---------------------------------------------------------- //


// SQL statement to call the stored proc. Positional paramaters - act as placeholders.
$sql = 'CALL CreateDaycare(:daycareName, :daycareAddress, :totalNumOfCaretakers)';

// Prepare for execution of stored procedure
$stmt = $db->prepare($sql);

// Clean up and sanitize data: remove html characters and strip any tags
$daycareName = htmlspecialchars(strip_tags($daycareName));
$daycareAddress = htmlspecialchars(strip_tags($daycareAddress));
$totalNumOfCaretakers = htmlspecialchars(strip_tags($totalNumOfCaretakers));

// Bind data
$stmt->bindParam(':daycareName', $daycareName);
$stmt->bindParam(':daycareAddress', $daycareAddress);
$stmt->bindParam(':totalNumOfCaretakers', $totalNumOfCaretakers);

// Validate request:

// Check if the data is empty
if (empty($daycareName) || empty($daycareAddress) || empty($totalNumOfCaretakers)) {

    // Set response code - 400 bad request
    http_response_code(400);

    echo 'Unable to create daycare. Data is incomplete.';

    // Check data type
}else if ( ctype_digit($daycareName) || ctype_digit($daycareAddress) || !is_numeric($totalNumOfCaretakers) ) {

    // Set response code - 400 bad request
    http_response_code(400);

    echo 'Unable to create daycare. Data type is not correct.';

    // Make sure that the input length matches model
}else if (strlen($daycareName) > 100 || strlen($daycareAddress) > 100 || strlen($totalNumOfCaretakers) > 11) {

    // Set response code - 400 bad request
    http_response_code(400);

    echo 'Unable to create daycare. Data does not match the defined model.';

}else {

    // Execute stored procedure
    try {
        $stmt->execute();
        // Set response code - 201 created
        http_response_code(201);

        echo "Daycare has been created.";
    }
    catch(PDOException $exception) {
        // Set response code - 400 bad request
        // Show error if something goes wrong.
        http_response_code(400);
        echo "Unable to create daycare. " . $exception->getMessage();
    }
}
?>
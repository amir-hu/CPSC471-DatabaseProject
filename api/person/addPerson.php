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
    $message = array('Message' => 'Request method ' . $_SERVER["REQUEST_METHOD"] . ' not allowed.');
    echo json_encode($message);
    exit();
}

// Get data in JSON format.
$data = json_decode(file_get_contents("php://input"));

// Check if any paramters were passed and return that else return an empty string.
$personSIN = !empty($data->SIN) ? $data->SIN : '';
$firstName = !empty($data->FirstName) ? $data->FirstName : '';
$lastName = !empty($data->LastName) ? $data->LastName : '';
$gender = !empty($data->Gender) ? $data->Gender : '';
$addrUnitNum = !empty($data->AddrUnitNum) ? $data->AddrUnitNum : '';
$addrStreet = !empty($data->AddrStreet) ? $data->AddrStreet : '';
$addrCity = !empty($data->AddrCity) ? $data->AddrCity : '';
$addrPostalCode = !empty($data->AddrPostalCode) ? $data->AddrPostalCode : '';
$startDay = !empty($data->StartDay) ? $data->StartDay : NULL;
$startMonth = !empty($data->StartMonth) ? $data->StartMonth : NULL;
$startYear = !empty($data->StartYear) ? $data->StartYear : NULL;
$phnNum = !empty($data->PhoneNum) ? $data->PhoneNum : '';

// Instantiate DB and connect
$database = new Database();
$db = $database->connect();
$database->authenticate();

// SQL statement to call the stored proc. Positional paramaters - act as placeholders.
$sql = 'CALL AddPerson(:personSIN, :firstName, :lastName, :gender, :addrUnitNum, :addrStreet, :addrCity, :addrPostalCode, :startDay, :startMonth, :startYear, :phnNum)';

// Prepare for execution of stored procedure
$stmt = $db->prepare($sql);

// Clean up and sanitize data: remove html characters and strip any tags
$personSIN = htmlspecialchars(strip_tags($personSIN));
$firstName = htmlspecialchars(strip_tags($firstName));
$lastName = htmlspecialchars(strip_tags($lastName));
$gender = htmlspecialchars(strip_tags($gender));
$addrUnitNum = htmlspecialchars(strip_tags($addrUnitNum));
$addrStreet = htmlspecialchars(strip_tags($addrStreet));
$addrCity = htmlspecialchars(strip_tags($addrCity));
$addrPostalCode = htmlspecialchars(strip_tags($addrPostalCode));
$startDay = $startDay == NULL ? htmlspecialchars(strip_tags($startDay)) : NULL;
$startMonth =  $startMonth == NULL ? htmlspecialchars(strip_tags($startMonth)) : NULL;
$startYear = $startYear == NULL ? htmlspecialchars(strip_tags($startYear)) : NULL;
$phnNum = htmlspecialchars(strip_tags($phnNum));

// Bind data
$stmt->bindParam(':personSIN', $personSIN);
$stmt->bindParam(':firstName', $firstName);
$stmt->bindParam(':lastName', $lastName);
$stmt->bindParam(':gender', $gender);
$stmt->bindParam(':addrUnitNum', $addrUnitNum);
$stmt->bindParam(':addrStreet', $addrStreet);
$stmt->bindParam(':addrCity', $addrCity);
$stmt->bindParam(':addrPostalCode', $addrPostalCode);
$stmt->bindParam(':startDay', $startDay);
$stmt->bindParam(':startMonth', $startMonth);
$stmt->bindParam(':startYear', $startYear);
$stmt->bindParam(':phnNum', $phnNum);

// Validate request:

// Check if the data is empty
if (empty($personSIN) || empty($firstName) || empty($lastName) || empty($gender)
    || empty($addrUnitNum) || empty($addrStreet) || empty($addrCity) || empty($addrPostalCode)
    || /*empty($startDay) || empty($startMonth) || empty($startYear) ||*/ empty($phnNum)) {

    // Set response code - 400 bad request
    http_response_code(400);

    $message = array('Message' => 'Unable to add person. Data is incomplete.');
    echo json_encode($message);

    // Check data type
}else if ( !is_numeric($personSIN) || ctype_digit($firstName) || ctype_digit($lastName) || ctype_digit($gender)
            || !is_numeric($addrUnitNum) || ctype_digit($addrStreet) || ctype_digit($addrCity)
            /*|| !is_numeric($startDay) || ctype_digit($startMonth) || !is_numeric($startYear)*/ ) {

    // Set response code - 400 bad request
    http_response_code(400);

    $message = array('Message' => 'Unable to add person. Data type is not correct.');
    echo json_encode($message);

    // Make sure that the input length matches model
}else if ( strlen($personSIN) > 8 || strlen($firstName) > 30 || strlen($lastName) > 30 || strlen($gender) > 30
            || strlen($addrUnitNum) > 11 || strlen($addrStreet) > 50 || strlen($addrCity) > 20 || strlen($addrPostalCode) > 20
            || strlen($startDay) > 11 || strlen($startMonth) > 9 || strlen($startYear) > 11|| strlen($phnNum) > 20 ) {

    // Set response code - 400 bad request
    http_response_code(400);

    $message = array('Message' => 'Unable to add person. Data length does not match the defined model.');
    echo json_encode($message);

}else {

    // Execute stored procedure
    try {
        $stmt->execute();
        // Set response code - 201 created
        http_response_code(201);

        $message = array('Message' => "Person has been added.");
        echo json_encode($message);
    }
    catch(PDOException $exception) {
        // Set response code - 400 bad request
        // Show error if something goes wrong.
        http_response_code(400);
        $message = array('Message' => "Unable to add person. " . $exception->getMessage());
        echo json_encode($message);
    }
}
?>
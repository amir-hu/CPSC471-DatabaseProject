<?php

class Daycare {

    // Connection instance
    private $connection;

    // Database table name
    private $table = 'Daycare';

    // Table attributes
    public $daycareName;
    public $daycareAddress;
    public $totalNumOfCaretakers;

    // Constructor with database connection
    public function __construct($dbConnection) {
        $this->connection = $dbConnection;
    }

    // CRUD Methods:

    public function create(){

    }

    public function read() {

        // Create SQL query
        $sql = 'SELECT
                     DaycareName
                   , DaycareAddress
                   , TotalNumOfCaretakers
                FROM ' . $this->table
            . ' WHERE 1';

        $stmt = $this->connection->prepare($sql);

        if ($stmt->execute()) {
            echo 'SQL query successfully executed.';
        } else {
            echo 'Error executing SQL query.';
        }

        return $stmt;
    }

    public function update(){

    }

    public function delete(){

    }

}

?>
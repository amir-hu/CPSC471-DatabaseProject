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

    public function read(){

    }

    public function update(){

    }

    public function delete(){

    }

}

?>
<?php
require_once '../config/db_connection.php';


class ClassManagemetnFunctions {

 private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

function insertIntoClassAndCreateBench($departmentid,$classname,$num_tables)
{
   $sql = "INSERT INTO class ( class_name,dept_id ,benchnum) VALUES (?, ?, ?)";
   $stmt = $this->conn->prepare($sql);
   $stmt->bind_param("sii",$classname,$departmentid,$num_tables);
    if ($stmt->execute()) {
        // Get the last inserted ID
        $insert_id = $this->conn->insert_id;
        echo $insert_id;
        
        for ($i=1; $i <=$num_tables ; $i++) { 
        	$benchSql = "INSERT INTO bench (bench_no,class_id) VALUES(?,?)";
        	$benchStmt = $this->conn->prepare($benchSql);
        	$benchStmt->bind_param("ii",$i,$insert_id);
        	$benchStmt->execute();
        	$benchStmt->close();
        }
        echo "Class and benches are inserted";
    }
    else{
    	echo "Error in insertion".$stmt->error;
    }
    






}

    public function getAllClasses()
    {
            $result  = $this->conn->query("SELECT * from class");
            $val = $result->fetch_all(MYSQLI_ASSOC);
            return $val;
    }

}


?>
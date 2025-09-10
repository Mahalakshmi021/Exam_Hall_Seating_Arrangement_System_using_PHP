<?php
require_once '../config/db_connection.php';

class DeptMgntFunctions {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }
    public function getAllDepartments()
    {
    $sql = "SELECT 
                d.department_id, 
                d.department_name, 
                b.block_id, 
                b.block_name 
            FROM department d
            JOIN block b ON d.block_id = b.block_id";

    $result = $this->conn->query($sql);

    // Check if the query executed successfully
    if (!$result) {
        die("Query failed: " . $this->conn->error); // Debugging purpose
    }

    $departments = [];
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row;
    }
    return $departments;
    }

public function deleteDepartment($department_id) {
    // Sanitize input to prevent SQL Injection
    $department_id = intval($department_id);

    $sql = "DELETE FROM department WHERE department_id = $department_id";

    if ($this->conn->query($sql) === TRUE) {
        return true; // Successfully deleted
    } else {
        return "Error deleting department: " . $this->conn->error;
    }
}


  // Function to insert a department
    public function insertDepartment($department_name, $block_id, $user_id) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO department (department_name, block_id, user_id) VALUES (?, ?, ?)");
            if (!$stmt) {
                die("Prepare failed: " . $this->conn->error);
            }
            $stmt->bind_param("sii", $department_name, $block_id, $user_id);
            if ($stmt->execute()) {
                return true;
            } else {
                return "Failed to insert department: " . $stmt->error;
            }
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }
             public function getDeptById($dept_id) {
            try {
                $stmt = $this->conn->prepare("SELECT * FROM deptartment WHERE dept_id = ?");
                if (!$stmt) {
                    die("Prepare failed: " . $this->conn->error);
                }
                $stmt->bind_param("i", $dept_id);
                $stmt->execute();
                $result = $stmt->get_result();
                return $result->fetch_assoc();
            } catch (Exception $e) {
                return null;
            }
      }
    
        public function updateDept($dept_id, $block_name, $user_id) {
        try {
            $stmt = $this->conn->prepare("UPDATE block SET block_name = ?, user_id = ? WHERE block_id = ?");
            if (!$stmt) {
                die("Prepare failed: " . $this->conn->error);
            }
            $stmt->bind_param("siis", $dept_name, $user_id, $block_id, $block_name);
            if ($stmt->execute()) {
                return true;
            } else {
                return "Failed to update block: " . $stmt->error;
            }
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
   }

    // Delete a block
    public function deleteDept($dept_id) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM department WHERE dept_id = ?");
            if (!$stmt) {
                die("Prepare failed: " . $this->conn->error);
            }
            $stmt->bind_param("i", $dept_id);
            if ($stmt->execute()) {
                return true;
            } else {
                return "Failed to delete block: " . $stmt->error;
            }
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }
}
?>

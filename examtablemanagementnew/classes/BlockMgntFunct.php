<?php
require_once '../config/db_connection.php';

class BlockMgntFunct {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function insertBlock($name, $userid) {
        try {
            // Prepare statement using MySQLi
            $stmt = $this->conn->prepare("INSERT INTO block (block_name, user_id) VALUES (?, ?)");
            
            if (!$stmt) {
                die("Prepare failed: " . $this->conn->error);
            }

            // Bind parameters
            $stmt->bind_param("si", $name, $userid); // "s" for string, "i" for integer
            
            // Execute statement
            if ($stmt->execute()) {
                return true;
            } else {
                return "Failed to insert block: " . $stmt->error;
            }
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }
    public function getAllBlocks()
    {
            $result  = $this->conn->query("SELECT * from block");
            $val = $result->fetch_all(MYSQLI_ASSOC);
            return $val;
    }

    public function getBlockById($block_id) {
    try {
        $stmt = $this->conn->prepare("SELECT * FROM block WHERE block_id = ?");
        if (!$stmt) {
            die("Prepare failed: " . $this->conn->error);
        }
        $stmt->bind_param("i", $block_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    } catch (Exception $e) {
        return null;
    }
}

public function updateBlock($block_id, $block_name, $user_id) {
    try {
        $stmt = $this->conn->prepare("UPDATE block SET block_name = ?, user_id = ? WHERE block_id = ?");
        if (!$stmt) {
            die("Prepare failed: " . $this->conn->error);
        }
        $stmt->bind_param("sii", $block_name, $user_id, $block_id);
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
    public function deleteBlock($block_id) {
        echo "string".$block_id;
        try {
            $stmt = $this->conn->prepare("DELETE FROM block WHERE block_id = ?");
            if (!$stmt) {
                die("Prepare failed: " . $this->conn->error);
            }
            $stmt->bind_param("i", $block_id);
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

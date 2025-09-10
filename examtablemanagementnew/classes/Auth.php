<?php
require_once '../config/db_connection.php';

class Auth {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }




    public function getUserByUsername($username) {
        $stmt = $this->conn->prepare("SELECT * FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function storeOTP($username, $otp) {
        $otpExpiry = date('Y-m-d H:i:s', strtotime('+10 minutes')); // OTP valid for 10 minutes
        $stmt = $this->conn->prepare("UPDATE user SET otp = ?, otp_expiry = ? WHERE username = ?");
        $stmt->bind_param("sss", $otp, $otpExpiry, $username);
        $stmt->execute();
    }

    public function verifyOTP($username, $otp) {
        $stmt = $this->conn->prepare("SELECT * FROM user WHERE username = ? AND otp = ?");
        $stmt->bind_param("ss", $username, $otp);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    public function updatePassword($username, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("UPDATE user SET password = ?, otp = NULL, otp_expiry = NULL WHERE username = ?");
        $stmt->bind_param("ss", $hashedPassword, $username);
        $stmt->execute();
    }

    public function login($username, $password) {
        $query = "SELECT * FROM user WHERE username = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        // Verify password if user exists
        if ($user && password_verify($password, $user['password'])) {
            return $user; // Return user data on successful login
        } else {
            return false; // Login failed
        }
    }

   public function register($name, $email, $register_number, $password, $course, $semester,$departmentid) {
    // Hash the password before storing it
    $hashPwd = password_hash($password, PASSWORD_DEFAULT);

    // First query: Insert into `user` table
    $stmt = $this->conn->prepare("INSERT INTO user (username, password, usertype) VALUES (?, ?, ?)");
    if (!$stmt) {
        return false;
    }

    $userType = 'user'; // Create a variable for usertype
    $stmt->bind_param("sss", $email, $hashPwd, $userType);

    if ($stmt->execute()) {
        // Get the last inserted ID
        $insert_id = $this->conn->insert_id;
        $stmt->close();

        // Second query: Insert into `students` table
        $stmt = $this->conn->prepare("INSERT INTO students(user_id, name, email_id, reg_number, course, semester,department) VALUES(?, ?, ?, ?, ?, ?,?)");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("isssssi", $insert_id, $name, $email, $register_number, $course, $semester,$departmentid);

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            $stmt->close();
            return false;
        }
    } else {
        $stmt->close();
        return false;
    }
}
public function getStudets($semester)

{
      $query = "SELECT * FROM students WHERE semester = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $semester);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user;

} 
 public function getStudentsBySemester($semester) {
        $query = "SELECT * FROM students WHERE semester = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $semester);
        $stmt->execute();
        $result = $stmt->get_result();
        $students = $result->fetch_all(MYSQLI_ASSOC); // ✅ Fetch all students
        $stmt->close();
        return $students;
    }

    // ✅ Get a single student by ID
    public function getStudentById($student_id) {
        $query = "SELECT * FROM students WHERE student_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $student = $result->fetch_assoc(); // ✅ Fetch one student
        $stmt->close();
        return $student;
    }

 public function getSemesters() {
        $query = "SELECT * FROM semester";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $semesters = $result->fetch_all(MYSQLI_ASSOC); // ✅ Fetch all semesters
        $stmt->close();
        return $semesters;
    }
 public function updateStudent($studentId, $name, $email, $semester) {
    $query = "UPDATE students SET name = ?, email_id = ?, semester = ? WHERE student_id = ?";
    $stmt = $this->conn->prepare($query);
    
    if (!$stmt) {
        die("Query preparation failed: " . $this->conn->error);
    }

    $stmt->bind_param("ssii", $name, $email, $semester, $studentId);

    if ($stmt->execute()) {
        $stmt->close();
        return true;
    } else {
        $stmt->close();
        return false;
    }
}

// Return user data on successful login
       
/*    public function allocation($bench, $department, $registernumber, $subjectname, $coursecode) {
        // Prepare the SQL query using prepared statements to prevent SQL injection
        $stmt = $this->conn->prepare("INSERT INTO seating_allocation (bench, department, registernumber, subjectname, coursecode) VALUES (?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            return false; // SQL error
        }

        // Bind parameters
        $stmt->bind_param("sssss", $bench, $department, $registernumber, $subjectname, $coursecode);

        // Execute and check for success
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            $stmt->close();
            return false;
        }
    }
    public function location($seatnumber, $classcapcity) {
        // Assuming you have a table named 'seating_location' with columns seatnumber and classcapcity
        $stmt = $this->conn->prepare("INSERT INTO seating_location (seatnumber, classcapcity) VALUES (?, ?)");
        
        if (!$stmt) {
            return false; // Handle SQL error appropriately
        }

        // Bind the parameters (adjust the type if classcapcity should be numeric, e.g., "si" for string and integer)
        $stmt->bind_param("ss", $seatnumber, $classcapcity);

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            $stmt->close();
            return false;
        }
    }
    public function addExamDetails($coursecode, $examduration) {
        $stmt = $this->conn->prepare("INSERT INTO exams (coursecode, examduration) VALUES (?, ?)");
        if (!$stmt) {
            // Optionally log error: $this->conn->error
            return false;
        }

        // Bind parameters; adjust the types if examduration is numeric, e.g., "si" for string and integer
        $stmt->bind_param("ss", $coursecode, $examduration);

        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            // Optionally log error: $stmt->error
            $stmt->close();
            return false;
        }
    }*/
}

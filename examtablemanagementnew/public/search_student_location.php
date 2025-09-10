<?php
require_once '../config/db_connection.php'; // Include the database connection

// Interface for database operations
interface ExamAllocationInterface {
    public function getRowAndLocationInfo(string $regnum, string $exam_type): ?array;
}

// Class implementing the interface
class ExamAllocation implements ExamAllocationInterface {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getRowAndLocationInfo(string $regnum, string $exam_type): ?array {
        $stmt = $this->conn->prepare("SELECT a.*, c.class_name, d.department_name, b.block_name,
            CASE 
                WHEN a.odd_loc = ? THEN 'odd_loc'
                WHEN a.even_loc = ? THEN 'even_loc'
            END AS location_type
            FROM allocation a
            JOIN class c ON a.class_id = c.class_id
            JOIN department d ON c.dept_id = d.department_id
            JOIN block b ON d.block_id = b.block_id
            WHERE (a.odd_loc = ? OR a.even_loc = ?) AND a.exam_type = ?");
        
        $stmt->bind_param("sssss", $regnum, $regnum, $regnum, $regnum, $exam_type);
        $stmt->execute();
        $allocation = $stmt->get_result()->fetch_assoc();
        
        if (!$allocation) return null;

        $studentRegNum = ($allocation['location_type'] === 'odd_loc') ? $allocation['odd_loc'] : $allocation['even_loc'];
        
        $stmt = $this->conn->prepare("SELECT name, reg_number FROM students WHERE reg_number = ?");
        $stmt->bind_param("s", $studentRegNum);
        $stmt->execute();
        $student = $stmt->get_result()->fetch_assoc();

        return [
            'student_name' => $student['name'] ?? 'Unknown',
            'regnumber' => $student['reg_number'] ?? 'N/A',
            'class_name' => $allocation['class_name'],
            'department_name' => $allocation['department_name'],
            'block_name' => $allocation['block_name'],
            'bench_no' => $allocation['bench_no'],
            'location_type' => $allocation['location_type'],
            'seat_number' => ($allocation['location_type'] === 'odd_loc') 
                ? $allocation['bench_no'] * 2 - 1 
                : $allocation['bench_no'] * 2
        ];
    }

    public function __destruct() {
        if ($this->conn) $this->conn->close();
    }
}

$examAllocation = new ExamAllocation($conn);
$row = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $registernumber = $_POST['registernumber'] ?? '';
    $exam_type = $_POST['exam_type'] ?? '';
    $row = $examAllocation->getRowAndLocationInfo($registernumber, $exam_type);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Exam Location</title>
    <link rel="stylesheet" type="text/css" href="css/studentlocstyle.css">
</head>
<body>
    <div class="container">
        <div class="hall-ticket">
            <div class="header">
                <h2>Find Your Exam Seat</h2>
            </div>

            <form method="POST" action="">
                <label for="exam_type">Exam Type:</label>
                <select id="exam_type" name="exam_type" required>
                    <option value="" disabled selected>Select Exam Type</option>
                    <option value="FirstSeries">First Series</option>
                    <option value="SecondSeries">Second Series</option>
                    <option value="University">University Exam</option>
                </select>
                <br><br>
                <label for="registernumber">Registration Number:</label>
                <input type="text" id="registernumber" name="registernumber" required>
                <br><br>
                <button type="submit">Search</button>
            </form>
            
            <?php if ($row): ?>
                <div class="details">
                    <h2>Student Exam Details</h2>
                    <p><strong>Student Name:</strong> <?= htmlspecialchars($row['student_name']) ?></p>
                    <p><strong>Registration Number:</strong> <?= htmlspecialchars($row['regnumber']) ?></p>
                    <p><strong>Class Name:</strong> <?= htmlspecialchars($row['class_name']) ?></p>
                    <p><strong>Block Name:</strong> <?= htmlspecialchars($row['block_name']) ?></p>
                    <p><strong>Department:</strong> <?= htmlspecialchars($row['department_name']) ?></p>
                    <p><strong>Bench Number:</strong> <?= htmlspecialchars($row['bench_no']) ?></p>
                    <p><strong>Seat Location:</strong> <?= htmlspecialchars($row['seat_number']) ?></p>
                </div>
            <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                <p style='color:red;'>No record found for the given registration number and exam type.</p>
            <?php endif; ?>
            
            <div class="footer">
                <p>YOU CAN FIND YOUR SEATING LOCATION HERE!</p>
            </div>
        </div>
    </div>
</body>
</html>

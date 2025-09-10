<?php
require_once '../config/db_connection.php'; // Include the database connection

// Interface for database operations
interface ExamAllocationInterface {
    public function getRowAndLocationInfo(string $regnum, string $exam_type): ?array;
    public function getAllAllocations(string $exam_type): array;
}

// Class implementing the interface
class ExamAllocation implements ExamAllocationInterface {
    private $conn;

    // Constructor to accept the database connection
    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Fetch row and determine the correct location
public function getRowAndLocationInfo(string $regnum, string $exam_type): ?array {
    // First determine if we're looking for odd or even location
    $stmt = $this->conn->prepare("
        SELECT 
            a.*,
            c.class_name,
            d.department_name,
            b.block_name,
            CASE 
                WHEN a.odd_loc = ? THEN 'odd_loc'
                WHEN a.even_loc = ? THEN 'even_loc'
            END AS location_type
        FROM allocation a
        JOIN class c ON a.class_id = c.class_id
        JOIN department d ON c.dept_id = d.department_id
        JOIN block b ON d.block_id = b.block_id
        WHERE (a.odd_loc = ? OR a.even_loc = ?) AND a.exam_type = ?
    ");
    $stmt->bind_param("sssss", $regnum, $regnum, $regnum, $regnum, $exam_type);
    $stmt->execute();
    $allocation = $stmt->get_result()->fetch_assoc();
    
    if (!$allocation) {
        return null;
    }

    // Now get the specific student's details
    $studentRegNum = ($allocation['location_type'] === 'odd_loc') 
        ? $allocation['odd_loc'] 
        : $allocation['even_loc'];
    
    $stmt = $this->conn->prepare("
        SELECT name, reg_number 
        FROM students 
        WHERE reg_number = ?
    ");
    $stmt->bind_param("s", $studentRegNum);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();

    // Combine the results
    return [
        'id' => $allocation['id'],
        'class_id' => $allocation['class_id'],
        'examdept1' => $allocation['examdept1'],
        'bench_id' => $allocation['bench_id'],
        'bench_no' => $allocation['bench_no'],
        'examdept2' => $allocation['examdept2'],
        'exam_type' => $allocation['exam_type'],
        'even_loc' => $allocation['even_loc'],
        'odd_loc' => $allocation['odd_loc'],
        'student_name' => $student['name'],
        'regnumber' => $student['reg_number'],
        'class_name' => $allocation['class_name'],
        'department_name' => $allocation['department_name'],
        'block_name' => $allocation['block_name'],
        'location_type' => $allocation['location_type'],
        'seat_number' => ($allocation['location_type'] === 'odd_loc') 
            ? $allocation['bench_no'] * 2 - 1 
            : $allocation['bench_no'] * 2
    ];
}

    // Fetch all student allocations for a specific exam type
    public function getAllAllocations(string $exam_type): array {
        $stmt = $this->conn->prepare("
            SELECT a.id, a.bench_no,a.exam_type, CASE WHEN t.location = 'even' THEN a.even_loc ELSE a.odd_loc END AS reg_number, CASE WHEN t.location = 'even' THEN s1.name ELSE s2.name END AS student_name, c.class_name, d.department_name, b.block_name, CASE WHEN t.location = 'even' THEN a.bench_no * 2 ELSE a.bench_no * 2 - 1 END AS seat_number, t.location AS location_type FROM allocation a CROSS JOIN (SELECT 'even' AS location UNION SELECT 'odd') t LEFT JOIN students s1 ON a.even_loc = s1.reg_number AND t.location = 'even' LEFT JOIN students s2 ON a.odd_loc = s2.reg_number AND t.location = 'odd' JOIN class c ON a.class_id = c.class_id JOIN department d ON c.dept_id = d.department_id JOIN block b ON d.block_id = b.block_id AND ( (t.location = 'even' AND a.even_loc IS NOT NULL) OR (t.location = 'odd' AND a.odd_loc IS NOT NULL) ) ORDER BY a.bench_no, t.location");
        $stmt->execute();
        $result = $stmt->get_result();

        $allocations = [];
        while ($row = $result->fetch_assoc()) {
            $allocations[] = $row;
        }
        return $allocations;
    }

    // Destructor to close database connection (optional)
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

// Instantiate the class with the existing connection
$examAllocation = new ExamAllocation($conn);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $registernumber = $_POST['registernumber'];
    $exam_type = $_POST['exam_type'];

    // Fetch row and location info
    $row = $examAllocation->getRowAndLocationInfo($registernumber, $exam_type);

    if ($row) {
        echo "<h2>Student Exam Details</h2>";
        echo "<p>Student Name: <strong>" . htmlspecialchars($row['student_name']) . "</strong></p>";
        echo "<p>Registration Number: <strong>" . htmlspecialchars($row['regnumber']) . "</strong></p>";
        echo "<p>Class Name: <strong>" . htmlspecialchars($row['class_name']) . "</strong></p>";
        echo "<p>Block Name: <strong>" . htmlspecialchars($row['block_name']) . "</strong></p>";
        echo "<p>Department: <strong>" . htmlspecialchars($row['department_name']) . "</strong></p>";
        echo "<p>Bench Number: <strong>" . htmlspecialchars($row['bench_no']) . "</strong></p>";

        // Calculate seat location based on location_type
        $benchpos = ($row['location_type'] === 'odd_loc') ? intval($row['bench_no']) * 2 - 1 : intval($row['bench_no']) * 2;
        echo "<p>Seat Location: <strong>" . $benchpos . "</strong></p>";
    } else {
        echo "<p style='color:red;'>No record found for the given registration number and exam type.</p>";
    }
}

// Fetch all student allocations for display
$exam_type_selected = $_POST['exam_type'] ?? 'FirstSeries'; // Default selection
$allAllocations = $examAllocation->getAllAllocations($exam_type_selected);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Location</title>
    <script>
     function goToHome() {
            window.location.replace("dashboard.php"); // Change "home.php" to your actual home page
        }
    </script>
    <link rel="stylesheet" href="css/exam_locationstyle.css"> 
    <script>
        function printPage() {
            var printContent = document.getElementById('printable').innerHTML;
            var newWindow = window.open('', '', 'width=800,height=600');
            newWindow.document.write('<html><head><title>Print</title></head><body>');
            newWindow.document.write(printContent);
            newWindow.document.write('</body></html>');
            newWindow.document.close();
            newWindow.print();
        }
    </script>
</head>
<body>
    <h1>Exam Location</h1>
    <form method="POST" action="">
        <label for="exam_type">Exam Type:</label>
        <select id="exam_type" name="exam_type" required>
            <option value="FirstSeries" <?= ($exam_type_selected == "FirstSeries") ? "selected" : "" ?>>First Series</option>
            <option value="SecondSeries" <?= ($exam_type_selected == "SecondSeries") ? "selected" : "" ?>>Second Series</option>
            <option value="University" <?= ($exam_type_selected == "University") ? "selected" : "" ?>>Final Exam</option>
        </select>
        <br><br>
        <label for="registernumber">Registration Number:</label>
        <input type="text" id="registernumber" name="registernumber" required>
        <br><br>
        <button type="submit">Search</button>
    </form>

    <hr>

    <h2>All Student Allocations for <?= htmlspecialchars($exam_type_selected) ?></h2>
    <div id="printable">
        <table border="1" cellspacing="0" cellpadding="5">
            <tr>
                <th>Student Name</th>
                <th>Reg No</th>
                <th>Class Name</th>
                <th>Block</th>
                <th>Bench No</th>
                <th>Exam Type</th>
            </tr>
            <?php foreach ($allAllocations as $allocation) : ?>
            <tr>
                <td><?= htmlspecialchars($allocation['student_name']) ?></td>
                <td><?= htmlspecialchars($allocation['reg_number']) ?></td>
                <td><?= htmlspecialchars($allocation['class_name']) ?></td>
                <td><?= htmlspecialchars($allocation['block_name']) ?></td>
                <td><?= htmlspecialchars($allocation['bench_no']) ?></td>
                <td><?= htmlspecialchars($allocation['exam_type']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <br>
    <button onclick="printPage()">Print</button>
</body>
</html>
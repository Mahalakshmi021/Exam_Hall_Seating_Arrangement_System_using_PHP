<?php
session_start();
require_once '../config/db_connection.php';
require_once '../classes/Auth.php';
require_once '../classes/DeptMgntFunctions.php';
require_once '../classes/ClassManagemetnFunctions.php';
require_once '../classes/AllocationManagementFunction.php';

// Initialize classes
$auth = new Auth($conn);
$deptMgntFunctions = new DeptMgntFunctions($conn);
$classManagemetnFunctions = new ClassManagemetnFunctions($conn);
$allocationManagemetnFunctions = new AllocationManagementFunction($conn);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize user input
    $selectdept2 = trim($_POST['selectdept2']);
    $selectdept1 = trim($_POST['selectdept1']);
    $exam_type = $_POST['exam_type'];
    $examdept1  = $_POST['examdept1'];
    $examdept2  = $_POST['examdept2'];
    $semesterdept1 = $_POST['semesterdept1'];
    $semesterdept2 = $_POST['semesterdept2'];
    $selectclass = $_POST['selectclass'];
    $selectedStudentsDept1 = $_POST['selectsudentdept1'] ?? [];
    $selectedStudentsDept2 = $_POST['selectsudentdept2'] ?? [];

    // Debugging: Log selected students
    error_log("Selected Students Dept1: " . implode(", ", $selectedStudentsDept1));
    error_log("Selected Students Dept2: " . implode(", ", $selectedStudentsDept2));

    // Call the allocation function
    $registration = $allocationManagemetnFunctions->insertSeatingAllocation(
        $selectdept1, $selectdept2, $exam_type, $examdept1, $examdept2, 
        $semesterdept1, $semesterdept2, $selectclass, $selectedStudentsDept1, $selectedStudentsDept2
    );

    if ($registration) {
        // Store registration session data
        header("Location: seatingallocation.php");
        exit();
    } else {
        $_SESSION['error'] = "Allocation Unsuccessful";
        header("Location: seatingallocation.php"); // Redirect to refresh the page
        exit();
    }
}

// Fetch data for the form
$classes = $classManagemetnFunctions->getAllClasses();
$departments = $deptMgntFunctions->getAllDepartments();
$error = isset($_SESSION['error']) ? $_SESSION['error'] : "";
unset($_SESSION['error']); // Clear the error after displaying it
?>

<!DOCTYPE html>
<html>
<head>
    <title>Seating Allocation</title>
    <link rel="stylesheet" href="css/allocstyle.css">
    <script>
        function goToHome() {
            window.location.replace("dashboard.php"); // Change "home.php" to your actual home page
        }

        function loadStudents(dept) {
            console.log("loadStudents called for:", dept); // Debugging line
            var departmentId, semester, studentDropdown;

            if (dept === 'dept1') {
                departmentId = document.getElementById('selectdept1').value;
                semester = document.getElementById('semesterdept1').value;
                studentDropdown = document.getElementById('selectsudentdept1');
            } else if (dept === 'dept2') {
                departmentId = document.getElementById('selectdept2').value;
                semester = document.getElementById('semesterdept2').value;
                studentDropdown = document.getElementById('selectsudentdept2');
            }

            console.log("Department ID:", departmentId, "Semester:", semester); // Debugging line

            if (departmentId && semester) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "fetch_students.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        console.log("Response:", xhr.responseText); // Debugging line
                        var students = JSON.parse(xhr.responseText);

                        // Clear existing options except the placeholder
                        studentDropdown.innerHTML = '<option value="" disabled selected>Select Students</option>';

                        // Add new options
                        students.forEach(function(student) {
                            var option = document.createElement('option');
                            option.value = student.reg_number;
                            option.textContent = student.name;
                            studentDropdown.appendChild(option);
                        });
                    }
                };
                xhr.send("department_id=" + departmentId + "&semester=" + semester);
            }
        }

        function loadExams() {
            var examType = document.getElementById('exam_type').value;
            var examDropdown1 = document.getElementById('examdept1');
            var examDropdown2 = document.getElementById('examdept2');

            if (examType) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "fetch_exams.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        var exams = JSON.parse(xhr.responseText);

                        // Populate examdept1
                        examDropdown1.innerHTML = '<option value="">Select Exam Dept1</option>';
                        exams.forEach(function(exam) {
                            var option = document.createElement('option');
                            option.value = exam.exam_id;
                            option.textContent = exam.exam_name;
                            examDropdown1.appendChild(option);
                        });

                        // Populate examdept2
                        examDropdown2.innerHTML = '<option value="">Select Exam Dept2</option>';
                        exams.forEach(function(exam) {
                            var option = document.createElement('option');
                            option.value = exam.exam_id;
                            option.textContent = exam.exam_name;
                            examDropdown2.appendChild(option);
                        });
                    }
                };
                xhr.send("exam_type=" + examType);
            } else {
                examDropdown1.innerHTML = '<option value="">Select Exam Dept1</option>';
                examDropdown2.innerHTML = '<option value="">Select Exam Dept2</option>';
            }
        }
    </script>
</head>
<body>
 
 <div class="sidebar">
        <?php include 'sidebar.php';?>
    </div>
    <div class="title-bar">
        <h1>Seating Allocation</h1>
        <button onclick="goToHome()">Home</button>
    </div>
   
    <div class="content">
        <div class="container">
            <div class="create-seating-allocation-form">
                <h2>Create Seating Allocation</h2>
                <form id="seating_allocation" method="POST" action="seatingallocation.php">
                    <label for="exam_type">Select Exam Type</label>
                    <select id="exam_type" name="exam_type" onchange="loadExams()">
                        <option value="">Select Exam Type</option>
                        <option value="FirstSeries">First Series Examination</option>
                        <option value="SecondSeries">Second Series Examination</option>
                        <option value="University">University Examination</option>
                    </select>

                    <label for="examdept1">Select Odd Location Dept Exam Subject</label>
                    <select id="examdept1" name="examdept1" required>
                        <option value="">Select Exam Subject</option>
                    </select>

                    <label for="examdept2">Select Even Location Dept Exam Subject</label>
                    <select id="examdept2" name="examdept2" required>
                        <option value="">Select Exam Subject</option>
                    </select>

                    <label for="selectdept1">Select Odd Location Department</label>
                    <select name="selectdept1" id="selectdept1" onchange="loadStudents('dept1')" required>
                        <option value="">Select Dept1</option>
                        <?php foreach ($departments as $department): ?>
                            <option value="<?php echo htmlspecialchars($department['department_id']); ?>">
                                <?php echo htmlspecialchars($department['department_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="selectdept2">Select Even Location Department</label>
                    <select name="selectdept2" id="selectdept2" onchange="loadStudents('dept2')" required>
                        <option value="">Select Dept2</option>
                        <?php foreach ($departments as $department): ?>
                            <option value="<?php echo htmlspecialchars($department['department_id']); ?>">
                                <?php echo htmlspecialchars($department['department_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="selectclass">Select class to assign</label>
                    <select name="selectclass" id="selectclass" required>
                        <option value="">Select class</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo htmlspecialchars($class['class_id']); ?>">
                                <?php echo htmlspecialchars($class['class_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="semesterdept1">Select Semester Dept1</label>
                    <select name="semesterdept1" id="semesterdept1" onchange="loadStudents('dept1')" required>
                        <option value="">Select Semester Dept1</option>
                        <?php for ($i = 1; $i <= 8; $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?> Semester</option>
                        <?php endfor; ?>
                    </select>

                    <label for="semesterdept2">Select Semester Dept2</label>
                    <select name="semesterdept2" id="semesterdept2" onchange="loadStudents('dept2')" required>
                        <option value="">Select Semester Dept2</option>
                        <?php for ($i = 1; $i <= 8; $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?> Semester</option>
                        <?php endfor; ?>
                    </select>

                    <label for="selectsudentdept1">Students Dept1:</label>
                    <select name="selectsudentdept1[]" id="selectsudentdept1" multiple required style="height: 200px; overflow-y: auto;">
                        <option value="" disabled selected>Select Students</option>
                    </select>

                    <label for="selectsudentdept2">Students Dept2:</label>
                    <select name="selectsudentdept2[]" id="selectsudentdept2" multiple required style="height: 200px; overflow-y: auto;">
                        <option value="" disabled selected>Select Students</option>
                    </select>

                    <input type="submit" value="Create Seating Allocation">
                </form>
            </div>
        </div>
    </div>

    <style>
        .sidebar {
    width: 260px;
    background: #2c3e50;
    color: white;
    padding-top: 20px;
    height: 100%;
    position: fixed;
    transition: 0.3s;
}

.sidebar h2 {
    text-align: center;
    margin-bottom: 20px;
    font-weight: 600;
}

.sidebar a {
    display: flex;
    align-items: center;
    padding: 15px;
    color: white;
    text-decoration: none;
    font-size: 18px;
    transition: 0.3s;
}

.sidebar a i {
    width: 30px;
    font-size: 20px;
}

.sidebar a:hover {
    background-color: #3498db;
    padding-left: 20px;
}
   .title-bar {
     margin-left: 260px;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                         background: #2c3e50;
                        padding: 10px 20px;
                        color: white;
                    }
                    .home-button {
                        text-decoration: none;
                        color: white;
                        background-color: #ffffff;
                        padding: 5px 15px;
                        border-radius: 5px;
                    }
.content {
            margin-left: 260px; /* Adjust based on sidebar width */
            padding: 20px;
             }

    </style>
</body>
</html>
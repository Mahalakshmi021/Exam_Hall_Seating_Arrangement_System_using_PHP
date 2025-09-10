<?php
session_start();
require_once '../config/db_connection.php';
require_once '../classes/Auth.php';

if (!isset($_SESSION['userid'])) {
    die("Unauthorized access.");
}

$auth = new Auth($conn);
$selectedSemester = isset($_GET['semester']) ? intval($_GET['semester']) : 1; // Validate input


try {
    $students = $auth->getStudentsBySemester($selectedSemester);
    $semesters = $auth->getSemesters();
} catch (Exception $e) {
    die("An error occurred: " . $e->getMessage());
}

function formatSemester($semester) {
    return ucwords(str_replace("_", " ", $semester));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student List</title>
    <script>
     function goToHome() {
            window.location.replace("dashboard.php"); // Change "home.php" to your actual home page
        }
    </script>
     <link rel="stylesheet" href="css/updatestudentstyle.css">
</head>
<body>
    <div class="sidebar">
        <?php include 'sidebar.php';?>
    </div>
    <div class="title-bar">
        <h1>Student List</h1>
        <button onclick="goToHome()">Home</button>
    </div>
    <div class="container">
        <h1>Student List</h1>
        <!-- Filter and Update Semester UI -->
        <div class="filters">
            <label for="semesterFilter">Select Semester:</label>
            <select id="semesterFilter">
                <?php foreach ($semesters as $sem): ?>
                    <option value="<?= $sem['semid'] ?>" <?= ($sem['semid'] == $selectedSemester) ? 'selected' : '' ?>>
                        <?= formatSemester($sem['semester']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button onclick="filterStudents()">Filter</button>
        </div>
        <div class="update-all">
            <label for="updateSemester">Update all students to:</label>
            <select id="updateSemester">
                <?php foreach ($semesters as $sem): ?>
                    <option value="<?= $sem['semid'] ?>"><?= formatSemester($sem['semester']) ?></option>
                <?php endforeach; ?>
            </select>
            <button onclick="updateAllSemesters()">Update All</button>
        </div>
        <!-- Table -->
        <table border="1">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Semester</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="studentList">
                <?php foreach ($students as $student): ?>
                    <tr id="row_<?= $student['student_id'] ?>">
                        <td><?= htmlspecialchars($student['name']) ?></td>
                        <td><?= htmlspecialchars($student['email_id']) ?></td>
                        <td><?= formatSemester($student['semester']) ?></td>
                        <td>
                            <button onclick="openEditModal(<?= $student['student_id'] ?>, '<?= htmlspecialchars($student['name']) ?>', '<?= htmlspecialchars($student['email_id']) ?>', <?= $student['semester'] ?>)">Edit</button> |
                            <button onclick="deleteStudent(<?= $student['student_id'] ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Edit Student Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Edit Student</h2>
            <form id="editStudentForm">
                <input type="hidden" id="editStudentId" name="student_id">
                <label for="editName">Name:</label>
                <input type="text" id="editName" name="name" required><br><br>
                <label for="editEmail">Email:</label>
                <input type="email" id="editEmail" name="email" required><br><br>
                <label for="editSemester">Semester:</label>
                <select id="editSemester" name="semester">
                    <?php foreach ($semesters as $sem): ?>
                        <option value="<?= $sem['semid'] ?>"><?= formatSemester($sem['semester']) ?></option>
                    <?php endforeach; ?>
                </select><br><br>
                <button type="button" onclick="submitEditForm()">Save Changes</button>
            </form>
        </div>
    </div>

    <script>
        // Function to open the edit modal
        function openEditModal(studentId, name, email, semester) {
            document.getElementById('editStudentId').value = studentId;
            document.getElementById('editName').value = name;
            document.getElementById('editEmail').value = email;
            document.getElementById('editSemester').value = semester;
            document.getElementById('editModal').style.display = 'block';
        }

        // Function to close the edit modal
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Function to submit the edit form
        function submitEditForm() {
            const formData = new FormData(document.getElementById('editStudentForm'));
            fetch('edit_student.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    closeEditModal();
                    location.reload(); // Refresh the page to reflect changes
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the student.');
            });
        }

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target === modal) {
                closeEditModal();
            }
        };

        // Existing functions
        function filterStudents() {
            let semester = document.getElementById("semesterFilter").value;
            window.location.href = "update_students.php?semester=" + semester;
        }

        function deleteStudent(studentId) {
            if (confirm("Are you sure you want to delete this student?")) {
                fetch("delete_student.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "student_id=" + studentId
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error("Network response was not ok");
                    }
                    return response.json();
                })
                .then(data => {
                    alert(data.message);
                    let row = document.getElementById("row_" + studentId);
                    if (row) row.remove();
                })
                .catch(error => {
                    console.error("Error:", error);
                    alert("An error occurred while deleting the student.");
                });
            }
        }

        function updateAllSemesters() {
            let oldSemester = document.getElementById("semesterFilter").value;
            let newSemester = document.getElementById("updateSemester").value;
            if (confirm("Update all students from Semester " + oldSemester + " to Semester" + newSemester + "?")) {
                fetch("update_semester.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "old_semester=" + oldSemester + "&new_semester=" + newSemester
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error("Network response was not ok");
                    }
                    return response.json();
                })
                .then(data => {
                    alert(data.message);
                    location.reload();
                })
                .catch(error => {
                    console.error("Error:", error);
                    alert("An error occurred while updating semesters.");
                });
            }
        }
    </script>
</body>
</html>
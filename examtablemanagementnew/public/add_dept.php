<?php
session_start();
require_once '../config/db_connection.php';
require_once '../classes/BlockMgntFunct.php';
require_once '../classes/DeptMgntFunctions.php';

// Ensure user is logged in
if (!isset($_SESSION['userid'])) {
    die("Unauthorized access.");
}

$userid = $_SESSION['userid'];

$blockMgntFunct = new BlockMgntFunct($conn);
$deptMgntFunctions = new DeptMgntFunctions($conn);

$blocks = $blockMgntFunct->getAllBlocks();
$departments = $deptMgntFunctions->getAllDepartments();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['department_name'], $_POST['block_id'])) {
    $department_name = trim($_POST['department_name']);
    $block_id = $_POST['block_id'];

    if (!empty($department_name) && !empty($block_id)) {
        $result = $deptMgntFunctions->insertDepartment($department_name, $block_id, $userid);
        $departments = $deptMgntFunctions->getAllDepartments();
        $message = $result === true ? "Department Added Successfully" : $result;
    } else {
        $message = "Please enter all required fields.";
    }
}

if (isset($_GET['delete_id'])) {
    $dept_id = $_GET['delete_id'];
    $result = $deptMgntFunctions->deleteDepartment($dept_id);

    if ($result === true) {
        $message = "Department deleted successfully.";
        $departments = $deptMgntFunctions->getAllDepartments();
    } else {
        $message = "Error deleting department: " . $result;
    }

    header("Location: add_dept.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Department</title>
    <script>
     function goToHome() {
            window.location.replace("dashboard.php"); // Change "home.php" to your actual home page
        }
    </script>
     <link rel="stylesheet" href="css/departmentstyle1.css">
</head>
<body>
    <div class="sidebar">
        <?php include 'sidebar.php';?>
    </div>
    <div class="title-bar">
        <h1>Add Department</h1>
        <button onclick="goToHome()">Home</button>
    </div>
    <div class="container">
        <h2>Departments List</h2>
        <!-- Top Section: Department List -->
        <div class="top-section">
            
            <?php if (isset($message)) echo "<p>$message</p>"; ?>
            <table>
                <thead>
                    <tr>
                        <th>Department Name</th>
                        <th>Block</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($departments as $dept): ?>
                        <tr>
                            <td><?= htmlspecialchars($dept['department_name']); ?></td>
                            <td><?= htmlspecialchars($dept['block_name']); ?></td>
                            <td class="actions">
                                <button onclick="openEditModal(<?= $dept['department_id']; ?>, '<?= addslashes($dept['department_name']); ?>', <?= $dept['block_id']; ?>)">Edit</button>
                                <a href="add_dept.php?delete_id=<?= $dept['department_id']; ?>" onclick="return confirm('Are you sure you want to delete this department?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Bottom Section: Add Department Form -->
        <div class="bottom-section">
            <h2>Add New Department</h2>
            <form method="post">
                <label>Department Name: </label>
                <input type="text" name="department_name" required>

                <label>Select Block</label>
                <select name="block_id" required>
                    <option value="">Select a Block</option>
                    <?php foreach ($blocks as $block): ?>
                        <option value="<?= $block['block_id']; ?>"> <?= $block['block_name']; ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">Add Department</button>
            </form>
        </div>
    </div>

    <!-- Modal for Editing Department -->
    <div id="editDeptModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit Department</h2>
            <form id="editDeptForm">
                <input type="hidden" id="editDeptId" name="department_id">
                
                <label for="editDeptName">Department Name:</label>
                <input type="text" id="editDeptName" name="department_name" required>
                
                <label for="editBlockId">Select Block:</label>
                <select id="editBlockId" name="block_id" required>
                    <?php foreach ($blocks as $block): ?>
                        <option value="<?= $block['block_id']; ?>"><?= $block['block_name']; ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">Update Department</button>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(deptId, deptName, blockId) {
            document.getElementById("editDeptId").value = deptId;
            document.getElementById("editDeptName").value = deptName;
            document.getElementById("editBlockId").value = blockId;
            document.getElementById("editDeptModal").style.display = "block";
        }

        document.querySelector(".close").addEventListener("click", () => {
            document.getElementById("editDeptModal").style.display = "none";
        });
    </script>
</body>
</html>

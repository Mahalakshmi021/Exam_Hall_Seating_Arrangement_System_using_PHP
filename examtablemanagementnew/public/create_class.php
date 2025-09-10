<?php
session_start();
require_once '../config/db_connection.php';
require_once '../classes/DeptMgntFunctions.php';
require_once '../classes/ClassManagemetnFunctions.php';

// Ensure the user is logged in
if (!isset($_SESSION['userid'])) {
    die("Unauthorized access.");
}

$userid = $_SESSION['userid'];

$deptMgntFunctions = new DeptMgntFunctions($conn);

$departments = $deptMgntFunctions->getAllDepartments();

if($_SERVER["REQUEST_METHOD"] == "POST")
{

    $class_name = $_POST["name_cls"];
   echo "classname  ".$class_name;

  $departmentid = $_POST['department'];


  $num_tables = intval($_POST['num_tables']);
   $classManagemetnFunctions = new ClassManagemetnFunctions($conn);
  $valuInserted = $classManagemetnFunctions->insertIntoClassAndCreateBench($departmentid,
    $class_name,$num_tables);

}


?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add class</title>
    <script>
     function goToHome() {
            window.location.replace("dashboard.php"); // Change "home.php" to your actual home page
        }
    </script>
     <link rel="stylesheet" href="css/classtyle.css">
</head>
<body>
    <div class="sidebar">
        <?php include 'sidebar.php';?>
    </div>
    <div class="title-bar">
        <h1>Create Class</h1>
        <button onclick="goToHome()">Home</button>
    </div>
    <div class="container">
        <h1>Create Class</h1>
<form id = "create_class" method="POST" action="create_class.php">
  <label for="department">Department:</label>
  <select id="department" name="department">

    <option value="">Select Department</option>


    <?php foreach($departments as $department): ?> 
      <option value="<?= htmlspecialchars($department['department_id']) ?>">
        <?= htmlspecialchars($department['department_name']) ?>

      </option>
     <?php endforeach;  ?>
    <!-- Add more options as needed -->
  </select>
  <br><br>
  
  <label for="classname">Class:</label>
  <input type="text" id="name_cls" name="name_cls">
  <br><br>
  
  <label for="num_tables">Number of Tables:</label>
  <input type="number" id="num_tables" name="num_tables">
  <br><br>
  
  <input type="submit" value="Submit">
</form>
</body>
</div>

</html>

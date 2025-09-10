<?php
session_start();
$userid = $_SESSION['userid'];
$user_type = $_SESSION['user_type'];


echo $userid;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashstyle.css">
</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar">
          <?php include 'sidebar.php'; ?> 
    </div>

    <!-- Top Navbar -->
    <div class="navbar">
        <h1>Exam Hall Seating Arrangement System</h1>
        <div class="profile">
            <img src="https://via.placeholder.com/40" alt="Profile">
            <span>Admin</span>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2>Welcome!</h2>
        <p>Overview of the system</p>

        <div class="dashboard-widgets">
            <div class="widget">
                <i class="fas fa-users"></i>
                <h3>120</h3>
                <p>Registered Students</p>
            </div>
            <div class="widget">
                <i class="fas fa-book"></i>
                <h3>20</h3>
                <p>Scheduled Exams</p>
            </div>
            <div class="widget">
                <i class="fas fa-chair"></i>
                <h3>50</h3>
                <p>Allocated Seats</p>
            </div>
            <div class="widget">
                <i class="fas fa-map-marker-alt"></i>
                <h3>5</h3>
                <p>Exam Locations</p>
            </div>
        <form name="form1" method="post" action="log_out.php">
      <label class="logoutLblPos">
        <input name="submit2" type="submit" id="submit2" value="log out"
        style="background-color: #2c3e50; color: #FFFFFF;">
        
      </label>
    </form>
        </div>
    </div>

</body>

</html>

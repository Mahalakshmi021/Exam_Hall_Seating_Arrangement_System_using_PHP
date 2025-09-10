<?php
$userid = $_SESSION['userid'] ?? null;
$user_type = $_SESSION['user_type'] ?? null;
?>

<div class="sidebar">
    <?php if ($user_type == 'admin'): ?>
                <a href="dashboard.php"><i class="fas fa-user-plus"></i> Dashboard</a>
        <a href="registration.php"><i class="fas fa-user-plus"></i> Registration</a>

        <a href="update_students.php"><i class="fas fa-user-graduate"></i> Students List</a>
        <a href="create_class.php"><i class="fas fa-chalkboard-teacher"></i> Create Class</a>
        <a href="exam.php"><i class="fas fa-book"></i> Exams</a>
        <a href="add_block.php"><i class="fas fa-building"></i> Add Block</a>
        <a href="add_dept.php"><i class="fas fa-university"></i> Add Department</a>
        <a href="seatingallocation.php"><i class="fas fa-chair"></i> Allocation</a>
        <a href="search_myexam_location.php"><i class="fas fa-map-marker-alt"></i> Search Location</a>
    <?php elseif ($user_type == 'user'): ?>
        <a href="search_myexam_location.php"><i class="fas fa-map-marker-alt"></i> Search My Exam Location</a>
    <?php endif; ?>
</div>

<?php
session_start();
require_once '../config/db_connection.php';
require_once '../classes/Auth.php';

$auth = new Auth($conn);
$error = "";

// Check if there is an error message stored in the session
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']); // Clear the error after displaying it
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize user input
    $seatnumber = trim($_POST['seatnumber']);
    $classcapcity = trim($_POST['classcapcity']);

    // Call the register function (Assuming Auth class has a register method)
    $registration = $auth->location($seatnumber, $classcapcity );

    if ($registration) {
        // Store registration session data
        $_SESSION['seatnumber'] = $seatnumber;
        $_SESSION['classcapcity'] = $classcapcity;
        header("Location: seatinglocation.php");
        exit();
    } else {
        $_SESSION['error'] = "Error In Locating Seat";
        header("Location: seatinglocation.php"); // Redirect to refresh the page
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Seating Locations</title>
	<link rel="stylesheet" href="locstyle.css">
</head>
<body>
	<div class="container">
		<h1>Seating Locations</h1>
		<table class="seating-locations-table">
			<tr>
				<th>Block</th>
				<th>Hall</th>
				<th>Seat</th>
				<th>Class Capacity</th>
				<th>Actions</th>
			</tr>
			<tr>
				<td>A</td>
				<td>MAIN BLOCK</td>
				<td>101</td>
				<td>50</td>
				<td>
					<a href="#">Edit</a> | <a href="#">Delete</a>
				</td>
			</tr>
			<!-- Add more seating locations here -->
		</table>
		<div class="create-seating-location-form">
			<h2>Create Seating Location</h2>
			<form>
				<select name="block">
					<option value="">Select Block</option>
					<option value="MAIN BLOCK">MAIN BLOCK</option>
					<option value="A">A</option>
					<option value="B">B</option>
				</select>
				<select name="hall">
					<option value="">Select Hall</option>
					<option value="A101">A101</option>
                    <option value="A101">A101</option>
                    <option value="A101">A101</option>
                    <option value="A101">A101</option>
                    <option value="A101">A101</option>
                    <option value="A101">A101</option>
                    <option value="A101">A101</option>
                    <option value="A101">A101</option>
                    <option value="A101">A101</option>
					<option value="A102">A102</option>
				</select>
				<input type="text" name="seat" placeholder="Seat Number">
				<input type="text" name="class_capacity" placeholder="Class Capacity">
				<input type="submit" value="Create Seating Location">
			</form>
		</div>
	</div>
</body>
</html>
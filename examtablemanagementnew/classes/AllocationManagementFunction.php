<?php
require_once '../config/db_connection.php';


class AllocationManagementFunction {

 private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }
   
/*function insertSeatingAllocation($selectdept1,$selectdept2, $exam_type,$examdept1,$examdept2,$semesterdept1, $semesterdept2,$selectclass,$selectedStudentsDept1,$selectedStudentsDept2)
{
  
        // Fetch benches from the benchtable for the selected class
        $sql = "SELECT bench_id, bench_no FROM bench WHERE class_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $selectclass);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if the number of benches matches the number of locations
        if ($result->num_rows != count($selectedStudentsDept2)) {
            $this->error = "Error: The number of benches does not match the number of even locations.";
            return false;
        }
        if ($result->num_rows != count($selectedStudentsDept1)) {
            $this->error = "Error: The number of benches does not match the number of odd locations.";
            return false;
        }

        // Insert each bench into the allocation table
        $index = 0; // Index to track locations
        while ($row = $result->fetch_assoc()) {
            $bench_id = $row['bench_id'];
            $bench_no = $row['bench_no'];
            $even_loc = trim($selectedStudentsDept2[$index]); // Get even location for this bench
            $odd_loc = trim($selectedStudentsDept1[$index]); // Get odd location for this bench

            // Prepare and bind the SQL statement for insertion
            $insert_stmt = $this->conn->prepare("INSERT INTO allocation (class_id, exam, bench_id, bench_no, even_loc, odd_loc) VALUES (?, ?, ?, ?, ?, ?)");
           $insert_stmt->bind_param("ississ", $selectclass, $exam_type, $bench_id, $bench_no, $even_loc, $odd_loc);


            // Execute the insertion
            if (!$insert_stmt->execute()) {
                $this->error = "Error inserting record for Bench ID: $bench_id - " . $insert_stmt->error;
                return false;
            }

            // Close the insertion statement
            $insert_stmt->close();

            $index++; // Move to the next location
        }

        // Close the fetch statement
        $stmt->close();
        return true;
    }
*/

 /*function insertSeatingAllocation($selectdept1,$selectdept2, $exam_type,$examdept1,$examdept2,$semesterdept1, $semesterdept2,$selectclass,$selectedStudentsDept1,$selectedStudentsDept2)
{
    print_r($selectedStudentsDept1);
    echo $selectedStudentsDept2[0];

    // Fetch benches from the bench table for the selected class
    $sql = "SELECT bench_id, bench_no FROM bench WHERE class_id = ?";
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $this->conn->error);
    }
    
    $stmt->bind_param("i", $selectclass);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the number of benches matches the number of locations
    if ($result->num_rows != count($selectedStudentsDept2)) {
        $this->error = "Error: The number of benches does not match the number of even locations.";
        return false;
    }
    if ($result->num_rows != count($selectedStudentsDept1)) {
        $this->error = "Error: The number of benches does not match the number of odd locations.";
        return false;
    }

    // Insert each bench into the allocation table
    $index = 0; 
    while ($row = $result->fetch_assoc()) {
        $bench_id = $row['bench_id'];
        $bench_no = $row['bench_no'];
        $even_loc = trim($selectedStudentsDept2[$index]); 
        $odd_loc = trim($selectedStudentsDept1[$index]); 

        // Prepare and bind the SQL statement for insertion
        $insert_stmt = $this->conn->prepare("INSERT INTO allocation (class_id, exam, bench_id, bench_no, even_loc, odd_loc) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$insert_stmt) {
            die("Insert Prepare failed: " . $this->conn->error);
        }
        
        $insert_stmt->bind_param("ississ", $selectclass, $exam_type, $bench_id, $bench_no, $even_loc, $odd_loc);

        // Execute the insertion
        if (!$insert_stmt->execute()) {
            die("Execute failed: " . $insert_stmt->error);
        }

        // Close the insertion statement
        $insert_stmt->close();

        $index++;
    }

    // Close the fetch statement
    $stmt->close();

    return "success";
}*/


function insertSeatingAllocation($selectdept1, $selectdept2, $exam_type, $examdept1, $examdept2, $semesterdept1, $semesterdept2, $selectclass, $selectedStudentsDept1, $selectedStudentsDept2)
{
    // Fetch benches from the bench table for the selected class
    $sql = "SELECT bench_id, bench_no FROM bench WHERE class_id = ?";
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $this->conn->error);
    }
    
    $stmt->bind_param("i", $selectclass);
    $stmt->execute();
    $result = $stmt->get_result();

    $benches = [];
    while ($row = $result->fetch_assoc()) {
        $benches[] = $row;
    }

    $totalBenches = count($benches);
    $totalStudentsDept1 = count($selectedStudentsDept1);
    $totalStudentsDept2 = count($selectedStudentsDept2);

    // If no benches available, return an error
    if ($totalBenches == 0) {
        return "Error: No benches available for seating.";
    }

    // Merge student lists alternatively to balance them
    $students = [];
    $maxCount = max($totalStudentsDept1, $totalStudentsDept2);

    for ($i = 0; $i < $maxCount; $i++) {
        if (isset($selectedStudentsDept1[$i])) {
            $students[] = $selectedStudentsDept1[$i]; // Odd-position student
        }
        if (isset($selectedStudentsDept2[$i])) {
            $students[] = $selectedStudentsDept2[$i]; // Even-position student
        }
    }

    $totalStudents = count($students);
    $index = 0;

    // Allocate students to benches
    foreach ($benches as $bench) {
        $bench_id = $bench['bench_id'];
        $bench_no = $bench['bench_no'];

        // Assign students alternately, leaving empty spots if necessary
        $even_loc = isset($students[$index]) ? trim($students[$index]) : NULL;
        $index++;
        $odd_loc = isset($students[$index]) ? trim($students[$index]) : NULL;
        $index++;


        /*if (isset($students[$index])) {
            $value = trim($students[$index]);
        } else {
            $value = NULL;
        }
        */
        // Prepare the SQL statement
        $insert_stmt = $this->conn->prepare("INSERT INTO allocation (class_id, exam_type, bench_id, bench_no, even_loc, odd_loc, examdept1, examdept2) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$insert_stmt) {
            die("Insert Prepare failed: " . $this->conn->error);
        }

        $insert_stmt->bind_param("ississii", $selectclass, $exam_type, $bench_id, $bench_no, $even_loc, $odd_loc, $examdept1, $examdept2);

        // Execute the insertion
        if (!$insert_stmt->execute()) {
            die("Execute failed: " . $insert_stmt->error);
        }

        // Close the insertion statement
        $insert_stmt->close();

        // Stop if all students are assigned
        if ($index >= $totalStudents) {
            break;
        }
    }

    // Close the fetch statement
    $stmt->close();

    return "success";
}

}




?>
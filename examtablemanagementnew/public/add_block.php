<?php
session_start();

require_once '../config/db_connection.php';
require_once '../classes/BlockMgntFunct.php';

// Ensure the user is logged in
if (!isset($_SESSION['userid'])) {
    die("Unauthorized access.");
}

$userid = $_SESSION['userid'];

// Pass database connection to the class
$blockMgntFunct = new BlockMgntFunct($conn);

// Fetch all blocks
$blocks = $blockMgntFunct->getAllBlocks();

// Handle form submission for adding a block
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['block_name'])) {
        $block_name = trim($_POST['block_name']);

        if (!empty($block_name)) {
            $result = $blockMgntFunct->insertBlock($block_name, $userid);
            if ($result === true) {
                $message = "Block Added Successfully";
                // Refresh the block list after adding a new block
                $blocks = $blockMgntFunct->getAllBlocks();
            } else {
                $message = $result;
            }
        } else {
            $message = "Please enter a block name.";
        }
    }
}

// Handle delete action
if (isset($_GET['delete_id'])) {
    $block_id = $_GET['delete_id'];

    // Delete the block
    $result = $blockMgntFunct->deleteBlock($block_id);

    if ($result === true) {
        $message = "Block deleted successfully.";
        // Refresh the block list after deletion
        $blocks = $blockMgntFunct->getAllBlocks();
    } else {
        $message = "Error deleting block: " . $result;
    }

    // Redirect to avoid form resubmission
    header("Location: add_block.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Block</title>
    <script>
     function goToHome() {
            window.location.replace("dashboard.php"); // Change "home.php" to your actual home page
        }
    </script>
     <link rel="stylesheet" href="css/blockstyle.css">
</head>
<body>
     <div class="sidebar">
        <?php include 'sidebar.php';?>
    </div>
    <div class="title-bar">
        <h1>Add Block</h1>
        <button onclick="goToHome()">Home</button>
    </div>
    <div class="container">
        <h2>Blocks List</h2>
        <!-- Top Section: Display Blocks -->
        <div class="top-section">
            <?php if (isset($message)) echo "<p>$message</p>"; ?>
            <table>
                <thead>
                    <tr>
                        <th>Block Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($blocks as $block): ?>
                        <tr>
                            <td><?= htmlspecialchars($block['block_name']); ?></td>
                            <td class="actions">
                                <button onclick="openEditModal(<?= $block['block_id']; ?>, '<?= addslashes($block['block_name']); ?>')">Edit</button>
                                <a href="add_block.php?delete_id=<?= $block['block_id']; ?>" onclick="return confirm('Are you sure you want to delete this block?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Bottom Section: Add Block Form -->
        <div class="bottom-section">
            <h2>Add New Block</h2>
            <form method="post">
                <label>Block Name: </label>
                <input type="text" name="block_name" required>
                <button type="submit">Add Block</button>
            </form>
        </div>
    

    <!-- Modal Dialog for Editing Block -->
    <div id="editBlockModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit Block</h2>
            <form id="editBlockForm">
                <input type = "hidden" id="editBlockId" name="block_id">
                <label for="editBlockName">Block Name:</label>
                <input type="text" id="editBlockName" name="block_name" required>
                <button type="submit">Update Block</button>
            </form>
        </div>
    </div>
    </div>

    <script>
        // Get the modal and close button
        const modal = document.getElementById("editBlockModal");
        const closeBtn = document.querySelector(".close");

        // Function to open the modal and populate the form
        function openEditModal(blockId, blockName) {
            document.getElementById("editBlockId").value = blockId;
            document.getElementById("editBlockName").value = blockName;
            modal.style.display = "block";
        }

        // Function to close the modal
        function closeModal() {
            modal.style.display = "none";
        }

        // Event listener for the close button
        closeBtn.addEventListener("click", closeModal);

        // Event listener for clicks outside the modal
        window.addEventListener("click", (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });

        // Handle form submission via AJAX 
        document.getElementById("editBlockForm").addEventListener("submit", function (e) {
            e.preventDefault(); // Prevent the form from submitting normally

            const blockId = document.getElementById("editBlockId").value;
            const blockName = document.getElementById("editBlockName").value;

            // Send an AJAX request to update the block
            fetch("update_block.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ block_id: blockId, block_name: blockName }),

            
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        alert("Block updated successfully!");
                        closeModal();
                        location.reload(); // Refresh the page to reflect changes
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .catch((error) => {
                    console.error("Error:", error);
                });
        });
    </script>
</body>
</html>
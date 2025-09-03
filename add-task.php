<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'db_connect.php';

if (!isset($_SESSION['UserID'])) {
    die("Session error: Please log in to add tasks.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_name = $_POST['task_name'] ?? null;
    $description = $_POST['description'] ?? null;
    $due_date = $_POST['due_date'] ?? null;
    $user_id = $_SESSION['UserID'];

    if (!$task_name || !$due_date) {
        die("Error: Task Name and Due Date are required.");
    }

    $sql = "INSERT INTO Chore (ChoreID, UID, Description, CName, DueDate, ChoreStatus)
            VALUES (UUID(), ?, ?, ?, ?, 'In Progress')";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ssss", $user_id, $description, $task_name, $due_date);

    if ($stmt->execute()) {
        header("Location: index.php"); // go back to homepage after adding
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>

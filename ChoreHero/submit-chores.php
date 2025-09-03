<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['UserID'])) {
    http_response_code(403);
    echo "Not logged in.";
    exit();
}

$today = date('Y-m-d');
$user_id = $_SESSION['UserID'];
$points_per_chore = 1; //One point per chore

if (!isset($_POST['chores'])) {
    http_response_code(400);
    echo "No chores selected.";
    exit();
}

$completed_count = 0;

foreach ($_POST['chores'] as $chore_id) {
    $sql = "UPDATE Chore SET ChoreStatus = 'Complete', DateCompleted = ? WHERE ChoreID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $today, $chore_id);
    if ($stmt->execute()) {
        $completed_count++;
    }
    $stmt->close();
}

//Increment user score
if ($completed_count > 0) {
    $points_earned = $completed_count * $points_per_chore;
    $score_sql = "UPDATE Household SET Score = Score + ? WHERE UserID = ?";
    $score_stmt = $conn->prepare($score_sql);
    $score_stmt->bind_param("is", $points_earned, $user_id);
    $score_stmt->execute();
    $score_stmt->close();
}

$conn->close();
http_response_code(200);
echo "Updated";
?>

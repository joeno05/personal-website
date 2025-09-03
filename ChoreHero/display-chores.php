<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['UserID'])) {
    echo "<p>You must log in to see your chores.</p>";
    exit();
}

$user_id = $_SESSION['UserID'];
$sql = "SELECT * FROM Chore WHERE UID = ? AND ChoreStatus != 'Complete'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<h2>Your Chores</h2>";
    echo "<div id='success-message'></div>";
    echo "<form id='chore-form' method='POST'>";
    while ($row = $result->fetch_assoc()) {
        $formattedDate = (new DateTime($row['DueDate']))->format('n/j/Y');
        echo "<div class='chore-item' id='chore-" . $row['ChoreID'] . "'>";
        echo "<label>";
        echo "<input type='checkbox' name='chores[]' value='" . $row['ChoreID'] . "'>";
        echo "<div class='chore-card'>";
        echo "<div class='chore-title'><strong>" . htmlspecialchars($row['CName']) . "</strong></div>";
        echo "<div class='chore-date'>Due: $formattedDate</div>";
        if (!empty($row['Description'])) {
            echo "<div class='chore-desc'><em>" . htmlspecialchars($row['Description']) . "</em></div>";
        }
        echo "</div>";
        echo "</label>";
        echo "</div>";
    }
    echo "<input type='submit' value='Submit'>";
    echo "</form>";
} else {
    echo "<p>No chores assigned yet.</p>";
}
$stmt->close();
?>

<script>
document.getElementById('chore-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = e.target;
    const checkedBoxes = form.querySelectorAll('input[type="checkbox"]:checked');
    const formData = new FormData(form);
    const response = await fetch('submit-chores.php', {
        method: 'POST',
        body: formData
    });

    if (response.ok) {
        //Fade out completed chores
        checkedBoxes.forEach(box => {
            const choreDiv = box.closest('.chore-item');
            choreDiv.style.transition = 'opacity 0.4s';
            choreDiv.style.opacity = 0;
            setTimeout(() => choreDiv.remove(), 400);
        });

        //Show success message
        const count = checkedBoxes.length;
        const message = count === 1 ? 'Task completed!' : `${count} tasks completed!`;
        const msgEl = document.getElementById('success-message');
        msgEl.textContent = message;
        msgEl.style.display = 'block';

        //Update the score on the page
        const scoreBox = document.getElementById('score-box');
        if (scoreBox) {
            const current = parseInt(scoreBox.textContent.replace(/\D/g, '')) || 0;
            const updated = current + count;
            scoreBox.textContent = 'Score: ' + updated;
        }

    } else {
        alert('Something went wrong while submitting chores.');
    }
});
</script>

<style>
    .chore-item {
        margin-bottom: 15px;
    }

    .chore-card {
        background: #f9f9f9;
        border-radius: 10px;
        padding: 12px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.1);
        margin-left: 10px;
        display: inline-block;
        width: calc(100% - 30px);
        vertical-align: middle;
    }

    .chore-title {
        font-size: 1.1rem;
        color: #333;
        margin-bottom: 4px;
    }

    .chore-date {
        color: #888;
        font-size: 0.9rem;
    }

    .chore-desc {
        margin-top: 5px;
        color: #444;
        font-size: 0.9rem;
    }

    #success-message {
        display: none;
        color: green;
        margin-bottom: 20px;
        font-weight: bold;
        text-align: center;
    }

    input[type="checkbox"] {
        transform: scale(1.3);
        margin-right: 10px;
        vertical-align: middle;
    }
</style>

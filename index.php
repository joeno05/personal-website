<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<title>ChoreHero</title>

<style>
    body {
        background-color: lightblue;
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
    }

    .container {
        max-width: 500px;
        margin: 40px auto;
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        position: relative;
    }

    .score-box {
        position: absolute;
        top: 20px;
        right: 20px;
        background-color: #007bff;
        color: white;
        padding: 8px 12px;
        border-radius: 10px;
        font-size: 0.95rem;
        font-weight: bold;
    }

    .user-list-box {
        position: absolute;
        top: 20px;
        left: 20px;
        background-color: #f0f8ff;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 0.85rem;
        box-shadow: 0 1px 4px rgba(0,0,0,0.1);
        width: 75px;
    }

    .user-list-box ul {
        list-style: none;
        padding-left: 0;
        margin: 8px 0 0;
    }

    .user-list-box li {
        padding: 2px 0;
        color: #333;
    }

    h1, h2 {
        text-align: center;
        color: #333;
    }

    form.login-bar {
        display: flex;
        flex-direction: column;
        gap: 15px;
        margin-bottom: 20px;
    }

    form.login-bar input[type="text"],
    form.login-bar input[type="password"],
    form.login-bar input[type="date"] {
        padding: 10px;
        font-size: 1rem;
        border: 1px solid #ccc;
        border-radius: 8px;
    }

    form.login-bar input[type="submit"] {
        padding: 10px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 1rem;
    }

    form.login-bar input[type="submit"]:hover {
        background-color: #0056b3;
    }

    .chore-list {
        margin-top: 20px;
    }

    .chore-item {
        padding: 10px 0;
        border-bottom: 1px solid #eee;
        margin-bottom: 10px;
    }

    #success-message {
        display: none;
        color: green;
        margin-bottom: 20px;
        font-weight: bold;
        text-align: center;
    }

    form#chore-form {
        margin-top: 20px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    form#chore-form input[type="submit"] {
        padding: 12px;
        background-color: #66b3ff;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 1rem;
        width: 100%;
        margin-top: 10px;
    }

    form#chore-form input[type="submit"]:hover {
        background-color: #3399ff;
    }

    button {
        padding: 10px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 1rem;
        margin-top: 10px;
        width: 100%;
    }

    button:hover {
        background-color: #0056b3;
    }

    .logout-btn {
        background-color: #ff4d4d;
        margin-top: 20px;
    }

    .logout-btn:hover {
        background-color: #cc0000;
    }

    .error {
        color: red;
        text-align: center;
        margin-bottom: 10px;
    }
</style>
</head>

<body>
  <div class="container">
    <h1>ChoreHero</h1>

    <?php
    session_start();

    //Login/registration handling
    if (!isset($_SESSION['UserID']) && $_SERVER["REQUEST_METHOD"] === "POST") {
        include 'db_connect.php';

        if (isset($_POST['login'])) {
            $username = $_POST['username'];
            $password = $_POST['password'];

            $sql = "SELECT UserID, Password FROM Household WHERE Email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($userID, $hashedPassword);
                $stmt->fetch();
                if (password_verify($password, $hashedPassword)) {
                    $_SESSION['UserID'] = $userID;
                    header("Location: index.php");
                    exit();
                } else {
                    $error = "Incorrect password.";
                }
            } else {
                $error = "No user found with that email.";
            }
            $stmt->close();

        } elseif (isset($_POST['register'])) {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $userID = uniqid();

            $check_sql = "SELECT * FROM Household WHERE Email = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) {
                $error = "An account with that email already exists.";
            } else {
                $insert_sql = "INSERT INTO Household (UserID, Email, Name, Password, Score) VALUES (?, ?, ?, ?, 0)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("ssss", $userID, $email, $name, $password);
                if ($insert_stmt->execute()) {
                    $_SESSION['UserID'] = $userID;
                    header("Location: index.php");
                    exit();
                } else {
                    $error = "Failed to register user.";
                }
                $insert_stmt->close();
            }
            $check_stmt->close();
        }
    }

    if (isset($_SESSION['UserID'])) {
        //Logged-in view
        $user_id = $_SESSION['UserID'];
        include 'db_connect.php';

        //Fetch score
        $score_sql = "SELECT Score FROM Household WHERE UserID = ?";
        $score_stmt = $conn->prepare($score_sql);
        $score_stmt->bind_param("s", $user_id);
        $score_stmt->execute();
        $score_stmt->bind_result($score);
        $score_stmt->fetch();
        $score_stmt->close();

        //Fetch all household members
        $users_sql = "SELECT Name FROM Household ORDER BY Name ASC";
        $users_result = $conn->query($users_sql);
        $household_members = [];
        while ($row = $users_result->fetch_assoc()) {
            $household_members[] = $row['Name'];
        }

        $conn->close();

        echo "<div class='score-box' id='score-box'>Score: $score</div>";

        echo "<div class='user-list-box'><strong>Household:</strong><ul>";
        foreach ($household_members as $name) {
            echo "<li>" . htmlspecialchars($name) . "</li>";
        }
        echo "</ul></div>";

        echo "<h2>Welcome</h2>";
        echo "<div class='chore-list'>";
        include 'display-chores.php';
        echo "</div>";
        echo "<button onclick=\"document.getElementById('new-task-form').style.display='block'\">New Task</button>";
        echo "<div id='new-task-form' style='display: none; margin-top: 20px;'>
                <h2>Add New Task</h2>
                <form action='add-task.php' method='POST' class='login-bar'>
                    <input type='text' name='task_name' placeholder='Task Name' required>
                    <input type='text' name='description' placeholder='Description (Optional)'>
                    <input type='date' name='due_date' required>
                    <input type='submit' value='Add Task'>
                </form>
              </div>";
        echo "<form action='logout.php' method='POST' style='text-align: center;'>
                <button type='submit' class='logout-btn'>Logout</button>
              </form>";

    } else {
        //Login and registration forms
        echo "<h2>Login</h2>";
        if (isset($error)) echo "<p class='error'>$error</p>";
        echo "<form class='login-bar' method='POST'>
                <input type='text' name='username' placeholder='Email' required>
                <input type='password' name='password' placeholder='Password' required>
                <input type='submit' name='login' value='Log In'>
              </form>";

        echo "<h2>Create Account</h2>";
        echo "<form class='login-bar' method='POST'>
                <input type='text' name='name' placeholder='Full Name' required>
                <input type='text' name='email' placeholder='Email' required>
                <input type='password' name='password' placeholder='Password' required>
                <input type='submit' name='register' value='Register'>
              </form>";
    }
    ?>
  </div>
</body>

</html>

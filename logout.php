<?php
session_start();
session_destroy(); //Destroy the session and clear all stored data
header("Location: index.php"); //Redirect back to the login/home page
exit(); //Make sure no further code is executed
?>
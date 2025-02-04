<?php
session_start();

$valid_username = 'harp';
$valid_password = 'harp_24';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
error_log("Username: " . $_POST['username']);
error_log("Password: " . $_POST['password']);

    if ($username === $valid_username && $password === $valid_password) {
        $_SESSION['authenticated'] = true;
        echo 'success';
    } else {
        echo 'failure';
    }
}
?>

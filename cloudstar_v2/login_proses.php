<?php
session_start();
include 'koneksi.php';

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
    if (mysqli_num_rows($query) === 1) {
        $row = mysqli_fetch_assoc($query);
        
        if (password_verify($password, $row['password'])) {
            $_SESSION['login'] = true;
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['email'] = $row['email'];

            // Alur setelah login berhasil
            if (empty($row['username'])) {
                header("Location: lengkapi_profile.php");
            } else if ($row['is_training_completed'] == 0) {
                header("Location: training.php");
            } else {
                header("Location: game.php");
            }
            exit;
        } else {
            echo "<script>alert('Password salah!'); window.location='index.html';</script>";
        }
    } else {
        echo "<script>alert('Email tidak ditemukan!'); window.location='index.html';</script>";
    }
    exit;
}
?>
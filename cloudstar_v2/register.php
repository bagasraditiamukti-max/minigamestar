<?php
include 'koneksi.php';

if (isset($_POST['register'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $password_hashed = password_hash($password, PASSWORD_DEFAULT);

    $cek_email = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
    if (mysqli_num_rows($cek_email) > 0) {
        echo "<script>alert('Email sudah terdaftar!');</script>";
    } else {
        $query = "INSERT INTO users (email, password) VALUES ('$email', '$password_hashed')";
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Registrasi berhasil! Silahkan login.'); window.location='index.html';</script>";
            exit;
        } else {
            echo "<script>alert('Gagal mendaftar, coba lagi.');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>CloudStar Game - Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="box">
        <h2>CloudStar Game ☁️⭐️</h2>
        <p>Buat Akun Baru</p>
        <form action="" method="POST">
            <input type="email" name="email" placeholder="Masukkan Email" required><br>
            <input type="password" name="password" placeholder="Masukkan Password" required><br>
            <button type="submit" name="register" class="btn-reg">Daftar</button>
        </form>
        <p><a href="index.html" class="link-blue">Sudah punya akun? Login di sini</a></p>
    </div>
</body>
</html>
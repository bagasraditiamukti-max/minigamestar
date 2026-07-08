<?php
session_start();
include 'koneksi.php';

// Proteksi Sesi: Jika belum login, kembalikan ke index.html
if (!isset($_SESSION['login'])) { 
    header("Location: index.html"); 
    exit; 
}

$user_id = $_SESSION['user_id'];

// Logika Backend: Memproses data saat tombol ditekan
if (isset($_POST['simpan_profile'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    
    // Logika default nama foto
    $nama_foto = "default.png";
    
    // Periksa apakah user mengunggah foto profil
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $ekstensi = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        // Membuat nama file unik agar tidak saling tertimpa di server
        $nama_foto = "user_" . $user_id . "_" . time() . "." . $ekstensi;
        $target = "uploads/" . $nama_foto;
        
        // Pindahkan file dari folder sementara server ke folder uploads kamu
        move_uploaded_file($_FILES['foto']['tmp_name'], $target);
    }

    // Update data nama, foto, dan set default pengaturan lainnya ke database
    $query = "UPDATE users SET username = '$username', foto_profil = '$nama_foto' WHERE id = '$user_id'";
    
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Profil berhasil diperbarui! Selamat datang di Dashboard.'); window.location='home.php';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal menyimpan profil, coba lagi.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>CloudStar Game - Atur Profil</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="box">
        <h2>Lengkapi Profil 👤</h2>
        <p>Silahkan isi nama dan unggah foto profil Anda sebelum bermain.</p>
        
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="text" name="username" placeholder="Masukkan Nama/Username Anda" required><br>
            
            <label style="font-size:12px; color:#cbd5e1; display:block; margin: 15px 0 5px 0; text-align:left;">Pilih Foto Profil:</label>
            <input type="file" name="foto" accept="image/*" required><br><br>
            
            <button type="submit" name="simpan_profile" class="btn-profile" style="background:#2ecc71; color:white; width:96%;">Simpan & Lanjut</button>
        </form>
    </div>
</body>
</html>
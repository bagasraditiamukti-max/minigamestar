<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: index.html");
    exit;
}

$user_id = $_SESSION['user_id'];
$pesan = "";

// Proses penyimpanan form saat tombol SIMPAN diklik
if (isset($_POST['simpan_pengaturan'])) {
    $tema = $_POST['tema'];
    $musik = intval($_POST['musik']);
    $bahasa = $_POST['bahasa'];

    $update = mysqli_query($conn, "UPDATE users SET tema = '$tema', musik = '$musik', bahasa = '$bahasa' WHERE id = '$user_id'");
    if ($update) {
        $pesan = "Pengaturan berhasil diperbarui!";
    } else {
        $pesan = "Gagal memperbarui pengaturan.";
    }
}

// Ambil data terbaru untuk memuat kondisi awal form
$query = mysqli_query($conn, "SELECT tema, musik, bahasa FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($query);

$lang = isset($user['bahasa']) ? $user['bahasa'] : 'indo';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>CloudStar Game - Pengaturan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="<?= ($user['tema'] == 'terang') ? 'tema-terang' : ''; ?>">

    <div class="box" style="max-width: 450px;">
        <h2>⚙️ Konfigurasi Game</h2>
        
        <?php if(!empty($pesan)): ?>
            <p style="color:#22c55e; font-weight:bold; margin-bottom:15px;"><?= $pesan; ?></p>
        <?php endif; ?>

        <form action="pengaturan.php" method="POST">
            
            <div class="form-group" style="text-align:left; margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px; font-weight:bold;">🌐 Pilih Bahasa (Language):</label>
                <select name="bahasa" style="width:100%; padding:8px; border-radius:5px;">
                    <option value="indo" <?= ($user['bahasa'] == 'indo') ? 'selected' : ''; ?>>Bahasa Indonesia</option>
                    <option value="inggris" <?= ($user['bahasa'] == 'inggris') ? 'selected' : ''; ?>>English</option>
                </select>
            </div>

            <div class="form-group" style="text-align:left; margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px; font-weight:bold;">🎨 Mode Tampilan (Theme):</label>
                <select name="tema" style="width:100%; padding:8px; border-radius:5px;">
                    <option value="gelap" <?= ($user['tema'] == 'gelap') ? 'selected' : ''; ?>>Tema Gelap (Dark Mode)</option>
                    <option value="terang" <?= ($user['tema'] == 'terang') ? 'selected' : ''; ?>>Tema Terang (Light Mode)</option>
                </select>
            </div>

            <div class="form-group" style="text-align:left; margin-bottom:20px;">
                <label style="display:block; margin-bottom:5px; font-weight:bold;">🎵 Musik Latar Game:</label>
                <select name="musik" style="width:100%; padding:8px; border-radius:5px;">
                    <option value="1" <?= ($user['musik'] == 1) ? 'selected' : ''; ?>>Nyalakan Musik (ON)</option>
                    <option value="0" <?= ($user['musik'] == 0) ? 'selected' : ''; ?>>Matikan Musik (OFF)</option>
                </select>
            </div>

            <button type="submit" name="simpan_pengaturan" class="btn-menu bg-green" style="width:100%; margin-bottom:10px;">💾 Simpan Perubahan</button>
            <button type="button" onclick="window.location='home.php'" class="btn-menu bg-orange" style="width:100%;">🏠 Kembali ke Menu Utama</button>
        </form>
    </div>

    <?php if ($user['musik'] == 1): ?>
        <audio id="bgMusic" loop autoplay>
            <source src="song.mp3" type="audio/mpeg">
        </audio>
        <script>
            document.addEventListener('click', function() {
                var audio = document.getElementById('bgMusic');
                if(audio.paused) { audio.play(); }
            }, { once: true });
        </script>
    <?php endif; ?>

</body>
</html>
<?php
session_start();
include 'koneksi.php';

// Proteksi Sesi: Jika belum login, kembalikan ke index.html
if (!isset($_SESSION['login'])) {
    header("Location: index.html");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data profil user lengkap
$query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($query);

// Logika Kamus Bahasa Multi-language
$lang = isset($user['bahasa']) ? $user['bahasa'] : 'indo';
$text = [
    'indo' => [
        'sambut' => 'Hai',
        'lvl' => 'Level Kamu',
        'btn_profil' => '👤 1. Lengkapi/Ubah Profil',
        'btn_setting' => '⚙️ 2. Pengaturan Game',
        'btn_latihan' => '🚀 3. Mulai (Sesi Latihan)',
        'btn_main' => '🎮 3. Mulai Game Utama',
        'keluar' => '🚪 Keluar Akun'
    ],
    'inggris' => [
        'sambut' => 'Hello',
        'lvl' => 'Your Level',
        'btn_profil' => '👤 1. Complete/Edit Profile',
        'btn_setting' => '⚙️ 2. Game Settings',
        'btn_latihan' => '🚀 3. Start (Training Session)',
        'btn_main' => '🎮 3. Start Main Game',
        'keluar' => '🚪 Sign Out'
    ]
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>CloudStar Game - Menu Utama</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="<?= (isset($user['tema']) && $user['tema'] == 'terang') ? 'tema-terang' : ''; ?>">

    <div class="box">
        <div class="profile-img-container">
            <?php 
            $foto = (!empty($user['foto_profil'])) ? $user['foto_profil'] : 'default.jpg';
            ?>
            <img src="uploads/<?= $foto; ?>" alt="Foto Profil">
        </div>
        
        <h3><?= $text[$lang]['sambut']; ?>, <?= htmlspecialchars($user['username'] ?? 'User'); ?>!</h3>
        <p style="margin-bottom:20px;"><?= $text[$lang]['lvl']; ?>: <span style="color:#fdbb2d; font-weight:bold;"><?= $user['current_level'] ?? 1; ?></span></p>

        <div class="menu-container">
            <button onclick="window.location='lengkapi_profile.php'" class="btn-menu bg-orange"><?= $text[$lang]['btn_profil']; ?></button>
            <button onclick="window.location='pengaturan.php'" class="btn-menu bg-purple"><?= $text[$lang]['btn_setting']; ?></button>
            
            <?php if (isset($user['is_training_completed']) && $user['is_training_completed'] == 0): ?>
                <button onclick="window.location='training.php'" class="btn-menu bg-green"><?= $text[$lang]['btn_latihan']; ?></button>
            <?php else: ?>
                <button onclick="window.location='game.php'" class="btn-menu bg-green"><?= $text[$lang]['btn_main']; ?></button>
            <?php endif; ?>
        </div>
        
        <p style="margin-top:20px; font-size:12px;"><a href="logout.php" style="color:#f87171; text-decoration: none; font-weight: bold;"><?= $text[$lang]['keluar']; ?></a></p>
    </div>

    <?php if (isset($user['musik']) && $user['musik'] == 1): ?>
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
<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: index.html");
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['latihan_selesai'])) {
    $query = "UPDATE users SET is_training_completed = 1 WHERE id = '$user_id'";
    if (mysqli_query($conn, $query)) {
        echo "sukses";
    } else {
        echo "gagal";
    }
    exit; 
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>CloudStar Game - Training Session</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="<?= (isset($user['tema']) && $user['tema'] == 'terang') ? 'tema-terang' : ''; ?>">

    <div class="header-panel">
        <h2>CloudStar Game ☁️⭐️</h2>
        <p id="status-text">Sesi Latihan: Track 1 (Garis Lurus) - Tuntun Bintang melewati jalur putih!</p>
    </div>

    <canvas id="gameCanvas" width="800" height="500"></canvas>

    <script>
        const canvas = document.getElementById('gameCanvas');
        const ctx = canvas.getContext('2d');
        const statusText = document.getElementById('status-text');

        let currentTrack = 1; 
        let gameActive = false; 
        let player = { x: 0, y: 0, radius: 8 };

        let startZone = { x: 50, y: 250, radius: 25 };
        let finishZone = { x: 750, y: 250, radius: 25 };

        function drawTrack() {
            ctx.fillStyle = '#1e293b'; 
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            // Dekorasi Awan Estetik
            ctx.fillStyle = 'rgba(255, 255, 255, 0.05)';
            ctx.beginPath();
            ctx.arc(200, 100, 50, 0, Math.PI * 2); ctx.arc(250, 90, 60, 0, Math.PI * 2); ctx.arc(300, 100, 50, 0, Math.PI * 2);
            ctx.arc(600, 380, 70, 0, Math.PI * 2);
            ctx.fill();

            ctx.strokeStyle = '#f8fafc'; 
            ctx.lineCap = 'round'; ctx.lineJoin = 'round';

            if (currentTrack === 1) {
                ctx.lineWidth = 50;
                ctx.beginPath(); ctx.moveTo(50, 250); ctx.lineTo(750, 250); ctx.stroke();
                startZone = { x: 50, y: 250, radius: 25 }; finishZone = { x: 750, y: 250, radius: 25 };
            } else if (currentTrack === 2) {
                ctx.lineWidth = 40;
                ctx.beginPath(); ctx.moveTo(50, 100); ctx.lineTo(400, 100); ctx.lineTo(400, 400); ctx.lineTo(750, 400); ctx.stroke();
                startZone = { x: 50, y: 100, radius: 20 }; finishZone = { x: 750, y: 400, radius: 20 };
            } else if (currentTrack === 3) {
                ctx.lineWidth = 35;
                ctx.beginPath(); ctx.moveTo(50, 250); ctx.bezierCurveTo(250, 20, 550, 480, 750, 250); ctx.stroke();
                startZone = { x: 50, y: 250, radius: 18 }; finishZone = { x: 750, y: 250, radius: 18 };
            }

            ctx.fillStyle = '#22c55e';
            ctx.beginPath(); ctx.arc(startZone.x, startZone.y, startZone.radius, 0, Math.PI*2); ctx.fill();
            ctx.fillStyle = '#eab308';
            ctx.beginPath(); ctx.arc(finishZone.x, finishZone.y, finishZone.radius, 0, Math.PI*2); ctx.fill();

            ctx.fillStyle = '#ffffff'; ctx.font = 'bold 12px sans-serif';
            ctx.fillText("START", startZone.x - 20, startZone.y + 4); ctx.fillText("GOAL", finishZone.x - 17, finishZone.y + 4);
        }

        function checkCollision() {
            let pixel = ctx.getImageData(player.x, player.y, 1, 1).data;
            let r = pixel[0], g = pixel[1], b = pixel[2];
            if (gameActive && r < 40 && g < 50 && b < 70) {
                gameActive = false;
                alert("Ups! Bola menabrak awan. Kembali ke titik START!");
            }
        }

        function drawPlayer() {
            ctx.fillStyle = '#fdbb2d';
            ctx.shadowBlur = 10; ctx.shadowColor = '#fdbb2d';
            ctx.beginPath(); ctx.arc(player.x, player.y, player.radius, 0, Math.PI * 2); ctx.fill();
            ctx.shadowBlur = 0;
        }

        function gameLoop() {
            drawTrack();
            if (gameActive) {
                checkCollision();
                let distToFinish = Math.hypot(player.x - finishZone.x, player.y - finishZone.y);
                if (distToFinish < finishZone.radius) {
                    gameActive = false;
                    nextLevel();
                }
            }
            drawPlayer();
            requestAnimationFrame(gameLoop);
        }

        function nextLevel() {
            if (currentTrack < 3) {
                currentTrack++;
                if(currentTrack === 2) statusText.innerText = "Sesi Latihan: Track 2 (Belokan Tajam) - Awas tikungan siku-siku!";
                if(currentTrack === 3) statusText.innerText = "Sesi Latihan: Track 3 (Tikungan Melengkung) - Jaga presisi di jalur melingkar!";
                alert("Hebat! Jalur berhasil dilewati. Bersiap untuk Track selanjutnya!");
            } else {
                alert("Selamat! Sesi latihan selesai. Sekarang kamu akan dialihkan ke Game Utama (100 Level)!");
                
                let formData = new FormData();
                formData.append('latihan_selesai', '1');

                fetch('training.php', { method: 'POST', body: formData })
                .then(response => response.text())
                .then(data => {
                    if(data.trim() === "sukses") {
                        window.location = 'game.php';
                    } else {
                        alert("Terjadi kesalahan sistem saat menyimpan progres.");
                    }
                });
            }
        }

        canvas.addEventListener('mousemove', (e) => {
            const rect = canvas.getBoundingClientRect();
            player.x = e.clientX - rect.left;
            player.y = e.clientY - rect.top;

            if (!gameActive) {
                let distToStart = Math.hypot(player.x - startZone.x, player.y - startZone.y);
                if (distToStart < startZone.radius) { gameActive = true; }
            }
        });

        gameLoop();
    </script>

    <?php if (isset($user['musik']) && $user['musik'] == 1): ?>
    <audio id="bgMusic" loop autoplay>
        <source src="song.mp3" type="audio/mpeg">
    </audio>
    <script>
        // Memastikan musik tetap berputar jika browser memblokir autoplay otomatis
        document.addEventListener('click', function() {
            var audio = document.getElementById('bgMusic');
            if(audio.paused) {
                audio.play();
            }
        }, { once: true });
    </script>
    <?php endif; ?>
</body>
</html>
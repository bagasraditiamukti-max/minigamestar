<?php
session_start();
include 'koneksi.php';

// Proteksi Sesi: Jika belum login, tendang balik ke index.html
if (!isset($_SESSION['login'])) {
    header("Location: index.html");
    exit;
}

$user_id = $_SESSION['user_id'];

// Mengambil preferensi lengkap pemain dari database
$result = mysqli_query($conn, "SELECT username, current_level, tema, musik, bahasa FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($result);

$username = $user['username'];
$current_level = $user['current_level'];

if ($current_level > 100) { $current_level = 100; }

// API Backend: Menerima pembaruan level dari JS via AJAX Fetch
if (isset($_POST['update_level'])) {
    $next_level = intval($_POST['update_level']);
    $query = "UPDATE users SET current_level = $next_level WHERE id = '$user_id'";
    if (mysqli_query($conn, $query)) {
        echo "sukses";
    } else {
        echo "gagal";
    }
    exit;
}

// Logika Kamus Bahasa Game
$lang = isset($user['bahasa']) ? $user['bahasa'] : 'indo';
$text = [
    'indo' => [
        'pemain' => 'Pemain',
        'waktu' => 'Sisa Waktu',
        'nav_menu' => '🏠 Menu Utama (Edit Profil / Pengaturan)',
        'sub' => 'Giring bintang dari START ke GOAL sebelum waktu habis tanpa keluar garis!',
        'konfirmasi' => 'Konfirmasi Bermain',
        'siap' => 'Siap menaklukkan Level ',
        'kembali' => '🏠 Kembali',
        'lanjut' => '▶️ Lanjut'
    ],
    'inggris' => [
        'pemain' => 'Player',
        'waktu' => 'Time Left',
        'nav_menu' => '🏠 Main Menu (Edit Profile / Settings)',
        'sub' => 'Drag the star from START to GOAL before time runs out without leaving the line!',
        'konfirmasi' => 'Play Confirmation',
        'siap' => 'Ready to conquer Level ',
        'kembali' => '🏠 Back',
        'lanjut' => '▶️ Continue'
    ]
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>CloudStar Game - Main Game</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="<?= ($user['tema'] == 'terang') ? 'tema-terang' : ''; ?>">

    <div class="header-panel">
        <h1>CloudStar Game ☁️⭐️</h1>
        <div class="info">
            <?= $text[$lang]['pemain']; ?>: <span style="color:#a7f3d0; font-weight:bold;"><?= htmlspecialchars($username); ?></span> | 
            Level: <span class="badge" id="level-badge"><?= $current_level; ?> / 100</span> |
            <?= $text[$lang]['waktu']; ?>: <span id="timer-badge" style="background: #ef4444; color: white; padding: 2px 8px; border-radius: 5px; font-weight: bold;">30s</span>
        </div>
        
        <div style="margin-top: 10px; display: flex; justify-content: center; gap: 15px; align-items: center;">
            <a href="home.php" style="color: #38bdf8; font-weight: bold; font-size: 14px; text-decoration: none; border: 1px solid #38bdf8; padding: 3px 10px; border-radius: 4px;"><?= $text[$lang]['nav_menu']; ?></a>
            <a href="leaderboard.php" style="color: #fdbb2d; font-weight: bold; font-size: 14px; text-decoration: underline;">🏆 Top 5 Leaderboard</a>
            <a href="logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar?')" style="color: #f87171; font-weight: bold; font-size: 14px; text-decoration: none; border: 1px solid #f87171; padding: 3px 10px; border-radius: 4px;">| Keluar</a>
        </div>
        <p style="margin:10px 0 0 0; font-size:13px; color:#cbd5e1;"><?= $text[$lang]['sub']; ?></p>
    </div>

    <div id="gameModal" class="modal-overlay" style="display: flex;">
        <div class="modal-content">
            <h3 id="modalTitle"><?= $text[$lang]['konfirmasi']; ?></h3>
            <p id="modalDesc"><?= $text[$lang]['siap'] . $current_level; ?>?</p>
            <div class="modal-btns">
                <a href="home.php" class="modal-btn" style="background:#ef4444; color:white; text-decoration:none; display:inline-block; line-height:35px; height:35px; border-radius:5px; flex:1; text-align:center;"><?= $text[$lang]['kembali']; ?></a>
                <button onclick="closeModalStartGame()" id="modalActionBtn" class="modal-btn" style="background:#22c55e; color:white; flex:1;"><?= $text[$lang]['lanjut']; ?></button>
            </div>
        </div>
    </div>

    <canvas id="mainGameCanvas" width="800" height="450"></canvas>

    <script>
        const canvas = document.getElementById('mainGameCanvas');
        const ctx = canvas.getContext('2d');
        const levelBadge = document.getElementById('level-badge');
        const timerBadge = document.getElementById('timer-badge');

        const gameModal = document.getElementById('gameModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalDesc = document.getElementById('modalDesc');
        const modalActionBtn = document.getElementById('modalActionBtn');

        let level = <?= $current_level; ?>; 
        let gameActive = false;
        let timeLeft = 30; 
        let timerInterval = null;

        let player = { x: 0, y: 0, radius: 6 };
        let startZone = { x: 60, y: 225, radius: 20 };
        let finishZone = { x: 740, y: 225, radius: 20 };

        function startTimer() {
            clearInterval(timerInterval); 
            timeLeft = Math.max(12, 30 - Math.floor(level / 4)); 
            timerBadge.innerText = timeLeft + "s";

            timerInterval = setInterval(() => {
                if (gameActive) {
                    timeLeft--;
                    timerBadge.innerText = timeLeft + "s";

                    if (timeLeft <= 0) {
                        gameActive = false;
                        clearInterval(timerInterval);
                        
                        modalTitle.innerText = "⏱️ TIME OUT / WAKTU HABIS";
                        modalDesc.innerText = "Kamu kehabisan waktu sebelum mencapai GOAL. Coba lagi?";
                        modalActionBtn.innerText = "🔄 Main Lagi";
                        modalActionBtn.onclick = function() {
                            resetToStart();
                            gameModal.style.display = 'none';
                        };
                        gameModal.style.display = 'flex';
                    }
                }
            }, 1000);
        }

        function closeModalStartGame() {
            gameModal.style.display = 'none';
            resetToStart();
        }

        function resetToStart() {
            player.x = startZone.x;
            player.y = startZone.y;
            gameActive = false;
            clearInterval(timerInterval);
            timerBadge.innerText = "30s";
        }

        function drawTrack() {
            ctx.fillStyle = '<?= ($user['tema'] == 'terang') ? '#cbd5e1' : '#0f172a'; ?>'; 
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            let lineWidth = Math.max(12, 45 - (level * 0.33));
            ctx.strokeStyle = '#ffffff';
            ctx.lineCap = 'round'; ctx.lineJoin = 'round';
            ctx.lineWidth = lineWidth;

            ctx.beginPath();
            ctx.moveTo(startZone.x, startZone.y);

            let nodes = 3 + Math.floor(level / 10); 
            let segmentWidth = (finishZone.x - startZone.x) / nodes;

            for (let i = 1; i < nodes; i++) {
                let nextX = startZone.x + (i * segmentWidth);
                let amplitude = 120 + (level * 0.5); 
                let nextY = 225 + Math.sin(i * level * 45) * (amplitude * 0.6);
                nextY = Math.max(60, Math.min(390, nextY));
                ctx.lineTo(nextX, nextY);
            }

            ctx.lineTo(finishZone.x, finishZone.y);
            ctx.stroke();

            ctx.fillStyle = '#22c55e';
            ctx.beginPath(); ctx.arc(startZone.x, startZone.y, startZone.radius, 0, Math.PI*2); ctx.fill();
            ctx.fillStyle = '#eab308';
            ctx.beginPath(); ctx.arc(finishZone.x, finishZone.y, finishZone.radius, 0, Math.PI*2); ctx.fill();

            ctx.fillStyle = '#1e293b'; ctx.font = 'bold 10px sans-serif';
            ctx.fillText("START", startZone.x - 16, startZone.y + 4); ctx.fillText("GOAL", finishZone.x - 14, finishZone.y + 4);
        }

        function checkCollision() {
            let pixel = ctx.getImageData(player.x, player.y, 1, 1).data;
            let r = pixel[0], g = pixel[1], b = pixel[2];

            let isBgGelap = (r === 15 && g === 23 && b === 42);
            let isBgTerang = (r === 203 && g === 213 && b === 225);

            if (gameActive && (isBgGelap || isBgTerang)) {
                gameActive = false;
                clearInterval(timerInterval);
                
                modalTitle.innerText = "💥 KELUAR JALUR!";
                modalDesc.innerText = "Kursor keluar dari lintasan awan! Ulangi lagi?";
                modalActionBtn.innerText = "🔄 Main Lagi";
                modalActionBtn.onclick = function() {
                    resetToStart();
                    gameModal.style.display = 'none';
                };
                gameModal.style.display = 'flex';
            }
        }

        function drawPlayer() {
            ctx.fillStyle = '#fdbb2d';
            ctx.shadowBlur = 8; ctx.shadowColor = '#fdbb2d';
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
                    clearInterval(timerInterval);
                    levelUp();
                }
            }
            drawPlayer();
            requestAnimationFrame(gameLoop);
        }

        function levelUp() {
            if (level >= 100) {
                modalTitle.innerText = "🌟 CONGRATULATIONS! 🌟";
                modalDesc.innerText = "Luar biasa! Kamu berhasil menamatkan seluruh 100 Level CloudStar!";
                modalActionBtn.innerText = "🏠 Selesai";
                modalActionBtn.onclick = function() { window.location = 'home.php'; };
                gameModal.style.display = 'flex';
                return;
            }

            level++;
            levelBadge.innerText = level + " / 100";

            modalTitle.innerText = "🎉 LEVEL UP!";
            modalDesc.innerText = "Hebat! Kamu naik ke Level " + level + ". Siap tantangan berikutnya?";
            modalActionBtn.innerText = "▶️ Lanjut";
            
            modalActionBtn.onclick = function() {
                let formData = new FormData();
                formData.append('update_level', level);

                fetch('game.php', { method: 'POST', body: formData })
                .then(response => response.text())
                .then(data => {
                    if(data.trim() === "sukses") {
                        gameModal.style.display = 'none';
                        resetToStart();
                    } else {
                        alert("Gagal sinkron database.");
                    }
                });
            };
            gameModal.style.display = 'flex';
        }

        canvas.addEventListener('mousemove', (e) => {
            const rect = canvas.getBoundingClientRect();
            player.x = e.clientX - rect.left;
            player.y = e.clientY - rect.top;

            if (!gameActive) {
                let distToStart = Math.hypot(player.x - startZone.x, player.y - startZone.y);
                if (distToStart < startZone.radius) { 
                    gameActive = true; 
                    startTimer(); 
                }
            }
        });

        resetToStart();
        gameLoop();
    </script>

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
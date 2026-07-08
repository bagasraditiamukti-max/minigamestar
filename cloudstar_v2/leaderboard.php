<?php
session_start();
include 'koneksi.php';

// Proteksi Sesi: Jika belum login, tendang balik ke pintu utama index.html
if (!isset($_SESSION['login'])) {
    header("Location: index.html");
    exit;
}

// Query SQL Lanjutan: Mengambil 5 user dengan level tertinggi (ORDER BY dan LIMIT)
// Menyaring user yang sudah mengisi username (NOT NULL)
$query = "SELECT username, current_level FROM users 
          WHERE username IS NOT NULL 
          ORDER BY current_level DESC, created_at ASC 
          LIMIT 5";

$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>CloudStar Game - Leaderboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="<?= (isset($user['tema']) && $user['tema'] == 'terang') ? 'tema-terang' : ''; ?>">
    <div class="box" style="width: 400px;">
        <h2>🏆 TOP 5 PEMAIN 🏆</h2>
        <p>Papan Peringkat Bintang Tertinggi</p>

        <table class="table-leaderboard">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Pemain</th>
                    <th>Level Tertinggi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        // Memberikan kelas warna khusus untuk juara 1, 2, dan 3
                        $rank_class = "";
                        if ($no == 1) $rank_class = "class='rank-1'";
                        if ($no == 2) $rank_class = "class='rank-2'";
                        if ($no == 3) $rank_class = "class='rank-3'";

                        echo "<tr>";
                        echo "<td $rank_class>" . $no . "</td>";
                        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                        echo "<td style='font-weight:bold; color:#38bdf8;'>" . $row['current_level'] . " / 100</td>";
                        echo "</tr>";
                        $no++;
                    }
                } else {
                    echo "<tr><td colspan='3'>Belum ada data pemain.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <a href="game.php" class="btn-kembali">🎮 Kembali Main</a>
    </div>
</body>
</html>
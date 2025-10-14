<?php
include 'koneksi.php';
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'atasan') {
    header("Location: index.php");
}

if (isset($_GET['setuju'])) {
    $id = $_GET['setuju'];
    mysqli_query($koneksi, "UPDATE pengajuan SET status='Disetujui' WHERE id_pengajuan=$id");
}

if (isset($_GET['tolak'])) {
    $id = $_GET['tolak'];
    mysqli_query($koneksi, "UPDATE pengajuan SET status='Ditolak' WHERE id_pengajuan=$id");
}

$data = mysqli_query($koneksi, "SELECT * FROM pengajuan");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Atasan</title>
</head>
<body>
    <h2>Dashboard Atasan - Persetujuan Pengajuan Kredit</h2>
    <table border="1" cellpadding="8">
        <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>Jumlah</th>
            <th>Keperluan</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        <?php while ($d = mysqli_fetch_assoc($data)) { ?>
        <tr>
            <td><?= $d['id_pengajuan'] ?></td>
            <td><?= $d['nama'] ?></td>
            <td><?= $d['jumlah'] ?></td>
            <td><?= $d['keperluan'] ?></td>
            <td><?= $d['status'] ?></td>
            <td>
                <a href="?setuju=<?= $d['id_pengajuan'] ?>">Setujui</a> |
                <a href="?tolak=<?= $d['id_pengajuan'] ?>">Tolak</a>
            </td>
        </tr>
        <?php } ?>
    </table>
    <br>
    <a href="index.php">Logout</a>
</body>
</html>

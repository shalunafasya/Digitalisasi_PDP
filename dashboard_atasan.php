<?php
include 'koneksi.php';
session_start();

if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'atasan') {
    header("Location: index.php");
    exit;
}

$res = mysqli_query($koneksi, "SELECT id_pengajuan, nama, nik, dealer, merk, harga_kendaraan, lama_kredit, status FROM pengajuan ORDER BY id_pengajuan DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Dashboard Atasan - Persetujuan</title>
    <style>
        body{font-family: Arial; margin:20px; max-width:1100px;}
        table{border-collapse:collapse; width:100%;}
        th,td{border:1px solid #ddd; padding:8px; text-align:left;}
        th{background:#f4f4f4;}
        a.btn{display:inline-block; padding:6px 10px; background:#1976d2;color:#fff;text-decoration:none;border-radius:4px;}
        .small{font-size:13px;color:#555;}
    </style>
</head>
<body>
<h2>Dashboard Atasan - Persetujuan Pengajuan</h2>
<p class="small">Klik <strong>Detail</strong> untuk melihat data lengkap & file KTP lalu Setujui / Tolak.</p>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Nama</th>
            <th>NIK</th>
            <th>Dealer</th>
            <th>Merk</th>
            <th>Harga</th>
            <th>Tenor</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($r = mysqli_fetch_assoc($res)): ?>
        <tr>
            <td><?= htmlspecialchars($r['id_pengajuan']) ?></td>
            <td><?= htmlspecialchars($r['nama']) ?></td>
            <td><?= htmlspecialchars($r['nik']) ?></td>
            <td><?= htmlspecialchars($r['dealer']) ?></td>
            <td><?= htmlspecialchars($r['merk']) ?></td>
            <td><?= isset($r['harga_kendaraan']) ? number_format($r['harga_kendaraan'],0,',','.') : '-' ?></td>
            <td><?= htmlspecialchars($r['lama_kredit']) ?> bulan</td>
            <td><?= htmlspecialchars($r['status']) ?></td>
            <td>
                <a class="btn" href="detail.php?id=<?= urlencode($r['id_pengajuan']) ?>">Detail</a>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

<br>
<a href="index.php" class="small">Logout</a>
</body>
</html>

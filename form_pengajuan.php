<?php
include 'koneksi.php';
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
}
if (isset($_POST['submit'])) {
    $nama = $_POST['nama'];
    $jumlah = $_POST['jumlah'];
    $keperluan = $_POST['keperluan'];
    $status = "Menunggu Persetujuan";

    $query = "INSERT INTO pengajuan (nama, jumlah, keperluan, status) VALUES ('$nama','$jumlah','$keperluan','$status')";
    mysqli_query($koneksi, $query);
    echo "<script>alert('Pengajuan berhasil dikirim!');</script>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Form Pengajuan Kredit</title>
</head>
<body>
    <h2>Form Pengajuan Kredit</h2>
    <form method="POST">
        Nama: <input type="text" name="nama" required><br><br>
        Jumlah Kredit: <input type="number" name="jumlah" required><br><br>
        Keperluan: <textarea name="keperluan" required></textarea><br><br>
        <button type="submit" name="submit">Kirim Pengajuan</button>
    </form>
    <br>
    <a href="index.php">Logout</a>
</body>
</html>

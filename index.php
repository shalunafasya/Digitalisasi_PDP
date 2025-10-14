<?php
include 'koneksi.php';
session_start();

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = mysqli_query($koneksi, "SELECT * FROM user WHERE username='$username' AND password='$password'");
    $data = mysqli_fetch_assoc($query);

    if ($data) {
        $_SESSION['username'] = $data['username'];
        $_SESSION['role'] = $data['role'];
        if ($data['role'] == 'pegawai') {
            header("Location: form_pengajuan.php");
        } else {
            header("Location: dashboard_atasan.php");
        }
    } else {
        echo "<script>alert('Username atau password salah!');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Pengajuan Kredit</title>
</head>
<body>
    <h2>Login</h2>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <button type="submit" name="login">Login</button>
    </form>
</body>
</html>

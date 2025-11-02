<?php
include 'koneksi.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

$upload_dir = __DIR__ . '/uploads/ktp/';
$upload_db_path_prefix = 'uploads/ktp/'; 
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
$max_size = 1572864; 

$messages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $nama               = isset($_POST['nama']) ? trim($_POST['nama']) : '';
    $nik                = isset($_POST['nik']) ? trim($_POST['nik']) : '';
    $tgl_lahir          = isset($_POST['tgl_lahir']) ? trim($_POST['tgl_lahir']) : null;
    $status_perkawinan  = isset($_POST['status_perkawinan']) ? trim($_POST['status_perkawinan']) : '';
    $data_pasangan      = isset($_POST['data_pasangan']) ? trim($_POST['data_pasangan']) : '';

    $dealer             = isset($_POST['dealer']) ? trim($_POST['dealer']) : '';
    $merk               = isset($_POST['merk']) ? trim($_POST['merk']) : '';
    $model              = isset($_POST['model']) ? trim($_POST['model']) : '';
    $tipe               = isset($_POST['tipe']) ? trim($_POST['tipe']) : '';
    $warna              = isset($_POST['warna']) ? trim($_POST['warna']) : '';
    $harga_kendaraan    = isset($_POST['harga_kendaraan']) ? trim($_POST['harga_kendaraan']) : '0';

    $asuransi           = isset($_POST['asuransi']) ? trim($_POST['asuransi']) : '';
    $dp                 = isset($_POST['dp']) ? trim($_POST['dp']) : '0';
    $lama_kredit        = isset($_POST['lama_kredit']) ? intval($_POST['lama_kredit']) : 0;
    $angsuran           = isset($_POST['angsuran']) ? trim($_POST['angsuran']) : '0';

    $status = "Menunggu Persetujuan";

    if ($nama === '' || $nik === '' || $dealer === '' || $merk === '') {
        $messages[] = "Nama, NIK, Dealer, & Merk wajib diisi.";
    }

    $file_ktp_db = null;
    if (isset($_FILES['ktp']) && $_FILES['ktp']['error'] !== UPLOAD_ERR_NO_FILE) {
        $f = $_FILES['ktp'];

        if ($f['error'] !== UPLOAD_ERR_OK) {
            $messages[] = "Error upload file (kode: {$f['error']}).";
        } else {
            if (!in_array($f['type'], $allowed_types)) {
                $messages[] = "Tipe file tidak diperbolehkan. Hanya JPG / JPEG / PNG.";
            }

            if ($f['size'] > $max_size) {
                $messages[] = "Ukuran file terlalu besar. Maksimum 1.5 MB.";
            }

            if (empty($messages)) {
                if (!is_dir($upload_dir)) {
                    if (!mkdir($upload_dir, 0755, true)) {
                        $messages[] = "Gagal membuat folder upload.";
                    }
                }

                if (empty($messages)) {
                    $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
                    $ext = strtolower($ext);
                    $newname = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                    $target = $upload_dir . $newname;

                    if (move_uploaded_file($f['tmp_name'], $target)) {
                        $file_ktp_db = $upload_db_path_prefix . $newname;
                    } else {
                        $messages[] = "Gagal memindahkan file upload.";
                    }
                }
            }
        }
    }

    if (empty($messages)) {
        $sql = "INSERT INTO pengajuan 
            (file_ktp, nama, nik, tgl_lahir, status_perkawinan, data_pasangan, dealer, merk, model, tipe, warna, harga_kendaraan, asuransi, dp, lama_kredit, angsuran, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($koneksi, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'ssssssssssssssiss',
                $file_ktp_db,
                $nama,
                $nik,
                $tgl_lahir,
                $status_perkawinan,
                $data_pasangan,
                $dealer,
                $merk,
                $model,
                $tipe,
                $warna,
                $harga_kendaraan,
                $asuransi,
                $dp,
                $lama_kredit,
                $angsuran,
                $status
            );

            if (mysqli_stmt_execute($stmt)) {
                $messages[] = "Pengajuan berhasil dikirim.";
                $nama = $nik = $tgl_lahir = $status_perkawinan = $data_pasangan = '';
                $dealer = $merk = $model = $tipe = $warna = $harga_kendaraan = '';
                $asuransi = $dp = $lama_kredit = $angsuran = '';
                $file_ktp_db = null;
            } else {
                $messages[] = "Gagal menyimpan data ke database.";
                if (!empty($file_ktp_db) && file_exists(__DIR__ . '/' . $file_ktp_db)) {
                    @unlink(__DIR__ . '/' . $file_ktp_db);
                }
            }

            mysqli_stmt_close($stmt);
        } else {
            $messages[] = "Query error: " . mysqli_error($koneksi);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Form Pengajuan Kredit</title>
    <meta charset="utf-8" />
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; max-width:900px; }
        label { display:block; margin-top:12px; font-weight:600; }
        input[type="text"], input[type="date"], input[type="number"], select, textarea {
            width:100%; padding:8px; margin-top:6px; box-sizing:border-box;
        }
        textarea { min-height:80px; }
        .note { font-size:13px; color:#555; margin-top:6px; }
        .messages { margin:12px 0; padding:10px; border-radius:6px; }
        .messages.success { background:#e6ffed; color:#116; border:1px solid #b8f0c6; }
        .messages.error { background:#ffe9e9; color:#800; border:1px solid #f0b8b8; }
        .btn { display:inline-block; margin-top:12px; padding:10px 16px; background:#1976d2; color:#fff; text-decoration:none; border-radius:6px; border:none; cursor:pointer; }
        table { border-collapse:collapse; width:100%; margin-top:18px; }
        table th, table td { border:1px solid #ddd; padding:8px; text-align:left; }
    </style>
</head>
<body>

<h2>Form Pengajuan Kredit</h2>

<?php
if (!empty($messages)) {
    $is_error = false;
    foreach ($messages as $m) {
        if (stripos($m, 'berhasil') !== false) { $is_error = $is_error || false; } 
        else { $is_error = $is_error || true; }
    }
    echo '<div class="messages ' . ($is_error ? 'error' : 'success') . '">';
    foreach ($messages as $m) {
        echo '<div>' . htmlspecialchars($m) . '</div>';
    }
    echo '</div>';
}
?>

<form method="POST" enctype="multipart/form-data">
    <h3>Data Konsumen</h3>
    <label>Scan KTP (JPG / PNG) - max 1.5 MB</label>
    <input type="file" name="ktp" accept=".jpg,.jpeg,.png">

    <label>Nama*</label>
    <input type="text" name="nama" required value="<?= isset($nama) ? htmlspecialchars($nama) : '' ?>">

    <label>NIK*</label>
    <input type="text" name="nik" required value="<?= isset($nik) ? htmlspecialchars($nik) : '' ?>">

    <label>Tanggal Lahir</label>
    <input type="date" name="tgl_lahir" value="<?= isset($tgl_lahir) ? htmlspecialchars($tgl_lahir) : '' ?>">

    <label>Status Perkawinan</label>
    <select name="status_perkawinan">
        <option value="">-- Pilih --</option>
        <option value="Belum Menikah" <?= (isset($status_perkawinan) && $status_perkawinan=='Belum Menikah') ? 'selected':'' ?>>Belum Menikah</option>
        <option value="Menikah" <?= (isset($status_perkawinan) && $status_perkawinan=='Menikah') ? 'selected':'' ?>>Menikah</option>
        <option value="Cerai" <?= (isset($status_perkawinan) && $status_perkawinan=='Cerai') ? 'selected':'' ?>>Cerai</option>
    </select>

    <label>Data Pasangan (jika ada)</label>
    <input type="text" name="data_pasangan" value="<?= isset($data_pasangan) ? htmlspecialchars($data_pasangan) : '' ?>">

    <br><br>
    <h3>Data Kendaraan</h3>
    <label>Dealer*</label>
    <input type="text" name="dealer" required value="<?= isset($dealer) ? htmlspecialchars($dealer) : '' ?>">

    <label>Merk*</label>
    <input type="text" name="merk" required value="<?= isset($merk) ? htmlspecialchars($merk) : '' ?>">

    <label>Model</label>
    <input type="text" name="model" value="<?= isset($model) ? htmlspecialchars($model) : '' ?>">

    <label>Tipe</label>
    <input type="text" name="tipe" value="<?= isset($tipe) ? htmlspecialchars($tipe) : '' ?>">

    <label>Warna</label>
    <input type="text" name="warna" value="<?= isset($warna) ? htmlspecialchars($warna) : '' ?>">

    <label>Harga Kendaraan</label>
    <input type="number" name="harga_kendaraan" step="0.01" value="<?= isset($harga_kendaraan) ? htmlspecialchars($harga_kendaraan) : '' ?>">

    <br><br>
    <h3>Data Pinjaman</h3>
    <label>Asuransi</label>
    <input type="text" name="asuransi" value="<?= isset($asuransi) ? htmlspecialchars($asuransi) : '' ?>">

    <label>Down Payment (DP)</label>
    <input type="number" name="dp" step="0.01" value="<?= isset($dp) ? htmlspecialchars($dp) : '' ?>">

    <label>Lama Kredit (bulan)</label>
    <input type="number" name="lama_kredit" value="<?= isset($lama_kredit) ? htmlspecialchars($lama_kredit) : '' ?>">

    <label>Angsuran / bulan</label>
    <input type="number" name="angsuran" step="0.01" value="<?= isset($angsuran) ? htmlspecialchars($angsuran) : '' ?>">

    <br>
    <button type="submit" class="btn">Kirim Pengajuan</button>
</form>

<hr>

<h3>Daftar Riwayat Pengajuan</h3>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>NIK</th>
            <th>Dealer</th>
            <th>Merk</th>
            <th>Harga</th>
            <th>Tenor</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $u = $_SESSION['username']; 
    $res = mysqli_query($koneksi, "SELECT id_pengajuan, nama, nik, dealer, merk, harga_kendaraan, lama_kredit, status FROM pengajuan ORDER BY id_pengajuan DESC");
    while ($row = mysqli_fetch_assoc($res)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id_pengajuan']) . "</td>";
        echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
        echo "<td>" . htmlspecialchars($row['nik']) . "</td>";
        echo "<td>" . htmlspecialchars($row['dealer']) . "</td>";
        echo "<td>" . htmlspecialchars($row['merk']) . "</td>";
        echo "<td>" . htmlspecialchars(number_format($row['harga_kendaraan'],0,',','.')) . "</td>";
        echo "<td>" . htmlspecialchars($row['lama_kredit']) . " bulan</td>";
        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
        echo "</tr>";
    }
    ?>
    </tbody>
</table>

<br>
<a href="index.php">Logout</a>

</body>
</html>

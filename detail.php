<?php
include 'koneksi.php';
session_start();

if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'atasan') {
    header("Location: index.php");
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo "ID pengajuan tidak valid.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && in_array($_POST['action'], ['approve','reject'])) {
        $action = $_POST['action'];
        if ($action === 'approve') {
            $newStatus = 'Disetujui';
        } else {
            $newStatus = 'Ditolak';
        }
        $stmt = mysqli_prepare($koneksi, "UPDATE pengajuan SET status = ? WHERE id_pengajuan = ?");
        mysqli_stmt_bind_param($stmt, 'si', $newStatus, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: dashboard_atasan.php?msg=" . urlencode("Status diubah menjadi: $newStatus"));
        exit;
    }
}

$stmt = mysqli_prepare($koneksi, "SELECT * FROM pengajuan WHERE id_pengajuan = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$data) {
    echo "Data tidak ditemukan.";
    exit;
}

$file_ktp = '';
if (!empty($data['file_ktp'])) {
    $file_ktp = $data['file_ktp']; 
    if (!file_exists(__DIR__ . '/' . $file_ktp)) {
        $file_ktp = '';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Detail Pengajuan #<?= htmlspecialchars($data['id_pengajuan']) ?></title>
    <style>
        body{font-family: Arial; margin:20px; max-width:900px;}
        .grid{display:grid; grid-template-columns: 1fr 1fr; gap:12px;}
        .field{background:#fafafa; padding:10px; border:1px solid #eee; border-radius:6px;}
        .label{font-weight:700; color:#333; margin-bottom:6px;}
        .val{color:#111;}
        .actions{margin-top:16px;}
        .btn{display:inline-block; padding:10px 14px; border-radius:6px; color:#fff; text-decoration:none; margin-right:8px;}
        .btn-approve{background:#2e7d32;}
        .btn-reject{background:#c62828;}
        .btn-back{background:#555;}
        .ktp-thumb{max-width:160px; border:1px solid #ddd; padding:6px; border-radius:6px; cursor:pointer;}
        .modal{display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.6); align-items:center; justify-content:center;}
        .modal img{max-width:90%; max-height:90%; border-radius:6px; box-shadow:0 6px 30px rgba(0,0,0,0.4);}
        .modal .close{position:absolute; right:20px; top:20px; color:#fff; font-size:24px; cursor:pointer;}
    </style>
</head>
<body>

<h2>Detail Pengajuan #<?= htmlspecialchars($data['id_pengajuan']) ?></h2>

<div style="margin-bottom:12px;">
    <a href="dashboard_atasan.php" class="btn btn-back" style="background:#1976d2; color:#fff; padding:8px 12px; border-radius:6px; text-decoration:none;">&larr; Kembali ke Dashboard</a>
</div>

<div class="grid">
    <div class="field">
        <div class="label">Nama</div>
        <div class="val"><?= htmlspecialchars($data['nama']) ?></div>

        <div class="label">NIK</div>
        <div class="val"><?= htmlspecialchars($data['nik']) ?></div>

        <div class="label">Tanggal Lahir</div>
        <div class="val"><?= htmlspecialchars($data['tgl_lahir']) ?></div>

        <div class="label">Status Perkawinan</div>
        <div class="val"><?= htmlspecialchars($data['status_perkawinan']) ?></div>

        <div class="label">Data Pasangan</div>
        <div class="val"><?= htmlspecialchars($data['data_pasangan']) ?></div>

        <div class="label">Asuransi</div>
        <div class="val"><?= htmlspecialchars($data['asuransi']) ?></div>
    </div>

    <div class="field">
        <div class="label">Dealer</div>
        <div class="val"><?= htmlspecialchars($data['dealer']) ?></div>

        <div class="label">Merk</div>
        <div class="val"><?= htmlspecialchars($data['merk']) ?></div>

        <div class="label">Model</div>
        <div class="val"><?= htmlspecialchars($data['model']) ?></div>

        <div class="label">Tipe</div>
        <div class="val"><?= htmlspecialchars($data['tipe']) ?></div>

        <div class="label">Warna</div>
        <div class="val"><?= htmlspecialchars($data['warna']) ?></div>

        <div class="label">Harga Kendaraan</div>
        <div class="val"><?= isset($data['harga_kendaraan']) ? number_format($data['harga_kendaraan'],0,',','.') : '-' ?></div>

        <div class="label">DP</div>
        <div class="val"><?= isset($data['dp']) ? number_format($data['dp'],0,',','.') : '-' ?></div>

        <div class="label">Lama Kredit</div>
        <div class="val"><?= htmlspecialchars($data['lama_kredit']) ?> bulan</div>

        <div class="label">Angsuran /bulan</div>
        <div class="val"><?= isset($data['angsuran']) ? number_format($data['angsuran'],0,',','.') : '-' ?></div>
    </div>
</div>

<div style="margin-top:16px;" class="field">
    <div class="label">Status Sekarang</div>
    <div class="val"><strong><?= htmlspecialchars($data['status']) ?></strong></div>
</div>

<div style="margin-top:14px;">
    <div class="label">Scan KTP</div>
    <?php if ($file_ktp): ?>
        <img src="<?= htmlspecialchars($file_ktp) ?>" alt="KTP" class="ktp-thumb" id="ktpThumb">
        <div class="small" style="margin-top:8px;color:#666;">Klik gambar untuk memperbesar.</div>

        <div class="modal" id="modalKtp">
            <span class="close" id="modalClose">&times;</span>
            <img src="<?= htmlspecialchars($file_ktp) ?>" alt="KTP Full">
        </div>

        <script>
            const thumb = document.getElementById('ktpThumb');
            const modal = document.getElementById('modalKtp');
            const closeBtn = document.getElementById('modalClose');
            thumb && thumb.addEventListener('click', ()=> modal.style.display='flex');
            closeBtn && closeBtn.addEventListener('click', ()=> modal.style.display='none');
            modal && modal.addEventListener('click', (e) => { if(e.target === modal) modal.style.display='none'; });
        </script>
    <?php else: ?>
        <div class="small">File KTP tidak tersedia.</div>
    <?php endif; ?>
</div>

<div class="actions">
    <form method="POST" onsubmit="return confirm('Yakin akan mengubah status pengajuan?');" style="display:inline-block;">
        <input type="hidden" name="action" value="approve">
        <button type="submit" class="btn btn-approve" style="background:#2e7d32">Setujui</button>
    </form>

    <form method="POST" onsubmit="return confirm('Yakin akan menolak pengajuan ini?');" style="display:inline-block;">
        <input type="hidden" name="action" value="reject">
        <button type="submit" class="btn btn-reject" style="background:#c62828">Tolak</button>
    </form>
</div>

</body>
</html>

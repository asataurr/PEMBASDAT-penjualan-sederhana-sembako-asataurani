<?php
// index.php

include 'header.php';

// (kode total transaksi, pendapatan, dan barang terlaris Anda di sini)
$res1 = $conn->query("SELECT COUNT(*) AS total FROM transaksi");
$total_transaksi = $res1 ? ($res1->fetch_assoc()['total'] ?? 0) : 0; 
$res2 = $conn->query("SELECT SUM(total_harga) AS total FROM transaksi");
$total_pendapatan = $res2 ? ($res2->fetch_assoc()['total'] ?? 0) : 0;
$res3 = $conn->query("
    SELECT b.nama_barang, SUM(t.jumlah) AS total_unit_terjual
    FROM transaksi t
    JOIN barang b ON b.id_barang = t.id_barang
    GROUP BY t.id_barang, b.nama_barang 
    ORDER BY total_unit_terjual DESC
    LIMIT 1
");
$barang_terlaris_data = $res3 ? $res3->fetch_assoc() : null;
$barang_terlaris = $barang_terlaris_data['nama_barang'] ?? 'Belum ada transaksi';
$total_unit = $barang_terlaris_data['total_unit_terjual'] ?? 0;

// --- KODE BARU: Query untuk mengambil barang terbaru (max 8) ---
$barang_list = $conn->query("SELECT id_barang, nama_barang, harga, gambar FROM barang ORDER BY id_barang DESC LIMIT 8");
?>

<div class="dashboard-header d-flex flex-column flex-md-row align-items-start justify-content-between mb-4 gap-3">
    <div>
        <h1 class="mb-1">Toko Penjualan Sembako Asataurani</h1>
        <p class="text-muted mb-0">Manajemen terbaik sembako kita!</p>
    </div>
    <div class="dashboard-actions d-flex gap-2">
        <a href="barang_create.php" class="btn btn-primary">+ Tambah Barang</a>
        <a href="transaksi_create.php" class="btn btn-success">Buat Transaksi</a>
    </div>
</div>

<!-- Stat summary cards -->
<div class="dashboard-grid mb-5">
    <div class="stat-card card-hover">
        <div class="stat-icon" aria-hidden="true">📦</div>
        <div>
            <div class="stat-label">Total Transaksi</div>
            <div class="stat-value"><?= $total_transaksi ?></div>
        </div>
    </div>

    <div class="stat-card card-hover">
        <div class="stat-icon" aria-hidden="true">💰</div>
        <div>
            <div class="stat-label">Total Pendapatan</div>
            <div class="stat-value">Rp<?= number_format($total_pendapatan, 0, ',', '.') ?></div>
        </div>
    </div>

    <div class="stat-card card-hover">
        <div class="stat-icon" aria-hidden="true">🔥</div>
        <div>
            <div class="stat-label">Barang Terlaris</div>
            <div class="stat-value"><?= htmlspecialchars($barang_terlaris) ?></div>
            <small class="text-muted">Terjual: <?= $total_unit ?> unit</small>
        </div>
    </div>
</div>

<hr class="my-4">

<h2 class="mb-3">Koleksi Produk Terbaru</h2>
<div class="product-grid">
    <?php
    if ($barang_list->num_rows > 0) {
        while ($row = $barang_list->fetch_assoc()) {
            $gambar_path = !empty($row['gambar']) 
                ? "assets/img/" . htmlspecialchars($row['gambar']) 
                : "assets/img/default.png";
            ?>

            <div class="product-card card card-hover">
                <div class="product-media">
                    <img src="<?= $gambar_path ?>" class="product-card-img" alt="<?= htmlspecialchars($row['nama_barang']) ?>">
                </div>
                <div class="p-3">
                    <h6 class="mb-1 text-truncate fw-bold" title="<?= htmlspecialchars($row['nama_barang']) ?>"><?= htmlspecialchars($row['nama_barang']) ?></h6>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="text-primary fs-6 fw-bold">Rp<?= number_format($row['harga'], 0, ',', '.') ?></div>
                        <div>
                            <a href="barang_edit.php?id=<?= $row['id_barang'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                        </div>
                    </div>
                </div>
            </div>

            <?php
        }
    } else {
        echo "<div class='p-4 text-center'>Tidak ada produk terbaru yang tersedia.</div>";
    }
    ?>
</div>
<hr class="my-5">
<h2 class="mb-3">Menu Kelola Data</h2>
<div class="d-flex gap-3">
    <a href="barang_list.php" class="btn btn-dark btn-lg">Kelola Barang</a>
    <a href="pembeli_list.php" class="btn btn-dark btn-lg">Kelola Pembeli</a>
    <a href="transaksi_list.php" class="btn btn-dark btn-lg">Kelola Transaksi</a>
</div>
<?php
include 'footer.php';
?>
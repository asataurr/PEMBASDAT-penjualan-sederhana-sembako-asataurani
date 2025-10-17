<?php
// transaksi_list.php - DENGAN FILTER TANGGAL DAN TOMBOL CETAK

include 'header.php';

// Menangkap filter tanggal dari URL
$tgl_mulai = $_GET['tgl_mulai'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';

// --- LOGIKA QUERY UTAMA ---
// Pastikan nama kolom sudah benar: nama_pembeli dan tanggal
$sql = "SELECT 
            t.id_transaksi, 
            p.nama_pembeli,   
            b.nama_barang, 
            t.jumlah, 
            t.harga_satuan, 
            t.total_harga, 
            t.tanggal          
        FROM transaksi t
        JOIN pembeli p ON t.id_pembeli = p.id_pembeli
        JOIN barang b ON t.id_barang = b.id_barang";

$where_clauses = [];

if ($tgl_mulai && $tgl_akhir) {
    // Menggunakan fungsi DATE() untuk membandingkan hanya tanggal (aman untuk DATETIME)
    $where_clauses[] = "DATE(t.tanggal) BETWEEN '$tgl_mulai' AND '$tgl_akhir'";
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(' AND ', $where_clauses);
}

$sql .= " ORDER BY t.tanggal DESC";

// 💡 PENTING: Eksekusi Query
$result = $conn->query($sql);

// --- LOGIKA MODE CETAK (Fitur Bonus) ---
if (isset($_GET['cetak']) && $_GET['cetak'] == 1) {
    // Memanggil file tampilan cetak dan menghentikan eksekusi kode normal
    include 'transaksi_cetak_view.php'; 
    exit();
}
?>

<h1 class="mb-4">Daftar Transaksi</h1>

<div class="card p-3 mb-4 shadow-sm">
    <form method="get" action="transaksi_list.php" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label for="tgl_mulai" class="form-label">Tanggal Mulai:</label>
            <input type="date" name="tgl_mulai" id="tgl_mulai" class="form-control" value="<?= htmlspecialchars($tgl_mulai) ?>">
        </div>
        <div class="col-md-4">
            <label for="tgl_akhir" class="form-label">Tanggal Akhir:</label>
            <input type="date" name="tgl_akhir" id="tgl_akhir" class="form-control" value="<?= htmlspecialchars($tgl_akhir) ?>">
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-info me-2">Filter</button>
            <a href="transaksi_list.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>
</div>

<div class="mb-3 d-flex justify-content-between">
    <div>
        <a href="transaksi_create.php" class="btn btn-success me-2">Catat Transaksi Baru</a>
        <a href="index.php" class="btn btn-secondary">Kembali ke Dashboard</a>
    </div>
    
    <?php 
    $print_params = http_build_query(['tgl_mulai' => $tgl_mulai, 'tgl_akhir' => $tgl_akhir]);
    ?>
    <a href="transaksi_list.php?cetak=1&<?= $print_params ?>" target="_blank" class="btn btn-dark">
        Cetak Laporan
    </a>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID Transaksi</th>
                <th>Pembeli</th>
                <th>Barang</th>
                <th>Jumlah</th>
                <th>Harga Satuan</th>
                <th>Total Harga</th>
                <th>Tanggal</th>
                <th style="width: 100px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result && $result->num_rows > 0) { // Cek $result tidak null
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['id_transaksi'] . "</td>";
                    echo "<td>" . htmlspecialchars($row['nama_pembeli']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['nama_barang']) . "</td>";
                    echo "<td>" . $row['jumlah'] . "</td>";
                    echo "<td>Rp " . number_format($row['harga_satuan'], 0, ',', '.') . "</td>";
                    echo "<td>Rp " . number_format($row['total_harga'], 0, ',', '.') . "</td>";
                    echo "<td>" . $row['tanggal'] . "</td>";
                    echo "<td>";
                    echo "<a href='transaksi_delete.php?id=" . $row['id_transaksi'] . "' class='btn btn-danger btn-sm' onclick=\"return confirm('Yakin ingin menghapus transaksi ini? Stok barang akan dikembalikan.')\">Hapus</a>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='8' class='text-center'>Tidak ada data transaksi.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
<?php
// pembeli_list.php - Tampil semua pembeli (READ) - DENGAN PENCARIAN

include 'header.php'; 

// ✅ FUNGSI BONUS: Logika Pencarian Pembeli
$keyword = $_GET['cari'] ?? ''; // Tangkap kata kunci pencarian dari URL

$sql = "SELECT id_pembeli, nama_pembeli, alamat FROM pembeli";

$where_clauses = [];

if ($keyword) {
    // Menambahkan kondisi pencarian menggunakan LIKE.
    // UPPER() digunakan untuk memastikan pencarian tidak case-sensitive.
    $keyword_safe = $conn->real_escape_string($keyword);
    $where_clauses[] = "UPPER(nama_pembeli) LIKE UPPER('%$keyword_safe%')";
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(' AND ', $where_clauses);
}

$sql .= " ORDER BY id_pembeli DESC"; // Pertahankan urutan dari ID

$result = $conn->query($sql);
?>

<h1 class="mb-4">Daftar Pembeli</h1>

<div class="mb-3 d-flex justify-content-between align-items-center">
    <div>
        <a href="pembeli_create.php" class="btn btn-success me-2">Tambah Pembeli Baru</a>
        <a href="index.php" class="btn btn-secondary">Kembali ke Dashboard</a>
    </div>
    
    <form method="get" action="pembeli_list.php" class="d-flex" style="width: 300px;">
        <input type="text" name="cari" class="form-control me-2" placeholder="Cari Nama Pembeli..." 
               value="<?= htmlspecialchars($keyword) ?>">
        <button type="submit" class="btn btn-primary">Cari</button>
    </form>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nama Pembeli</th>
                <th>Alamat</th>
                <th style="width: 150px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['id_pembeli'] . "</td>";
                    echo "<td>" . htmlspecialchars($row['nama_pembeli']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['alamat']) . "</td>";
                    echo "<td>";
                    echo "<a href='pembeli_edit.php?id=" . $row['id_pembeli'] . "' class='btn btn-warning btn-sm me-1'>Edit</a>";
                    echo "<a href='pembeli_delete.php?id=" . $row['id_pembeli'] . "' class='btn btn-danger btn-sm' onclick=\"return confirm('Yakin ingin menghapus pembeli ini?')\">Hapus</a>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                // Pesan saat tidak ada data ATAU pencarian tidak ditemukan
                $message = $keyword ? "Pembeli dengan nama '{$keyword}' tidak ditemukan." : "Tidak ada data pembeli.";
                echo "<tr><td colspan='4' class='text-center'>{$message}</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>
<?php
include 'footer.php';
?>
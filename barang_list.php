<?php
// barang_list.php

include 'header.php';

// 1. TAMBAH kolom 'gambar' di query SQL
$sql = "SELECT id_barang, nama_barang, harga, stok, gambar FROM barang ORDER BY id_barang DESC";
$result = $conn->query($sql);
?>
<h1 class="mb-4">Daftar Barang</h1>
<div class="mb-3">
    <a href="barang_create.php" class="btn btn-success me-2">Tambah Barang Baru</a>
    <a href="index.php" class="btn btn-secondary">Kembali ke Dashboard</a>
</div>
<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Gambar</th> <th>Nama Barang</th>
                <th>Harga</th>
                <th>Stok</th>
                <th style="width: 150px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    
                    // Tentukan path gambar
                    $gambar_path = !empty($row['gambar']) 
                        ? "assets/img/" . htmlspecialchars($row['gambar']) 
                        : "assets/img/default.png"; // Gunakan gambar default jika kolom kosong
                    
                    // Tentukan jumlah kolom yang digunakan untuk colspan
                    $colspan = 6; 

                    echo "<tr>";
                    echo "<td>" . $row['id_barang'] . "</td>";
                    
                    // 2. TAMPILKAN GAMBAR di sel tabel
                    echo "<td>";
                    echo "<img src='" . $gambar_path . "' style='width: 50px; height: 50px; object-fit: cover; border-radius: 4px;' alt='Gambar Barang'>";
                    echo "</td>";
                    
                    echo "<td>" . htmlspecialchars($row['nama_barang']) . "</td>";
                    echo "<td>Rp " . number_format($row['harga'], 0, ',', '.') . "</td>";
                    echo "<td>" . $row['stok'] . "</td>";
                    echo "<td>";
                    echo "<a href='barang_edit.php?id=" . $row['id_barang'] . "' class='btn btn-warning btn-sm me-1'>Edit</a> ";
                    echo "<a href='barang_delete.php?id=" . $row['id_barang'] . "' class='btn btn-danger btn-sm' onclick=\"return confirm('Yakin ingin menghapus?')\">Hapus</a>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                // Perbarui colspan menjadi 6 (jumlah total kolom)
                echo "<tr><td colspan='6' class='text-center'>Tidak ada data barang.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>
<?php
include 'footer.php';
?>
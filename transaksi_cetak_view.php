<?php
// transaksi_cetak_view.php - Tampilan Laporan Cetak Transaksi
// File ini dipanggil dari transaksi_list.php

// Variabel $conn, $result, $tgl_mulai, $tgl_akhir sudah tersedia dari transaksi_list.php

// Pastikan kursor $result direset jika sudah pernah di-fetch sebelumnya
// Di kasus ini, karena mode cetak exit() di awal, $result masih utuh, jadi tidak perlu reset.

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Laporan Transaksi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Sembunyikan elemen non-esensial saat dicetak */
        @media print {
            .no-print { display: none; }
        }
        body { font-size: 10pt; padding: 20px; }
        table { width: 100%; border-collapse: collapse; }
    </style>
</head>
<body onload="window.print()">

<h2 class="mb-4 text-center">LAPORAN TRANSAKSI</h2>

<?php if ($tgl_mulai && $tgl_akhir): ?>
    <p class="text-center">Periode: **<?= date('d M Y', strtotime($tgl_mulai)) ?>** sampai **<?= date('d M Y', strtotime($tgl_akhir)) ?>**</p>
<?php endif; ?>

<table class="table table-bordered table-striped table-sm">
    <thead class="table-dark">
        <tr>
            <th>ID Transaksi</th>
            <th>Pembeli</th>
            <th>Barang</th>
            <th>Jumlah</th>
            <th>Harga Satuan</th>
            <th>Total Harga</th>
            <th>Tanggal</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Reset kursor query jika diperlukan (Jika mode cetak dipanggil setelah looping utama)
        // Di sini kita asumsikan $result sudah ada dan datanya siap dibaca dari awal
        if ($result->num_rows > 0) {
            // Kita harus mengulang query atau menyimpan datanya ke array 
            // karena $result di transaksi_list.php sudah di-fetch.
            // Cara termudah: jalankan ulang query SQL (tanpa khawatir tentang header/footer)
            $result_cetak = $conn->query($sql);

            while ($row = $result_cetak->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['id_transaksi'] . "</td>";
                echo "<td>" . htmlspecialchars($row['nama_pembeli']) . "</td>";
                echo "<td>" . htmlspecialchars($row['nama_barang']) . "</td>";
                echo "<td>" . $row['jumlah'] . "</td>";
                echo "<td>Rp " . number_format($row['harga_satuan'], 0, ',', '.') . "</td>";
                echo "<td>Rp " . number_format($row['total_harga'], 0, ',', '.') . "</td>";
                echo "<td>" . $row['tanggal'] . "</td>"; 
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='7' class='text-center'>Tidak ada data transaksi.</td></tr>";
        }
        ?>
    </tbody>
</table>

</body>
</html>
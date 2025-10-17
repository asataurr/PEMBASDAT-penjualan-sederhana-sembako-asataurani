<?php
// transaksi_create.php - Tambah transaksi baru (CREATE)

include 'header.php';

$error = '';
$success = '';

// Ambil data Pembeli dan Barang untuk Dropdown
$pembeli_result = $conn->query("SELECT id_pembeli, nama_pembeli FROM pembeli ORDER BY nama_pembeli ASC");
$barang_result = $conn->query("SELECT id_barang, nama_barang, harga, stok FROM barang WHERE stok > 0 ORDER BY nama_barang ASC");

// Simpan data barang dalam array PHP untuk memudahkan JS
$barang_data_js = [];
if ($barang_result->num_rows > 0) {
    while ($row = $barang_result->fetch_assoc()) {
        $barang_data_js[$row['id_barang']] = [
            'harga' => (float)$row['harga'],
            'stok' => (int)$row['stok'],
        ];
    }
    // Reset pointer untuk digunakan di form
    $barang_result->data_seek(0);
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_pembeli = anti_injection($_POST['id_pembeli']);
    $id_barang = anti_injection($_POST['id_barang']);
    $jumlah = anti_injection($_POST['jumlah']);
    $tanggal = date("Y-m-d H:i:s"); // Sesuai tipe DATETIME di database

    if (empty($id_pembeli) || empty($id_barang) || empty($jumlah) || !is_numeric($jumlah) || $jumlah <= 0) {
        $error = "Semua kolom harus diisi dengan benar.";
    } else {
        // 2. Cek Stok Barang dan Harga
        $stmt = $conn->prepare("SELECT harga, stok FROM barang WHERE id_barang = ?");
        $stmt->bind_param("i", $id_barang);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows == 0) {
            $error = "Barang tidak ditemukan.";
        } else {
            $barang_data = $res->fetch_assoc();
            $harga_satuan_barang = $barang_data['harga'];
            $stok_tersedia = $barang_data['stok'];

            if ($jumlah > $stok_tersedia) {
                $error = "Jumlah yang dibeli ($jumlah) melebihi stok yang tersedia ($stok_tersedia).";
            } else {
                // 4. Hitung total_harga
                $total_harga = $harga_satuan_barang * $jumlah;
                $harga_satuan_transaksi = $harga_satuan_barang; 

                // Memulai Transaksi Database (Atomic operation)
                $conn->begin_transaction();
                try {
                    // A. INSERT Transaksi (Termasuk harga_satuan)
                    $sql_transaksi = "INSERT INTO transaksi (id_pembeli, id_barang, jumlah, harga_satuan, total_harga, tanggal) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt_t = $conn->prepare($sql_transaksi);
                    $stmt_t->bind_param("iiidds", $id_pembeli, $id_barang, $jumlah, $harga_satuan_transaksi, $total_harga, $tanggal);
                    $stmt_t->execute();

                    // B. UPDATE Stok Barang (Pengurangan Stok)
                    $sql_update_stok = "UPDATE barang SET stok = stok - ? WHERE id_barang = ?";
                    $stmt_b = $conn->prepare($sql_update_stok);
                    $stmt_b->bind_param("ii", $jumlah, $id_barang);
                    $stmt_b->execute();

                    $conn->commit();
                    $success = "Transaksi berhasil dicatat!";
                    header("Location: transaksi_list.php?status=success");
                    exit();

                } catch (Exception $e) {
                    $conn->rollback();
                    $error = "Transaksi gagal: " . $e->getMessage();
                }
            }
        }
    }
}
?>

<h1 class="mb-4">Catat Transaksi Baru</h1>

<?php if ($error) : ?>
    <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
<?php endif; ?>
<?php if ($success) : ?>
    <div class="alert alert-success" role="alert"><?php echo $success; ?></div>
<?php endif; ?>

<form method="post" action="" class="p-4 border rounded shadow-sm bg-white">
    
    <div class="mb-3">
        <label for="id_pembeli" class="form-label">Pembeli:</label>
        <select id="id_pembeli" name="id_pembeli" class="form-select" required>
            <option value="">-- Pilih Pembeli --</option>
            <?php while ($row = $pembeli_result->fetch_assoc()): ?>
                <option value="<?php echo $row['id_pembeli']; ?>"><?php echo htmlspecialchars($row['nama_pembeli']); ?></option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="mb-3">
        <label for="id_barang" class="form-label">Barang (Stok Tersedia):</label>
        <select id="id_barang" name="id_barang" class="form-select" required>
            <option value="">-- Pilih Barang --</option>
            <?php while ($row = $barang_result->fetch_assoc()): ?>
                <option 
                    value="<?php echo $row['id_barang']; ?>"
                    data-stok="<?php echo $row['stok']; ?>"
                    data-harga="<?php echo $row['harga']; ?>"
                >
                    <?php echo htmlspecialchars($row['nama_barang']); ?> 
                    (Stok: <?php echo $row['stok']; ?> | Rp<?php echo number_format($row['harga'], 0, ',', '.'); ?>)
                </option>
            <?php endwhile; ?>
        </select>
    </div>
    
    <div class="mb-3">
        <label for="jumlah" class="form-label">Jumlah Beli:</label>
        <input type="number" id="jumlah" name="jumlah" class="form-control" min="1" required>
        <div id="stok-warning" class="form-text text-danger d-none">Jumlah melebihi stok yang tersedia!</div>
    </div>
    
    <h3 class="mt-4">Total Harga: <span id="total_harga_display" class="text-primary">Rp 0</span></h3>

    <button type="submit" class="btn btn-primary me-2 mt-3" id="submit-btn">Catat Transaksi</button>
    <a href="transaksi_list.php" class="btn btn-secondary mt-3">Batal</a>
</form>

<script>
// Data barang yang di-pass dari PHP ke JavaScript
const BARANG_DATA = JSON.parse('<?php echo json_encode($barang_data_js); ?>');
document.addEventListener('DOMContentLoaded', function() {
    const barangSelect = document.getElementById('id_barang');
    const jumlahInput = document.getElementById('jumlah');
    const totalHargaDisplay = document.getElementById('total_harga_display');
    const stokWarning = document.getElementById('stok-warning');
    const submitBtn = document.getElementById('submit-btn');

    function updateKalkulasi() {
        const idBarang = barangSelect.value;
        const jumlah = parseInt(jumlahInput.value) || 0;
        let isValid = true;

        stokWarning.classList.add('d-none');
        submitBtn.disabled = true;

        if (idBarang && BARANG_DATA[idBarang]) {
            const data = BARANG_DATA[idBarang];
            const harga = data.harga;
            const stok = data.stok;
            
            // 1. Validasi Stok (Frontend)
            if (jumlah > stok) {
                stokWarning.classList.remove('d-none');
                isValid = false;
            } else if (jumlah > 0) {
                // 2. Kalkulasi Total
                const total = harga * jumlah;
                totalHargaDisplay.textContent = 'Rp ' + total.toLocaleString('id-ID');
                
                if (isValid) {
                    submitBtn.disabled = false;
                }
            } else {
                totalHargaDisplay.textContent = 'Rp 0';
            }
        } else {
            totalHargaDisplay.textContent = 'Rp 0';
        }
    }
    
    barangSelect.addEventListener('change', updateKalkulasi);
    jumlahInput.addEventListener('input', updateKalkulasi);

    // Panggil sekali saat load
    updateKalkulasi();
});
</script>

<?php
include 'footer.php';
?>
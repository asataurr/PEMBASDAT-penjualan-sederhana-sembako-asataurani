<?php
// barang_edit.php - Ubah data barang (UPDATE)

include 'header.php';

$error = '';
$success = '';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: barang_list.php");
    exit();
}

// 1. Ambil data barang yang akan diedit (TERMASUK kolom 'gambar')
$sql_select = "SELECT nama_barang, harga, stok, gambar FROM barang WHERE id_barang = ?";
$stmt_select = $conn->prepare($sql_select);
$stmt_select->bind_param("i", $id);
$stmt_select->execute();
$result = $stmt_select->get_result();

if ($result->num_rows === 0) {
    $error = "Barang tidak ditemukan.";
} else {
    $barang = $result->fetch_assoc();
}

// 2. Proses form saat POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $nama_barang = anti_injection($_POST['nama_barang']);
    $harga = anti_injection($_POST['harga']);
    $stok = anti_injection($_POST['stok']);
    $gambar_lama = anti_injection($_POST['gambar_lama']);
    $gambar_baru = $gambar_lama; // Default: pertahankan gambar lama

    // 🚨 BARIS BARU: Konversi nama barang menjadi huruf kapital
    $nama_barang = strtoupper($nama_barang); 
    
    // --- Logika Penanganan Unggah Gambar Baru ---
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "assets/img/";
        $ekstensi = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $gambar_name = uniqid('barang_') . '.' . $ekstensi;
        $target_file = $target_dir . $gambar_name;

        // Validasi
        if ($_FILES["gambar"]["size"] > 5000000) {
            $error = "Maaf, ukuran file terlalu besar (Maks 5MB).";
        } elseif (!in_array($ekstensi, ['jpg', 'png', 'jpeg', 'gif'])) {
            $error = "Maaf, hanya file JPG, JPEG, PNG & GIF yang diizinkan.";
        } else {
            // Coba pindahkan file yang diunggah
            if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
                $gambar_baru = $gambar_name; // Simpan nama file baru
                
                // HAPUS gambar lama dari server jika ada
                if ($gambar_lama && file_exists($target_dir . $gambar_lama)) {
                    unlink($target_dir . $gambar_lama);
                }
            } else {
                $error = "Error saat mengunggah file baru.";
            }
        }
    }
    // --- Akhir Logika Unggah Gambar ---

    if ($error) {
        // Jika ada error upload, biarkan form tetap tampil dengan pesan error
    } elseif (empty($nama_barang) || empty($harga) || !is_numeric($harga) || empty($stok) || !is_numeric($stok)) {
        $error = "Semua kolom harus diisi dengan benar.";
    } else {
        // Siapkan update SQL (TAMBAH kolom gambar)
        $sql_update = "UPDATE barang SET nama_barang = ?, harga = ?, stok = ?, gambar = ? WHERE id_barang = ?";
        $stmt_update = $conn->prepare($sql_update);
        // Tipe parameter: s(nama), d(harga), i(stok), s(gambar), i(id)
        $stmt_update->bind_param("sdisi", $nama_barang, $harga, $stok, $gambar_baru, $id);

        if ($stmt_update->execute()) {
            $success = "Data barang berhasil diubah!";
            header("Location: barang_list.php?status=updated");
            exit();
        } else {
            $error = "Error: " . $stmt_update->error;
        }
        $stmt_update->close();
    }
}
?>

<h1 class="mb-4">Edit Barang</h1>

<?php if ($error) : ?>
    <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
<?php endif; ?>

<?php if (isset($barang)) : ?>
<form method="post" action="" class="p-4 border rounded shadow-sm bg-white" enctype="multipart/form-data">
    <input type="hidden" name="update" value="1">
    <input type="hidden" name="gambar_lama" value="<?= htmlspecialchars($barang['gambar'] ?? '') ?>">
    
    <div class="mb-3">
        <label for="nama_barang" class="form-label">Nama Barang:</label>
        <input type="text" id="nama_barang" name="nama_barang" class="form-control" value="<?= htmlspecialchars($barang['nama_barang']) ?>" required>
    </div>

    <div class="mb-3">
        <label for="harga" class="form-label">Harga (Rp):</label>
        <input type="number" id="harga" name="harga" class="form-control" min="0" step="0.01" value="<?= htmlspecialchars($barang['harga']) ?>" required>
    </div>

    <div class="mb-3">
        <label for="stok" class="form-label">Stok:</label>
        <input type="number" id="stok" name="stok" class="form-control" min="0" value="<?= htmlspecialchars($barang['stok']) ?>" required>
    </div>
    
    <div class="mb-3 border p-3 rounded">
        <label for="gambar" class="form-label fw-bold">Ganti Gambar Barang:</label>
        <?php if (!empty($barang['gambar'])): ?>
            <div class="mb-2">
                <p class="mb-1">Gambar Saat Ini:</p>
                <img src="assets/img/<?= htmlspecialchars($barang['gambar']) ?>" style="width: 100px; height: 100px; object-fit: cover;" class="img-thumbnail">
            </div>
        <?php else: ?>
            <div class="mb-2 text-muted">Belum ada gambar yang diunggah.</div>
        <?php endif; ?>
        
        <input type="file" id="gambar" name="gambar" class="form-control mt-2">
        <small class="form-text text-muted">Kosongkan kolom ini jika tidak ingin mengganti gambar.</small>
    </div>
    
    <button type="submit" class="btn btn-primary me-2">Simpan Perubahan</button>
    <a href="barang_list.php" class="btn btn-secondary">Batal</a>
</form>
<?php endif; ?>

<?php
include 'footer.php';
?>
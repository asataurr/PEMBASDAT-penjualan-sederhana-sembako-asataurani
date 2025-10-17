<?php
// barang_create.php - Tambah barang baru (CREATE)

include 'header.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_barang = anti_injection($_POST['nama_barang']);
    $harga = anti_injection($_POST['harga']);
    $stok = anti_injection($_POST['stok']);

    // TAMBAHAN BARU: Konversi nama barang menjadi huruf kapital
    $nama_barang = strtoupper($nama_barang); 

    // --- File Upload Logic ---
    $gambar = null;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "assets/img/";
        
        // Ganti nama file agar unik (lebih aman dari basename)
        $ekstensi = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $gambar_name = uniqid('barang_') . '.' . $ekstensi;
        $target_file = $target_dir . $gambar_name;
        
        // Validation for file type and size
        if ($_FILES["gambar"]["size"] > 5000000) { // 5MB limit
            $error = "Maaf, ukuran file terlalu besar (Maks 5MB).";
        } elseif (!in_array($ekstensi, ['jpg', 'png', 'jpeg', 'gif'])) {
            $error = "Maaf, hanya file JPG, JPEG, PNG & GIF yang diizinkan.";
        } else {
            // Attempt to move the uploaded file
            if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
                $gambar = $gambar_name;
            } else {
                $error = "Maaf, terjadi kesalahan saat mengunggah file Anda.";
            }
        }
    }

    if ($error) {
        // Tampilkan error upload
    } elseif (empty($nama_barang) || empty($harga) || !is_numeric($harga) || empty($stok) || !is_numeric($stok)) {
        $error = "Semua kolom harus diisi dengan benar.";
    } elseif (is_null($gambar)) {
        $error = "Gambar barang wajib diunggah.";
    } else {
        // Prepare the SQL statement to include the 'gambar' column
        $sql = "INSERT INTO barang (nama_barang, harga, stok, gambar) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdis", $nama_barang, $harga, $stok, $gambar);

        if ($stmt->execute()) {
            $success = "Barang berhasil ditambahkan!";
            header("Location: barang_list.php?status=created");
            exit();
        } else {
            $error = "Error database: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<h1 class="mb-4">Tambah Barang Baru</h1>

<?php if ($error) : ?>
    <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
<?php endif; ?>
<?php if ($success) : ?>
    <div class="alert alert-success" role="alert"><?php echo $success; ?></div>
<?php endif; ?>

<form method="post" action="" class="p-4 border rounded shadow-sm bg-white" enctype="multipart/form-data">
    <div class="mb-3">
        <label for="nama_barang" class="form-label">Nama Barang:</label>
        <input type="text" id="nama_barang" name="nama_barang" class="form-control" required>
    </div>

    <div class="mb-3">
        <label for="harga" class="form-label">Harga (Rp):</label>
        <input type="number" id="harga" name="harga" class="form-control" min="0" step="0.01" required>
    </div>

    <div class="mb-3">
        <label for="stok" class="form-label">Stok:</label>
        <input type="number" id="stok" name="stok" class="form-control" min="0" required>
    </div>

    <div class="mb-3">
        <label for="gambar" class="form-label">Gambar Barang:</label>
        <input type="file" id="gambar" name="gambar" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary me-2">Simpan</button>
    <a href="barang_list.php" class="btn btn-secondary">Batal</a>
</form>

<?php
include 'footer.php';
?>
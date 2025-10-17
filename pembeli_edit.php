<?php
// pembeli_edit.php - Ubah data pembeli (UPDATE)

include 'header.php';

$error = '';
$success = '';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: pembeli_list.php");
    exit();
}

// 1. Ambil data pembeli yang akan diedit
$sql_select = "SELECT nama_pembeli, alamat FROM pembeli WHERE id_pembeli = ?";
$stmt_select = $conn->prepare($sql_select);
$stmt_select->bind_param("i", $id);
$stmt_select->execute();
$result = $stmt_select->get_result();

if ($result->num_rows === 0) {
    $error = "Pembeli tidak ditemukan.";
} else {
    $pembeli = $result->fetch_assoc();
}

// 2. Proses form saat POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $nama_pembeli = anti_injection($_POST['nama_pembeli']);
    $alamat = anti_injection($_POST['alamat']);

    if (empty($nama_pembeli) || empty($alamat)) {
        $error = "Nama dan alamat harus diisi.";
    } else {
        $sql_update = "UPDATE pembeli SET nama_pembeli = ?, alamat = ? WHERE id_pembeli = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ssi", $nama_pembeli, $alamat, $id);

        if ($stmt_update->execute()) {
            $success = "Data pembeli berhasil diubah!";
            header("Location: pembeli_list.php?status=updated");
            exit();
        } else {
            $error = "Error: " . $stmt_update->error;
        }
        $stmt_update->close();
    }
}
?>

<h1 class="mb-4">Edit Pembeli</h1>

<?php if ($error) : ?>
    <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
<?php endif; ?>

<?php if (isset($pembeli)) : ?>
<form method="post" action="" class="p-4 border rounded shadow-sm bg-white">
    <input type="hidden" name="update" value="1">
    
    <div class="mb-3">
        <label for="nama_pembeli" class="form-label">Nama Lengkap Pembeli:</label>
        <input type="text" id="nama_pembeli" name="nama_pembeli" class="form-control" 
               value="<?= htmlspecialchars($pembeli['nama_pembeli']) ?>" required>
    </div>

    <div class="mb-3">
        <label for="alamat" class="form-label">Alamat:</label>
        <textarea id="alamat" name="alamat" class="form-control" rows="3" required><?= htmlspecialchars($pembeli['alamat']) ?></textarea>
    </div>

    <button type="submit" class="btn btn-primary me-2">Simpan Perubahan</button>
    <a href="pembeli_list.php" class="btn btn-secondary">Batal</a>
</form>
<?php endif; ?>

<?php
include 'footer.php';
?>
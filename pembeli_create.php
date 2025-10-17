<?php
// pembeli_create.php - Tambah pembeli baru (CREATE)
include 'header.php'; // Memanggil header.php

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_pembeli = anti_injection($_POST['nama_pembeli']);
    $alamat = anti_injection($_POST['alamat']);

    if (empty($nama_pembeli) || empty($alamat)) {
        $error = "Nama dan alamat harus diisi.";
    } else {
        $sql = "INSERT INTO pembeli (nama_pembeli, alamat) VALUES ('$nama_pembeli', '$alamat')";

        if ($conn->query($sql) === TRUE) {
            header("Location: pembeli_list.php?status=created");
            exit();
        } else {
            $error = "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}
?>

<h1 class="mb-4">Tambah Pembeli Baru</h1>

<?php if ($error) : ?>
    <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
<?php endif; ?>

<form method="post" action="" class="p-4 border rounded shadow-sm bg-white">
    
    <div class="mb-3">
        <label for="nama_pembeli" class="form-label">Nama Lengkap Pembeli:</label>
        <input type="text" id="nama_pembeli" name="nama_pembeli" class="form-control" required>
    </div>

    <div class="mb-3">
        <label for="alamat" class="form-label">Alamat:</label>
        <textarea id="alamat" name="alamat" class="form-control" rows="3" required></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="pembeli_list.php" class="btn btn-secondary">Batal</a>
</form>

<?php
include 'footer.php'; // Memanggil footer.php
?>
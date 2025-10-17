<?php
// pembeli_delete.php - Hapus pembeli (DELETE)

// Panggil header untuk memulai koneksi dan kerangka tampilan jika terjadi error
include 'header.php'; 

$id_pembeli = isset($_GET['id']) ? anti_injection($_GET['id']) : null;
$error = '';

if ($id_pembeli && is_numeric($id_pembeli)) {
    // 1. Gunakan Prepared Statement untuk keamanan (menggantikan penggunaan string langsung)
    $sql = "DELETE FROM pembeli WHERE id_pembeli = ?";
    $stmt = $conn->prepare($sql);
    
    // Bind parameter 'i' untuk integer
    $stmt->bind_param("i", $id_pembeli);

    // Karena di tabel transaksi kita menggunakan ON DELETE CASCADE, 
    // semua transaksi pembeli ini akan otomatis terhapus.
    if ($stmt->execute()) {
        // Sukses: Langsung redirect
        header("Location: pembeli_list.php?status=deleted");
        exit();
    } else {
        // Gagal: Simpan pesan error
        $error = "Error menghapus data: " . $stmt->error;
    }
    
    $stmt->close();

} else {
    // Jika tidak ada ID, langsung redirect
    header("Location: pembeli_list.php");
    exit();
}

// Hanya tampilkan jika ada error yang tersimpan
if ($error) {
    echo "<h1 class='mb-4 text-danger'>Kegagalan Operasi</h1>";
    echo "<div class='alert alert-danger' role='alert'>" . $error . "</div>";
    echo "<a href='pembeli_list.php' class='btn btn-secondary'>Kembali ke Daftar Pembeli</a>";
}

include 'footer.php';
?>
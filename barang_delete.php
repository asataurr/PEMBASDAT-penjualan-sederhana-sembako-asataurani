<?php
// barang_delete.php - Hapus barang (DELETE)

// Panggil header untuk memulai koneksi dan kerangka tampilan jika terjadi error
include 'header.php'; 

$id_barang = isset($_GET['id']) ? anti_injection($_GET['id']) : null;
$error = '';

if ($id_barang && is_numeric($id_barang)) {
    // Gunakan Prepared Statement untuk keamanan
    $sql = "DELETE FROM barang WHERE id_barang = ?";
    $stmt = $conn->prepare($sql);
    
    // Bind parameter 'i' untuk integer
    $stmt->bind_param("i", $id_barang);

    if ($stmt->execute()) {
        // Sukses: Langsung redirect
        header("Location: barang_list.php?status=deleted");
        exit();
    } else {
        // Gagal: Simpan pesan error
        $errorMessage = $stmt->error;
        
        // Cek apakah error disebabkan oleh Foreign Key Constraint (FK)
        // Nomor error MySQL untuk FK Constraint biasanya 1451
        if (strpos($errorMessage, 'a foreign key constraint fails') !== false || strpos($errorMessage, '1451') !== false) {
            $error = "Gagal menghapus barang. Barang ini sudah pernah dicatat dalam transaksi dan tidak dapat dihapus sebelum transaksi terkait dihapus.";
        } else {
            $error = "Error menghapus data: " . $errorMessage;
        }
    }
    
    $stmt->close();

} else {
    // Jika tidak ada ID, langsung redirect
    header("Location: barang_list.php");
    exit();
}

// Hanya tampilkan jika ada error yang tersimpan ($error terisi)
if ($error) {
    echo "<h1 class='mb-4 text-danger'>Kegagalan Operasi</h1>";
    echo "<div class='alert alert-danger' role='alert'>" . $error . "</div>";
    echo "<a href='barang_list.php' class='btn btn-secondary'>Kembali ke Daftar Barang</a>";
}

// Tutup HTML dan koneksi
include 'footer.php';
?>
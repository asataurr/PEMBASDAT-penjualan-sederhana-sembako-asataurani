<?php
// transaksi_delete.php - Hapus transaksi (DELETE)

include 'db.php';

$id_transaksi = isset($_GET['id']) ? anti_injection($_GET['id']) : null;

if ($id_transaksi && is_numeric($id_transaksi)) {
    // 1. Ambil data transaksi yang akan dihapus (untuk mendapatkan ID barang & jumlah)
    $sql_get_data = "SELECT id_barang, jumlah FROM transaksi WHERE id_transaksi='$id_transaksi'";
    $result = $conn->query($sql_get_data);

    if ($result->num_rows == 1) {
        $data = $result->fetch_assoc();
        $id_barang = $data['id_barang'];
        $jumlah_dikembalikan = $data['jumlah'];

        // Memulai Transaksi Database (agar penghapusan dan pengembalian stok pasti berhasil/gagal bersamaan)
        $conn->begin_transaction();
        try {
            // A. Hapus Transaksi
            $sql_delete = "DELETE FROM transaksi WHERE id_transaksi='$id_transaksi'";
            $conn->query($sql_delete);

            // B. Kembalikan Stok Barang (Stok = Stok + Jumlah)
            $sql_update_stok = "UPDATE barang SET stok = stok + $jumlah_dikembalikan WHERE id_barang = '$id_barang'";
            $conn->query($sql_update_stok);

            $conn->commit();
            header("Location: transaksi_list.php?status=deleted");
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            echo "Error menghapus data dan mengembalikan stok: " . $e->getMessage();
        }

    } else {
        echo "Transaksi tidak ditemukan.";
    }
} else {
    header("Location: transaksi_list.php");
    exit();
}

$conn->close();
?>
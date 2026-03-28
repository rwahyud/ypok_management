<?php
require_once __DIR__ . '/../config/database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$id = $_GET['id'] ?? 0;

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jenis_dokumen = $_POST['jenis_dokumen'];
    $nomor_dokumen = $_POST['nomor_dokumen'];
    $tanggal_terbit = $_POST['tanggal_terbit'];
    $tanggal_kadaluarsa = $_POST['tanggal_kadaluarsa'];
    $instansi_penerbit = $_POST['instansi_penerbit'];
    $status = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE legalitas SET jenis_dokumen=?, nomor_dokumen=?, tanggal_terbit=?, tanggal_kadaluarsa=?, instansi_penerbit=?, status=? WHERE id=?");
    $stmt->execute([$jenis_dokumen, $nomor_dokumen, $tanggal_terbit, $tanggal_kadaluarsa, $instansi_penerbit, $status, $id]);
    
    header('Location: legalitas.php?updated=1');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM legalitas WHERE id = ?");
$stmt->execute([$id]);
$dokumen = $stmt->fetch();

if(!$dokumen) {
    header('Location: legalitas.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Dokumen - YPOK Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../components/navbar.php'; ?>
    
    <div class="main-content">
        <div class="top-bar">
            <h2 class="page-title">Edit Dokumen</h2>
            <div class="user-info">
                <span class="icon">👤</span>
                <span><?php echo $_SESSION['nama_lengkap']; ?></span>
            </div>
        </div>
        
        <div class="container">
            <div class="form-container">
                <form action="" method="POST">
                    <div class="form-group">
                        <label>Jenis Dokumen *</label>
                        <input type="text" name="jenis_dokumen" value="<?php echo htmlspecialchars($dokumen['jenis_dokumen']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Nomor Dokumen *</label>
                        <input type="text" name="nomor_dokumen" value="<?php echo htmlspecialchars($dokumen['nomor_dokumen']); ?>" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Tanggal Terbit *</label>
                            <input type="date" name="tanggal_terbit" value="<?php echo $dokumen['tanggal_terbit']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Tanggal Kadaluarsa *</label>
                            <input type="date" name="tanggal_kadaluarsa" value="<?php echo $dokumen['tanggal_kadaluarsa']; ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Instansi Penerbit *</label>
                        <input type="text" name="instansi_penerbit" value="<?php echo htmlspecialchars($dokumen['instansi_penerbit']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Status *</label>
                        <select name="status" required>
                            <option value="Aktif" <?php echo $dokumen['status'] == 'Aktif' ? 'selected' : ''; ?>>Aktif</option>
                            <option value="Akan Kadaluarsa" <?php echo $dokumen['status'] == 'Akan Kadaluarsa' ? 'selected' : ''; ?>>Akan Kadaluarsa</option>
                            <option value="Dalam Proses" <?php echo $dokumen['status'] == 'Dalam Proses' ? 'selected' : ''; ?>>Dalam Proses</option>
                            <option value="Kadaluarsa" <?php echo $dokumen['status'] == 'Kadaluarsa' ? 'selected' : ''; ?>>Kadaluarsa</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <a href="legalitas.php" class="btn-secondary">Batal</a>
                        <button type="submit" class="btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

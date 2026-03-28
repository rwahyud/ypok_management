<?php
require_once '../config/database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$id = $_GET['id'] ?? 0;

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $jabatan = $_POST['jabatan'];
    $periode = $_POST['periode'];
    $email = $_POST['email'] ?? null;
    $telepon = $_POST['telepon'] ?? null;
    $status = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE pengurus SET nama=?, jabatan=?, periode=?, email=?, telepon=?, status=? WHERE id=?");
    $stmt->execute([$nama, $jabatan, $periode, $email, $telepon, $status, $id]);
    
    header('Location: legalitas.php?updated=1');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM pengurus WHERE id = ?");
$stmt->execute([$id]);
$pengurus = $stmt->fetch();

if(!$pengurus) {
    header('Location: legalitas.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pengurus - YPOK Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../components/navbar.php'; ?>
    
    <div class="main-content">
        <div class="top-bar">
            <h2 class="page-title">Edit Pengurus</h2>
            <div class="user-info">
                <span class="icon">👤</span>
                <span><?php echo $_SESSION['nama_lengkap']; ?></span>
            </div>
        </div>
        
        <div class="container">
            <div class="form-container">
                <form action="" method="POST">
                    <div class="form-group">
                        <label>Nama Lengkap *</label>
                        <input type="text" name="nama" value="<?php echo htmlspecialchars($pengurus['nama']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Jabatan *</label>
                        <input type="text" name="jabatan" value="<?php echo htmlspecialchars($pengurus['jabatan']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Periode *</label>
                        <input type="text" name="periode" value="<?php echo htmlspecialchars($pengurus['periode']); ?>" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($pengurus['email']); ?>">
                        </div>
                        <div class="form-group">
                            <label>Telepon</label>
                            <input type="text" name="telepon" value="<?php echo htmlspecialchars($pengurus['telepon']); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Status *</label>
                        <select name="status" required>
                            <option value="Aktif" <?php echo $pengurus['status'] == 'Aktif' ? 'selected' : ''; ?>>Aktif</option>
                            <option value="Tidak Aktif" <?php echo $pengurus['status'] == 'Tidak Aktif' ? 'selected' : ''; ?>>Tidak Aktif</option>
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

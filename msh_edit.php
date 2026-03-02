<?php
require_once 'config/supabase.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM majelis_sabuk_hitam WHERE id = ?");
$stmt->execute([$id]);
$msh = $stmt->fetch();

if(!$msh) {
    header('Location: msh.php');
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode_msh = $_POST['kode_msh'];
    $nama = $_POST['nama'];
    $tingkat_dan = $_POST['tingkat_dan'];
    $no_telp = $_POST['no_telp'];
    $dojo_cabang = $_POST['dojo_cabang'];
    $status = $_POST['status'];
    $alamat = $_POST['alamat'];
    
    $stmt = $pdo->prepare("UPDATE majelis_sabuk_hitam SET nama = ?, kode_msh = ?, tingkat_dan = ?, no_telp = ?, dojo_cabang = ?, status = ?, alamat = ? WHERE id = ?");
    
    if($stmt->execute([$nama, $kode_msh, $tingkat_dan, $no_telp, $dojo_cabang, $status, $alamat, $id])) {
        header('Location: msh.php?updated=1');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data MSH - YPOK Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    
    <div class="main-content">
        <div class="top-bar">
            <h2 class="page-title">Edit Data MSH</h2>
            <div class="user-info">
                <span class="icon">👤</span>
                <span><?php echo $_SESSION['nama_lengkap']; ?></span>
            </div>
        </div>
        
        <div class="container">
            <div class="content-header">
                <h1>✏️ Edit Data MSH</h1>
                <button class="btn-primary" onclick="window.location.href='msh.php'">
                    ← Kembali
                </button>
            </div>
            
            <div class="table-container" style="padding: 30px;">
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label>No. MSH <span style="color: red;">*</span></label>
                            <input type="text" name="kode_msh" value="<?php echo htmlspecialchars($msh['kode_msh']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Nama Lengkap <span style="color: red;">*</span></label>
                            <input type="text" name="nama" value="<?php echo htmlspecialchars($msh['nama']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Tingkat Dan <span style="color: red;">*</span></label>
                            <select name="tingkat_dan" required>
                                <option value="Dan 1" <?php echo $msh['tingkat_dan'] == 'Dan 1' ? 'selected' : ''; ?>>Dan 1</option>
                                <option value="Dan 2" <?php echo $msh['tingkat_dan'] == 'Dan 2' ? 'selected' : ''; ?>>Dan 2</option>
                                <option value="Dan 3" <?php echo $msh['tingkat_dan'] == 'Dan 3' ? 'selected' : ''; ?>>Dan 3</option>
                                <option value="Dan 4" <?php echo $msh['tingkat_dan'] == 'Dan 4' ? 'selected' : ''; ?>>Dan 4</option>
                                <option value="Dan 5" <?php echo $msh['tingkat_dan'] == 'Dan 5' ? 'selected' : ''; ?>>Dan 5</option>
                                <option value="Dan 6" <?php echo $msh['tingkat_dan'] == 'Dan 6' ? 'selected' : ''; ?>>Dan 6</option>
                                <option value="Dan 7" <?php echo $msh['tingkat_dan'] == 'Dan 7' ? 'selected' : ''; ?>>Dan 7</option>
                                <option value="Dan 8" <?php echo $msh['tingkat_dan'] == 'Dan 8' ? 'selected' : ''; ?>>Dan 8</option>
                                <option value="Dan 9" <?php echo $msh['tingkat_dan'] == 'Dan 9' ? 'selected' : ''; ?>>Dan 9</option>
                                <option value="Dan 10" <?php echo $msh['tingkat_dan'] == 'Dan 10' ? 'selected' : ''; ?>>Dan 10</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>No. Telepon <span style="color: red;">*</span></label>
                            <input type="tel" name="no_telp" value="<?php echo htmlspecialchars($msh['no_telp']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Dojo/Cabang <span style="color: red;">*</span></label>
                            <input type="text" name="dojo_cabang" value="<?php echo htmlspecialchars($msh['dojo_cabang']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Status <span style="color: red;">*</span></label>
                            <select name="status" required>
                                <option value="aktif" <?php echo $msh['status'] == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                <option value="non-aktif" <?php echo $msh['status'] == 'non-aktif' ? 'selected' : ''; ?>>Non-Aktif</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row full">
                        <div class="form-group">
                            <label>Alamat Lengkap <span style="color: red;">*</span></label>
                            <textarea name="alamat" rows="3" required><?php echo htmlspecialchars($msh['alamat']); ?></textarea>
                        </div>
                    </div>
                    
                    <div style="margin-top: 30px; display: flex; gap: 10px;">
                        <button type="button" class="btn-cancel" onclick="window.location.href='msh.php'">Batal</button>
                        <button type="submit" class="btn-submit" style="flex: 2;">💾 Update Data MSH</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

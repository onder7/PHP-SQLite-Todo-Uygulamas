<?php
require_once 'DatabaseBackup.php';
$backup = new DatabaseBackup();

$message = '';

if (isset($_POST['backup'])) {
    try {
        $message = $backup->backup();
    } catch (Exception $e) {
        $message = "Hata: " . $e->getMessage();
    }
}

if (isset($_POST['restore'])) {
    try {
        $message = $backup->restore($_POST['file']);
    } catch (Exception $e) {
        $message = "Hata: " . $e->getMessage();
    }
}

$backups = $backup->getBackups();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Veritabanı Yedekleme</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Veritabanı Yedekleme</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-info"><?= $message ?></div>
        <?php endif; ?>

        <div class="mb-4">
            <form method="post">
                <button type="submit" name="backup" class="btn btn-primary">Yeni Yedek Al</button>
            </form>
        </div>

        <h2>Yedekler</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Dosya</th>
                    <th>Tarih</th>
                    <th>Boyut</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($backups as $b): ?>
                <tr>
                    <td><?= htmlspecialchars(basename($b['file'])) ?></td>
                    <td><?= $b['date'] ?></td>
                    <td><?= $b['size'] ?></td>
                    <td>
                        <form method="post" style="display:inline">
                            <input type="hidden" name="file" value="<?= htmlspecialchars($b['file']) ?>">
                            <button type="submit" name="restore" class="btn btn-warning btn-sm" 
                                    onclick="return confirm('Veritabanını bu yedekten geri yüklemek istediğinize emin misiniz?')">
                                Geri Yükle
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <a href="index.php" class="btn btn-secondary">Geri Dön</a>
    </div>
</body>
</html>
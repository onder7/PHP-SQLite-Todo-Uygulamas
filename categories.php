<?php
require_once 'CategoryManager.php';
$categoryManager = new CategoryManager('todo.db');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $categoryManager->add($_POST['name']);
    } elseif (isset($_POST['delete'])) {
        $categoryManager->delete($_POST['id']);
    } elseif (isset($_POST['update'])) {
        $categoryManager->update($_POST['id'], $_POST['name']);
    }
    header('Location: categories.php');
    exit;
}

$categories = $categoryManager->getAll();
$editCategory = isset($_GET['edit']) ? $categoryManager->getById($_GET['edit']) : null;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kategoriler</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Kategoriler</h1>
            <a href="index.php" class="btn btn-secondary">Geri Dön</a>
        </div>
        
        <div class="card mb-4">
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <div class="col-auto">
                        <input type="text" name="name" class="form-control" placeholder="Yeni kategori adı" required>
                    </div>
                    <div class="col-auto">
                        <button type="submit" name="add" class="btn btn-primary">Kategori Ekle</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Kategori Adı</th>
                                <th class="text-end">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?= $category['id'] ?></td>
                                <td><?= htmlspecialchars($category['name']) ?></td>
                                <td class="text-end">
                                    <a href="?edit=<?= $category['id'] ?>" class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil"></i> Düzenle
                                    </a>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="id" value="<?= $category['id'] ?>">
                                        <button type="submit" name="delete" class="btn btn-danger btn-sm" 
                                                onclick="return confirm('<?= htmlspecialchars($category['name']) ?> kategorisini silmek istediğinize emin misiniz?')">
                                            <i class="bi bi-trash"></i> Sil
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php if ($editCategory): ?>
    <div class="modal show" tabindex="-1" style="display: block; background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Kategori Düzenle</h5>
                        <a href="categories.php" class="btn-close"></a>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" value="<?= $editCategory['id'] ?>">
                        <div class="mb-3">
                            <label for="categoryName" class="form-label">Kategori Adı</label>
                            <input type="text" class="form-control" id="categoryName" name="name" 
                                   value="<?= htmlspecialchars($editCategory['name']) ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="categories.php" class="btn btn-secondary">İptal</a>
                        <button type="submit" name="update" class="btn btn-primary">Güncelle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
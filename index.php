<?php
require_once 'TaskManager.php';
require_once 'CategoryManager.php';
require_once 'vendor/tecnickcom/tcpdf/tcpdf.php';

$taskManager = new TaskManager('todo.db');
$categoryManager = new CategoryManager('todo.db');

if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'create':
        case 'update':
            $data = [
                'text' => $_POST['text'],
                'category' => $_POST['category'],
                'priority' => $_POST['priority'],
                'due_date' => $_POST['due_date'],
                'due_time' => $_POST['due_time'],
                'notes' => $_POST['notes'],
                'has_alarm' => isset($_POST['has_alarm']) ? 1 : 0,
            ];
            
            if ($_POST['action'] === 'create') {
                $taskManager->create($data);
            } else {
                $taskManager->update($_POST['task_id'], $data);
            }
            break;
            
        case 'toggle_complete':
            $taskManager->toggleComplete($_POST['task_id']);
            break;
            
        case 'bulk_update':
            if (isset($_POST['selected_tasks']) && isset($_POST['bulk_category'])) {
                $taskManager->bulkUpdateCategory($_POST['selected_tasks'], $_POST['bulk_category']);
            }
            break;
    }
    header('Location: index.php');
    exit;
}

$tasks = $taskManager->getAll();
$categories = $categoryManager->getAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Todo List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .task-actions { white-space: nowrap; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Todo List</h1>
            <div>
                <button type="button" class="btn btn-primary" onclick="openTaskModal()">
                    <i class="bi bi-plus-lg"></i> Yeni Görev
                </button>
                <a href="categories.php" class="btn btn-info">
                    <i class="bi bi-tag"></i> Kategoriler
                </a>
                <a href="backup.php" class="btn btn-secondary">
                    <i class="bi bi-download"></i> Yedekleme
                </a>
                <a href="export.php?type=excel" class="btn btn-success">
                    <i class="bi bi-file-excel"></i> Excel
                </a>
                <a href="export.php?type=pdf" class="btn btn-danger">
                    <i class="bi bi-file-pdf"></i> PDF
                </a>
                <button type="button" class="btn btn-danger" onclick="confirmClear()">
    <i class="bi bi-trash3"></i> Veritabanını Temizle
</button>
            </div>
        </div>

        <form method="POST" id="tasksForm">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="selectAll">
                                <label class="form-check-label" for="selectAll">Tümünü Seç</label>
                            </div>
                        </div>
                        <div class="col-auto">
                            <select name="bulk_category" class="form-select">
                                <option value="">Kategori Seçin</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= htmlspecialchars($category['name']) ?>">
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" name="action" value="bulk_update" class="btn btn-warning" id="bulkUpdateBtn" disabled>
                                <i class="bi bi-pencil"></i> Seçili Görevleri Güncelle
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table id="tasksTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Seç</th>
                            <th>Görev</th>
                            <th>Kategori</th>
                            <th>Öncelik</th>
                            <th>Durum</th>
                            <th>Bitiş Tarihi</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tasks as $task): ?>
                        <tr>
                            <td><?= $task['id'] ?></td>
                            <td>
                                <input type="checkbox" class="form-check-input task-checkbox" 
                                       name="selected_tasks[]" value="<?= $task['id'] ?>">
                            </td>
                            <td><?= htmlspecialchars($task['text']) ?></td>
                            <td><?= htmlspecialchars($task['category']) ?></td>
                            <td>
                                <?php 
                                $priority = [
                                    0 => '<span class="badge bg-secondary">Düşük</span>', 
                                    1 => '<span class="badge bg-primary">Orta</span>', 
                                    2 => '<span class="badge bg-danger">Yüksek</span>'
                                ];
                                echo $priority[$task['priority']] ?? 'Belirsiz';
                                ?>
                            </td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="toggle_complete">
                                    <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                                    <button type="submit" class="btn btn-sm <?= $task['completed'] ? 'btn-success' : 'btn-secondary' ?>">
                                        <?php if ($task['completed']): ?>
                                            <i class="bi bi-check-lg"></i> Tamamlandı
                                        <?php else: ?>
                                            <i class="bi bi-hourglass"></i> Bekliyor
                                        <?php endif; ?>
                                    </button>
                                </form>
                            </td>
                            <td>
                                <?php if ($task['due_date']): ?>
                                    <i class="bi bi-calendar"></i> 
                                    <?= htmlspecialchars($task['due_date']) ?>
                                    <?php if ($task['due_time']): ?>
                                        <i class="bi bi-clock"></i> 
                                        <?= htmlspecialchars($task['due_time']) ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-end task-actions">
                                <button type="button" class="btn btn-sm btn-warning" 
                                        onclick="editTask(<?= htmlspecialchars(json_encode($task)) ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <a href="delete.php?id=<?= $task['id'] ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Bu görevi silmek istediğinize emin misiniz?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>

    <!-- Task Modal -->
    <div class="modal fade" id="taskModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Yeni Görev</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" id="formAction" value="create">
                    <input type="hidden" name="task_id" id="task_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Görev</label>
                            <input type="text" name="text" id="text" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select name="category" id="category" class="form-control">
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= htmlspecialchars($category['name']) ?>">
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Öncelik</label>
                            <select name="priority" id="priority" class="form-control">
                                <option value="0">Düşük</option>
                                <option value="1">Orta</option>
                                <option value="2">Yüksek</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bitiş Tarihi</label>
                            <input type="date" name="due_date" id="due_date" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bitiş Saati</label>
                            <input type="time" name="due_time" id="due_time" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notlar</label>
                            <textarea name="notes" id="notes" class="form-control"></textarea>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" name="has_alarm" id="has_alarm" class="form-check-input" value="1">
                            <label class="form-check-label">Alarm Ekle</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    $(document).ready(function() {
        const taskModal = new bootstrap.Modal(document.getElementById('taskModal'));
        
        $('#tasksTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/tr.json"
            },
            "columnDefs": [
                { "orderable": false, "targets": [1, 7] }
            ],
            "order": [[0, 'desc']]
        });

        const selectAll = document.getElementById('selectAll');
        const taskCheckboxes = document.getElementsByClassName('task-checkbox');
        const bulkUpdateBtn = document.getElementById('bulkUpdateBtn');

        function updateBulkButtonState() {
            const checkedBoxes = document.querySelectorAll('.task-checkbox:checked');
            bulkUpdateBtn.disabled = checkedBoxes.length === 0;
        }

        selectAll.addEventListener('change', function() {
            Array.from(taskCheckboxes).forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkButtonState();
        });

        Array.from(taskCheckboxes).forEach(checkbox => {
            checkbox.addEventListener('change', updateBulkButtonState);
        });
    });

    function openTaskModal() {
        document.getElementById('modalTitle').textContent = 'Yeni Görev';
        document.getElementById('formAction').value = 'create';
        document.getElementById('task_id').value = '';
        document.getElementById('text').value = '';
        document.getElementById('category').value = 'Genel';
        document.getElementById('priority').value = '0';
        document.getElementById('due_date').value = '';
        document.getElementById('due_time').value = '';
        document.getElementById('notes').value = '';
        document.getElementById('has_alarm').checked = false;
        
        new bootstrap.Modal(document.getElementById('taskModal')).show();
    }

    function editTask(task) {
        document.getElementById('modalTitle').textContent = 'Görevi Düzenle';
        document.getElementById('formAction').value = 'update';
        document.getElementById('task_id').value = task.id;
        document.getElementById('text').value = task.text;
        document.getElementById('category').value = task.category;
        document.getElementById('priority').value = task.priority;
        document.getElementById('due_date').value = task.due_date;
        document.getElementById('due_time').value = task.due_time;
        document.getElementById('notes').value = task.notes;
        document.getElementById('has_alarm').checked = task.has_alarm == 1;
        
        new bootstrap.Modal(document.getElementById('taskModal')).show();
    }
    function confirmClear() {
    if (confirm('Veritabanındaki tüm görevler silinecek. Emin misiniz?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'clear_db.php';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'confirm';
        input.value = 'true';
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}

// Temizleme mesajı için
<?php if (isset($_GET['cleared'])): ?>
    alert('Veritabanı başarıyla temizlendi!');
<?php endif; ?>
    </script>
</body>
</html>
<?php
require_once 'TaskManager.php';

$taskManager = new TaskManager('todo.db');

$id = $_GET['id'] ?? null;
if ($id) {
    $taskManager->delete($id);
}

header('Location: index.php');
exit;
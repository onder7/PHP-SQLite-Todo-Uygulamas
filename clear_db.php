<?php
// clear_db.php
class DBCleaner {
    private $db;

    public function __construct($dbPath = 'todo.db') {
        $this->db = new SQLite3($dbPath);
    }

    public function clearAll() {
        $tables = ['tasks', 'categories'];
        foreach ($tables as $table) {
            $this->db->exec("DELETE FROM $table");
            $this->db->exec("DELETE FROM sqlite_sequence WHERE name='$table'");
        }
        
        // Varsayılan kategorileri ekle
        $categories = ['Genel', 'İş', 'Kişisel', 'Alışveriş', 'Eğitim'];
        foreach ($categories as $category) {
            $stmt = $this->db->prepare("INSERT INTO categories (name) VALUES (:name)");
            $stmt->bindValue(':name', $category, SQLITE3_TEXT);
            $stmt->execute();
        }
    }
}

if ($_POST['confirm'] === 'true') {
    $cleaner = new DBCleaner();
    $cleaner->clearAll();
    header('Location: index.php?cleared=1');
    exit;
}
?>
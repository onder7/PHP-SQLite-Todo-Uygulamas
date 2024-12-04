<?php
class CategoryManager {
    private $db;

    public function __construct($dbPath = 'todo.db') {
        $this->db = new SQLite3($dbPath);
        $this->createTable();
    }

    private function createTable() {
        $query = "CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE
        )";
        $this->db->exec($query);
        
        // Default kategorileri ekle
        $defaults = ['Genel', 'İş', 'Kişisel', 'Alışveriş', 'Eğitim'];
        foreach ($defaults as $category) {
            $stmt = $this->db->prepare("INSERT OR IGNORE INTO categories (name) VALUES (:name)");
            $stmt->bindValue(':name', $category, SQLITE3_TEXT);
            $stmt->execute();
        }
    }

    public function getAll() {
        $query = "SELECT * FROM categories ORDER BY name";
        $result = $this->db->query($query);
        $categories = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $categories[] = $row;
        }
        return $categories;
    }

    public function add($name) {
        $stmt = $this->db->prepare("INSERT INTO categories (name) VALUES (:name)");
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM categories WHERE id = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        return $stmt->execute();
    }
    public function update($id, $name) {
        $stmt = $this->db->prepare("UPDATE categories SET name = :name WHERE id = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        return $stmt->execute();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        return $result->fetchArray(SQLITE3_ASSOC);
    }
}
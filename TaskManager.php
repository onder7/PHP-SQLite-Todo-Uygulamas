<?php
class TaskManager {
    private $db;

    public function __construct($dbPath = 'tasks.sqlite') {
        $this->db = new SQLite3($dbPath);
        $this->createTable();
    }

    private function createTable() {
        $query = "CREATE TABLE IF NOT EXISTS tasks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            text TEXT NOT NULL,
            completed BOOLEAN DEFAULT FALSE,
            priority INTEGER DEFAULT 0,
            category TEXT DEFAULT 'Genel',
            notes TEXT,
            due_date TEXT,
            due_time TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            has_alarm BOOLEAN DEFAULT FALSE
        )";
        $this->db->exec($query);
    }

    public function create($data) {
        $query = "INSERT INTO tasks (text, completed, priority, category, notes, due_date, due_time, has_alarm) 
                 VALUES (:text, :completed, :priority, :category, :notes, :due_date, :due_time, :has_alarm)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':text', $data['text'], SQLITE3_TEXT);
        $stmt->bindValue(':completed', $data['completed'] ?? false, SQLITE3_INTEGER);
        $stmt->bindValue(':priority', $data['priority'] ?? 0, SQLITE3_INTEGER);
        $stmt->bindValue(':category', $data['category'] ?? 'Genel', SQLITE3_TEXT);
        $stmt->bindValue(':notes', $data['notes'] ?? null, SQLITE3_TEXT);
        $stmt->bindValue(':due_date', $data['due_date'] ?? null, SQLITE3_TEXT);
        $stmt->bindValue(':due_time', $data['due_time'] ?? null, SQLITE3_TEXT);
        $stmt->bindValue(':has_alarm', $data['has_alarm'] ?? false, SQLITE3_INTEGER);
        
        return $stmt->execute();
    }

    public function getAll($filters = []) {
        $where = [];
        $params = [];
        
        if (isset($filters['completed'])) {
            $where[] = "completed = :completed";
            $params[':completed'] = $filters['completed'];
        }
        if (isset($filters['category'])) {
            $where[] = "category = :category";
            $params[':category'] = $filters['category'];
        }
        if (isset($filters['priority'])) {
            $where[] = "priority = :priority";
            $params[':priority'] = $filters['priority'];
        }

        $whereClause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";
        $query = "SELECT * FROM tasks $whereClause ORDER BY priority DESC, due_date ASC, created_at DESC";
        
        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $result = $stmt->execute();
        $tasks = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $tasks[] = $row;
        }
        return $tasks;
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM tasks WHERE id = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        return $result->fetchArray(SQLITE3_ASSOC);
    }

    public function update($id, $data) {
        $updateFields = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            if ($key !== 'id' && $key !== 'created_at') {
                $updateFields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }

        $query = "UPDATE tasks SET " . implode(", ", $updateFields) . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        
        foreach ($params as $key => $value) {
            $type = is_bool($value) ? SQLITE3_INTEGER : (is_int($value) ? SQLITE3_INTEGER : SQLITE3_TEXT);
            $stmt->bindValue($key, $value, $type);
        }

        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM tasks WHERE id = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        return $stmt->execute();
    }

    public function toggleComplete($id) {
        $stmt = $this->db->prepare("UPDATE tasks SET completed = NOT completed WHERE id = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        return $stmt->execute();
    }

public function bulkUpdateCategory($taskIds, $newCategory) {
    try {
        $placeholders = str_repeat('?,', count($taskIds) - 1) . '?';
        $query = "UPDATE tasks SET category = ? WHERE id IN ($placeholders)";
        
        $stmt = $this->db->prepare($query);
        
        $params = array_merge([$newCategory], $taskIds);
        foreach ($params as $i => $param) {
            $stmt->bindValue($i + 1, $param);
        }
        
        return $stmt->execute();
    } catch (Exception $e) {
        return false;
    }
}
}

// // Usage Example:
// $taskManager = new TaskManager();

// // Create
// $newTask = [
//     'text' => 'Complete project documentation',
//     'priority' => 2,
//     'category' => 'Work',
//     'due_date' => '2024-12-31',
//     'due_time' => '17:00',
//     'has_alarm' => true
// ];
// $taskManager->create($newTask);

// // Read with filters
// $workTasks = $taskManager->getAll(['category' => 'Work', 'completed' => false]);

// // Update
// $taskManager->update(1, ['priority' => 3, 'notes' => 'Updated deadline']);

// // Delete
// $taskManager->delete(1);

// // Toggle completion
// $taskManager->toggleComplete(2);
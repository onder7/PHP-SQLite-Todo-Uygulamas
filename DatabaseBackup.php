<?php
class DatabaseBackup {
    private $dbFile;
    private $backupDir;

    public function __construct($dbFile = 'todo.db', $backupDir = 'backups') {
        $this->dbFile = $dbFile;
        $this->backupDir = $backupDir;
        
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0777, true);
        }
    }

    public function backup() {
        if (!file_exists($this->dbFile)) {
            throw new Exception("Veritabanı dosyası bulunamadı.");
        }

        $timestamp = date('Y-m-d_H-i-s');
        $backupFile = $this->backupDir . '/backup_' . $timestamp . '.db';

        if (copy($this->dbFile, $backupFile)) {
            return "Yedekleme başarılı: " . $backupFile;
        } else {
            throw new Exception("Yedekleme başarısız.");
        }
    }

    public function restore($backupFile) {
        if (!file_exists($backupFile)) {
            throw new Exception("Yedek dosyası bulunamadı.");
        }

        if (copy($backupFile, $this->dbFile)) {
            return "Geri yükleme başarılı.";
        } else {
            throw new Exception("Geri yükleme başarısız.");
        }
    }

    public function getBackups() {
        $backups = glob($this->backupDir . '/backup_*.db');
        return array_map(function($file) {
            return [
                'file' => $file,
                'date' => date('Y-m-d H:i:s', filemtime($file)),
                'size' => round(filesize($file) / 1024, 2) . ' KB'
            ];
        }, $backups);
    }
}

// Kullanım
// try {
//     $backup = new DatabaseBackup();
    
//     // Yedek al
//     // echo $backup->backup();
    
//     // // Yedekleri listele
//     // $backups = $backup->getBackups();
//     // foreach ($backups as $b) {
//     //     echo sprintf(
//     //         "Dosya: %s, Tarih: %s, Boyut: %s\n",
//     //         $b['file'],
//     //         $b['date'],
//     //         $b['size']
//     //     );
//     // }
    
//     // Geri yükle
//     // echo $backup->restore('backups/backup_2024-12-04_10-30-00.db');
    
// } catch (Exception $e) {
//     echo "Hata: " . $e->getMessage();
// }
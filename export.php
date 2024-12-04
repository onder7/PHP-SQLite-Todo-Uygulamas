<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
require_once 'TaskManager.php';

class ExportManager {
    private $db;

    public function __construct($dbPath = 'todo.db') {
        $this->db = new SQLite3($dbPath);
    }

    public function exportToExcel() {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Başlık stili
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getStyle('A1:G1')->getAlignment()->setHorizontal('center');
        
        // Başlıklar
        $headers = ['ID', 'Görev', 'Kategori', 'Öncelik', 'Durum', 'Bitiş Tarihi', 'Notlar'];
        $sheet->fromArray([$headers], NULL, 'A1');
        
        // Sütun genişlikleri
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(30);

        // Veriler
        $query = "SELECT * FROM tasks ORDER BY id DESC";
        $result = $this->db->query($query);
        
        $row = 2;
        while ($data = $result->fetchArray(SQLITE3_ASSOC)) {
            $priority = ['Düşük', 'Orta', 'Yüksek'][$data['priority']] ?? 'Belirsiz';
            $status = $data['completed'] ? 'Tamamlandı' : 'Bekliyor';
            
            $sheet->setCellValue('A'.$row, $data['id']);
            $sheet->setCellValue('B'.$row, $data['text']);
            $sheet->setCellValue('C'.$row, $data['category']);
            $sheet->setCellValue('D'.$row, $priority);
            $sheet->setCellValue('E'.$row, $status);
            $sheet->setCellValue('F'.$row, $data['due_date']);
            $sheet->setCellValue('G'.$row, $data['notes']);
            
            $row++;
        }

        // Excel dosyasını oluştur
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="tasks_'.date('Y-m-d').'.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

// PDF fonksiyonunda
public function exportToPDF() {
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator('Todo App');
 

        // TCPDF konfigürasyonu
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('Todo App');
        $pdf->SetTitle('Görev Listesi');
        
        // Header ve footer'ı kaldır
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Font ayarları
        $pdf->AddPage();
        $pdf->SetFont('dejavusans', 'B', 16);
        
        // Başlık
        $pdf->Cell(0, 10, 'Görev Listesi', 0, 1, 'C');
        $pdf->Ln(10);
        
        // Tablo başlıkları
        $pdf->SetFont('dejavusans', 'B', 10);
        $pdf->Cell(15, 10, 'ID', 1, 0);
        $pdf->Cell(75, 10, 'Görev', 1, 0);
        $pdf->Cell(30, 10, 'Kategori', 1, 0);
        $pdf->Cell(25, 10, 'Öncelik', 1, 0);
        $pdf->Cell(35, 10, 'Durum', 1, 1);
        
        // Veriler
        $query = "SELECT * FROM tasks ORDER BY id DESC";
        $result = $this->db->query($query);
        
        $pdf->SetFont('dejavusans', '', 10);
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $priority = ['Düşük', 'Orta', 'Yüksek'][$row['priority']] ?? 'Belirsiz';
            $status = $row['completed'] ? 'Tamamlandı' : 'Bekliyor';
            
            $pdf->Cell(15, 10, $row['id'], 1, 0);
            $pdf->Cell(75, 10, $this->truncate($row['text'], 40), 1, 0);
            $pdf->Cell(30, 10, $row['category'], 1, 0);
            $pdf->Cell(25, 10, $priority, 1, 0);
            $pdf->Cell(35, 10, $status, 1, 1);
        }
        
        $pdf->Output('tasks_'.date('Y-m-d').'.pdf', 'D');
        exit;
    }

    private function truncate($string, $length) {
        return mb_strlen($string) > $length ? mb_substr($string, 0, $length-3) . '...' : $string;
    }
}

// Export işlemi
if(isset($_GET['type'])) {
    $exporter = new ExportManager();
    
    switch($_GET['type']) {
        case 'excel':
            $exporter->exportToExcel();
            break;
        case 'pdf':
            $exporter->exportToPDF();
            break;
    }
}
?>
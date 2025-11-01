<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// DATABASE CONNECTION
$servername = "localhost";
$username = "root";       
$password = "";           
$dbname = "commute_ease";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT day, location, type, destination, departure_time, estimated_arrival, frequency FROM schedule"; 
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Schedule Report'); 

    if (!empty($results)) {

        $headers = array_keys($results[0]);
        $col = 'A'; // Start at column 'A'
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++; 
        }

        $row = 2; 
        foreach ($results as $dataRow) {
            $col = 'A'; 
            foreach ($dataRow as $cellValue) {
                $sheet->setCellValue($col . $row, $cellValue);
                $col++;
            }
            $row++;
        }

    } else {

        $sheet->setCellValue('A1', 'No data found for this query.');
    }

    $filename = 'schedule-report-' . date('Y-m-d') . '.xlsx';

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Expires: 0');
    header('Pragma: public');

    $writer = new Xlsx($spreadsheet);
    
    $writer->save('php://output');

    exit;

} catch(PDOException $e) {
    echo "Database Error: " . $e->getMessage();
} catch(\Exception $e) {
    echo "General Error: " . $e->getMessage();
}

?>
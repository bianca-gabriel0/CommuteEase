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

    // SQL QUERY
    $sql = "SELECT day, location, type, destination, departure_time, estimated_arrival, frequency FROM schedule"; 
    
    // Prepare and execute the query
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // Fetch all results as an associative array
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // THE SPREADSHEET
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Schedule Report'); 

    // POPULATE THE SPREADSHEET
    if (!empty($results)) {
        // --- ADD THE HEADER ROW ---
        $headers = array_keys($results[0]);
        $col = 'A'; // Start at column 'A'
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++; 
        }

        // --- ADD THE DATA ROWS ---
        $row = 2; // Start from the second row
        foreach ($results as $dataRow) {
            $col = 'A'; // Reset column to 'A' for each new row
            foreach ($dataRow as $cellValue) {
                $sheet->setCellValue($col . $row, $cellValue);
                $col++;
            }
            $row++;
        }

    } else {
        // If no data was found
        $sheet->setCellValue('A1', 'No data found for this query.');
    }

    // 7. SEND FILE TO THE BROWSER.
    
    // Dynamic filename
    $filename = 'schedule-report-' . date('Y-m-d') . '.xlsx'; // (Updated filename)

    // Set the proper HTTP headers
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Expires: 0');
    header('Pragma: public');

    // Create the Xlsx Writer object
    $writer = new Xlsx($spreadsheet);
    
    // 'php://output' is a special "file" that sends its data to the output buffer
    $writer->save('php://output');

    exit;

} catch(PDOException $e) {
    // Handle any database errors
    echo "Database Error: " . $e->getMessage();
} catch(\Exception $e) {
    // Handle any other errors (like from PhpSpreadsheet)
    echo "General Error: " . $e->getMessage();
}

?>
<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ExportImportService
{
    protected $exportPath = 'exports';
    protected $importPath = 'imports';

    public function export(
        string $format,
        Collection $data,
        array $headers,
        string $viewPath = null,
        array $viewData = [],
        string $filename = null
    ): string {
        $filename = $filename ?? 'export-' . time();

        switch (strtolower($format)) {
            case 'pdf':
                return $this->exportToPdf($data, $headers, $viewPath, $viewData, $filename);
            case 'xlsx':
            case 'excel (xlsx)': // Add format variation
                return $this->exportToExcel($data, $headers, $filename, 'xlsx');
            case 'csv':
                return $this->exportToExcel($data, $headers, $filename, 'csv');
            default:
        }
    }

    protected function exportToPdf(Collection $data, array $headers, string $viewPath = null, array $viewData = [], string $filename): string
    {
        // Force download PDF
        $pdf = PDF::loadView('exports.pdf-layout', [
            'data' => $data,
            'headers' => $headers,
            'title' => $viewData['title'] ?? 'Export Data'
        ]);

        // Set paper orientation and size
        $pdf->setPaper('a4', 'landscape');
        
        // Generate a unique filename
        $timestamp = time();
        $filename = "{$filename}-{$timestamp}.pdf";
        
        // Save to storage
        $fullPath = storage_path("app/public/{$this->exportPath}/{$filename}");
        
        // Ensure directory exists
        if (!file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }
        
        // Save the file
        $pdf->save($fullPath);

        // Return the full path
        return Storage::disk('public')->url("{$this->exportPath}/{$filename}");
    }

    protected function exportToExcel(Collection $data, array $headers, string $filename, string $format): string
    {
        $export = new class($data, $headers) implements \Maatwebsite\Excel\Concerns\FromCollection {
            private $data;
            private $headers;

            public function __construct($data, $headers) {
                $this->data = $data;
                $this->headers = $headers;
            }

            public function collection(): \Illuminate\Support\Collection
            {
                // Convert data to array first for debugging
                $dataArray = $this->data->toArray();
                
                // Create array for all rows including header
                $allRows = [];
                
                // Add headers row
                $allRows[] = array_map(function($header) {
                    return $header['label'];
                }, $this->headers);
                
                // Add data rows
                foreach ($dataArray as $item) {
                    $row = [];
                    foreach ($this->headers as $header) {
                        $value = data_get($item, $header['key'], '');
                        if (is_array($value)) {
                            $value = $value['name'] ?? '';
                        }
                        $row[] = strip_tags((string)$value);
                    }
                    $allRows[] = $row;
                }

                return collect($allRows);
            }
        };

        $timestamp = time();
        $filename = "{$filename}-{$timestamp}.{$format}";

        // Store the file using Laravel's storage
        Storage::disk('public')->makeDirectory($this->exportPath);
        Excel::store($export, "{$this->exportPath}/{$filename}", 'public');

        return Storage::disk('public')->url("{$this->exportPath}/{$filename}");
    }

    public function import($file, $columnMap, $rowValidator = null, $rowProcessor = null): array
    {
        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        $rows = [];
        
        try {
            // Handle both uploaded files and file paths
            if ($file instanceof UploadedFile) {
                $filePath = $file->getRealPath();
                $extension = $file->getClientOriginalExtension();
            } else {
                $filePath = $file;
                $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            }
            
            // Validate file extension
            if (!in_array(strtolower($extension), ['csv', 'txt'])) {
                throw new \Exception("Invalid file format. Only CSV files are supported.");
            }
            
            // Read the file and parse it
            $handle = fopen($filePath, 'r');
            
            if (!$handle) {
                throw new \Exception("Could not open file: {$filePath}");
            }
            
            // Get header row first
            $headers = fgetcsv($handle);
            if (!$headers) {
                fclose($handle);
                throw new \Exception('Empty or invalid file. No headers found.');
            }
            
            // Clean up header names (trim whitespace)
            $headers = array_map('trim', $headers);
            
            // Map column indexes to our expected fields
            $columnIndexes = $this->mapColumnsToIndexes($headers, $columnMap);
            
            // Ensure we have mapped at least some columns
            if (empty($columnIndexes)) {
                fclose($handle);
                throw new \Exception('Could not map any columns from the file. Headers found: ' . implode(', ', $headers));
            }
            
            // Process each row
            $rowIndex = 0;
            while (($row = fgetcsv($handle)) !== false) {
                // Skip empty rows
                if (count(array_filter($row)) === 0) {
                    continue;
                }
                
                // Map CSV row to associative array using column map
                $mappedRow = [];
                foreach ($columnMap as $key => $label) {
                    $index = $columnIndexes[$key] ?? null;
                    $mappedRow[$key] = ($index !== null && isset($row[$index])) ? trim($row[$index]) : null;
                }
                
                // Store for debugging
                $rows[] = $mappedRow;
                
                // Validate row if validator provided
                $isValid = true;
                $validationError = null;
                
                if ($rowValidator) {
                    $validationResult = $rowValidator($mappedRow, $rowIndex);
                    
                    if ($validationResult !== true) {
                        $isValid = false;
                        $validationError = is_string($validationResult) ? $validationResult : "Row " . ($rowIndex + 2) . ": Invalid data";
                        $errors[] = $validationError;
                        $errorCount++;
                    }
                }
                
                // Process row if valid and processor provided
                if ($isValid && $rowProcessor) {
                    try {
                        $rowProcessor($mappedRow);
                        $successCount++;
                    } catch (\Exception $e) {
                        $errorCount++;
                        $errors[] = "Row " . ($rowIndex + 2) . ": " . $e->getMessage();
                    }
                } elseif ($isValid) {
                    // Count as success if no processor but valid
                    $successCount++;
                }
                
                $rowIndex++;
            }
            
            fclose($handle);
            
            // If we have no rows processed, but the file isn't empty, something went wrong
            if ($rowIndex > 0 && $successCount == 0 && $errorCount == 0) {
                $errors[] = "No rows were processed. Check that your CSV columns match the expected format.";
            }
            
            return [
                'success' => $successCount > 0,
                'processed' => $successCount,
                'skipped' => $errorCount,
                'errors' => $errors,
                'rows' => $rows,
                'headers' => $headers,
                'columnIndexes' => $columnIndexes
            ];
            
        } catch (\Exception $e) {
            if (isset($handle) && is_resource($handle)) {
                fclose($handle);
            }
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'processed' => 0,
                'skipped' => 0,
                'errors' => [$e->getMessage()],
                'rows' => $rows ?? [],
                'headers' => $headers ?? [],
                'columnIndexes' => $columnIndexes ?? []
            ];
        }
    }

    private function mapColumnsToIndexes(array $headers, array $columnMap): array
    {
        $columnIndexes = [];
        $columnLabels = array_values($columnMap);
        
        foreach ($headers as $index => $header) {
            $header = trim($header);
            
            // Try to find the header in our column map
            $columnKey = array_search($header, $columnMap);
            
            if ($columnKey !== false) {
                $columnIndexes[$columnKey] = $index;
                continue;
            }
            
            // Try to find by position if headers match count
            if (count($headers) === count($columnLabels) && isset($columnLabels[$index])) {
                $key = array_search($columnLabels[$index], $columnMap);
                if ($key !== false) {
                    $columnIndexes[$key] = $index;
                }
            }
        }
        
        return $columnIndexes;
    }
}

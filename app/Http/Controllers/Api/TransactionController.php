<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Transaction::with('category');

        //Filter dari tipe
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        //Filter dari Kurensi
        if ($request->has('currency')) {
            $query->where('currency', $request->currency);
        }

        //Filter dari tanggal
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        //Filter dari Kategori
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')
                            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|in:IDR,USD',
            'type' => 'required|in:income,expense',
            'description' => 'nullable|string',
            'transaction_date' => 'required|date',
        ]);

        $transaction = Transaction::create($validated);
        $transaction->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil ditambahkan',
            'data' => $transaction,
        ],201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction): JsonResponse
    {
        $transaction->load('category');
        return response()->json([
            'success' => true,
            'data' => $transaction,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|in:IDR,USD',
            'type' => 'required|in:income,expense',
            'description' => 'nullable|string',
            'transaction_date' => 'required|date',
        ]);

        $transaction->update($validated);
        $transaction->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil diubah',
            'data' => $transaction,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction): JsonResponse
    {
        $transaction->delete();

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil dihapus',
        ]);
    }

    /**
     * Import transactions from CSV with flexible format support
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'csv_data' => 'required|string',
            'currency' => 'required|in:IDR,USD',
        ]);

        $csvData = $request->csv_data;
        $currency = $request->currency;
        $lines = str_getcsv($csvData, "\n");
        
        if (count($lines) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'CSV file must contain at least a header and one data row',
            ], 422);
        }

        // Parse header and auto-detect column mapping
        $header = str_getcsv($lines[0]);
        $columnMapping = $this->detectColumnMapping($header);
        
        if (!$columnMapping) {
            return response()->json([
                'success' => false,
                'message' => 'Could not detect CSV format. Supported columns: Date/Tanggal, Category/Kategori, Amount/Nominal, Description/Keterangan',
                'received_headers' => $header,
            ], 422);
        }

        $imported = 0;
        $errors = [];
        $categories = \App\Models\Category::pluck('name', 'id')->toArray();
        $categoryMap = array_flip($categories); // name -> id mapping

        // Process data rows
        for ($i = 1; $i < count($lines); $i++) {
            $row = str_getcsv($lines[$i]);
            
            try {
                $parsedData = $this->parseRowData($row, $columnMapping, $header);
                
                // Parse Indonesian date format
                $date = $this->parseIndonesianDate($parsedData['date']);
                if (!$date) {
                    $errors[] = "Row {$i}: Invalid date format '{$parsedData['date']}'";
                    continue;
                }
                
                // Parse Indonesian currency format
                $amount = $this->parseIndonesianCurrency($parsedData['amount']);
                if ($amount === false) {
                    $errors[] = "Row {$i}: Invalid amount format '{$parsedData['amount']}'";
                    continue;
                }
                
                // Determine transaction type and find category
                $type = null;
                
                // First priority: Check if CSV has explicit type column
                if ($parsedData['type']) {
                    $explicitType = strtolower(trim($parsedData['type']));
                    if (in_array($explicitType, ['income', 'pemasukan', 'pendapatan', 'masuk'])) {
                        $type = 'income';
                    } elseif (in_array($explicitType, ['expense', 'pengeluaran', 'keluar', 'belanja'])) {
                        $type = 'expense';
                    }
                }
                
                // Second priority: Determine by category name if type not explicitly set
                if (!$type) {
                    $type = $this->determineTransactionType($parsedData['category'], $parsedData['subcategory'] ?? null);
                }
                
                $categoryName = $parsedData['subcategory'] ?? $parsedData['category'];
                
                // Find or create category if needed
                $categoryId = $this->findOrSuggestCategory($categoryName, $categoryMap, $type);
                if (!$categoryId) {
                    $errors[] = "Row {$i}: Category '{$categoryName}' not found. Please create this category first.";
                    continue;
                }

                Transaction::create([
                    'transaction_date' => $date,
                    'type' => $type,
                    'category_id' => $categoryId,
                    'amount' => $amount,
                    'currency' => $currency,
                    'description' => $parsedData['description'] ?: null,
                ]);
                $imported++;
                
            } catch (\Exception $e) {
                $errors[] = "Row {$i}: {$e->getMessage()}";
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully imported {$imported} transactions",
            'imported' => $imported,
            'errors' => $errors,
            'column_mapping' => $columnMapping,
        ]);
    }
    
    /**
     * Auto-detect column mapping from header
     */
    private function detectColumnMapping($header)
    {
        $mapping = [];
        
        foreach ($header as $index => $columnName) {
            $columnName = trim($columnName);
            
            // Date columns
            if (in_array(strtolower($columnName), ['date', 'tanggal', 'tgl'])) {
                $mapping['date'] = $index;
            }
            // Category columns
            elseif (in_array(strtolower($columnName), ['category', 'kategori', 'kat'])) {
                $mapping['category'] = $index;
            }
            // Sub Category columns
            elseif (in_array(strtolower($columnName), ['sub category', 'sub kategori', 'subcategory', 'subkategori'])) {
                $mapping['subcategory'] = $index;
            }
            // Amount columns
            elseif (in_array(strtolower($columnName), ['amount', 'nominal', 'jumlah', 'nilai'])) {
                $mapping['amount'] = $index;
            }
            // Description columns
            elseif (in_array(strtolower($columnName), ['description', 'keterangan', 'desc', 'ket', 'note', 'catatan'])) {
                $mapping['description'] = $index;
            }
            // Type columns
            elseif (in_array(strtolower($columnName), ['type', 'tipe', 'jenis'])) {
                $mapping['type'] = $index;
            }
        }
        
        // Minimum required: date, category, amount
        if (isset($mapping['date']) && isset($mapping['category']) && isset($mapping['amount'])) {
            return $mapping;
        }
        
        return false;
    }
    
    /**
     * Parse row data based on column mapping
     */
    private function parseRowData($row, $mapping, $header)
    {
        $data = [];
        
        $data['date'] = isset($mapping['date']) ? ($row[$mapping['date']] ?? '') : '';
        $data['category'] = isset($mapping['category']) ? ($row[$mapping['category']] ?? '') : '';
        $data['subcategory'] = isset($mapping['subcategory']) ? ($row[$mapping['subcategory']] ?? null) : null;
        $data['amount'] = isset($mapping['amount']) ? ($row[$mapping['amount']] ?? '') : '';
        $data['description'] = isset($mapping['description']) ? ($row[$mapping['description']] ?? '') : '';
        $data['type'] = isset($mapping['type']) ? ($row[$mapping['type']] ?? null) : null;
        
        return $data;
    }
    
    /**
     * Parse various date formats including Indonesian and international formats
     */
    private function parseIndonesianDate($dateString)
    {
        $dateString = trim($dateString);
        
        // Indonesian and English month mapping
        $monthMap = [
            // Indonesian months
            'jan' => '01', 'januari' => '01',
            'feb' => '02', 'februari' => '02', 
            'mar' => '03', 'maret' => '03',
            'apr' => '04', 'april' => '04',
            'mei' => '05', 'may' => '05',
            'jun' => '06', 'juni' => '06', 'june' => '06',
            'jul' => '07', 'juli' => '07', 'july' => '07',
            'agu' => '08', 'agus' => '08', 'agustus' => '08', 'aug' => '08', 'august' => '08',
            'sep' => '09', 'sept' => '09', 'september' => '09',
            'okt' => '10', 'oktober' => '10', 'oct' => '10', 'october' => '10',
            'nov' => '11', 'november' => '11',
            'des' => '12', 'desember' => '12', 'dec' => '12', 'december' => '12',
        ];
        
        // Pattern 1: DD-MMM-YY (30-Jun-25, 15-Dec-24)
        if (preg_match('/(\d{1,2})[-\/](\w+)[-\/](\d{2})$/', $dateString, $matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $monthStr = strtolower($matches[2]);
            $yearShort = $matches[3];
            
            // Convert 2-digit year to 4-digit (assume 2000+)
            $year = '20' . $yearShort;
            
            if (isset($monthMap[$monthStr])) {
                return $year . '-' . $monthMap[$monthStr] . '-' . $day;
            }
        }
        
        // Pattern 2: DD-MMM-YYYY (30-Jun-2025, 15-Dec-2024)
        if (preg_match('/(\d{1,2})[-\/](\w+)[-\/](\d{4})/', $dateString, $matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $monthStr = strtolower($matches[2]);
            $year = $matches[3];
            
            if (isset($monthMap[$monthStr])) {
                return $year . '-' . $monthMap[$monthStr] . '-' . $day;
            }
        }
        
        // Pattern 3: DD MMM YYYY (31 Jul 2025, 01 Agu 2025)
        if (preg_match('/(\d{1,2})\s+(\w+)\s+(\d{4})/', $dateString, $matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $monthStr = strtolower($matches[2]);
            $year = $matches[3];
            
            if (isset($monthMap[$monthStr])) {
                return $year . '-' . $monthMap[$monthStr] . '-' . $day;
            }
        }
        
        // Pattern 4: MMM DD, YYYY (Jun 30, 2025)
        if (preg_match('/(\w+)\s+(\d{1,2}),?\s+(\d{4})/', $dateString, $matches)) {
            $monthStr = strtolower($matches[1]);
            $day = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $year = $matches[3];
            
            if (isset($monthMap[$monthStr])) {
                return $year . '-' . $monthMap[$monthStr] . '-' . $day;
            }
        }
        
        // Pattern 5: Standard numeric formats
        $standardFormats = [
            'Y-m-d',    // 2025-06-30
            'd/m/Y',    // 30/06/2025
            'm/d/Y',    // 06/30/2025
            'd-m-Y',    // 30-06-2025
            'm-d-Y',    // 06-30-2025
            'Y/m/d',    // 2025/06/30
            'd.m.Y',    // 30.06.2025
            'd/m/y',    // 30/06/25
            'm/d/y',    // 06/30/25
            'd-m-y',    // 30-06-25
            'm-d-y',    // 06-30-25
        ];
        
        foreach ($standardFormats as $format) {
            $parsed = \DateTime::createFromFormat($format, $dateString);
            if ($parsed !== false && $parsed->format($format) === $dateString) {
                // Handle 2-digit years
                $year = (int) $parsed->format('Y');
                if ($year < 100) {
                    $year = $year < 50 ? 2000 + $year : 1900 + $year;
                    $parsed->setDate($year, (int) $parsed->format('m'), (int) $parsed->format('d'));
                }
                return $parsed->format('Y-m-d');
            }
        }
        
        return false;
    }
    
    /**
     * Parse Indonesian currency format (Rp 2.500.000 -> 2500000)
     */
    private function parseIndonesianCurrency($amountString)
    {
        $amountString = trim($amountString);
        
        // Remove currency symbols and spaces
        $cleaned = preg_replace('/[Rp\s\.]/', '', $amountString);
        $cleaned = str_replace(',', '.', $cleaned); // Handle decimal comma
        
        // Check if it's a valid number
        if (is_numeric($cleaned)) {
            return (float) $cleaned;
        }
        
        // Try to extract number from string
        if (preg_match('/(\d[\d\.,]*)/', $amountString, $matches)) {
            $numberStr = str_replace(['.', ','], ['', '.'], $matches[1]);
            if (is_numeric($numberStr)) {
                return (float) $numberStr;
            }
        }
        
        return false;
    }
    
    /**
     * Determine transaction type based on category with expanded keyword detection
     */
    private function determineTransactionType($category, $subcategory = null)
    {
        // Income keywords (Indonesian & English)
        $incomeKeywords = [
            'pemasukan', 'income', 'pendapatan', 'masuk', 'terima',
            'gaji', 'salary', 'upah', 'wage',
            'bonus', 'tunjangan', 'allowance', 'komisi', 'commission',
            'hadiah', 'gift', 'hibah', 'grant', 'donasi', 'donation',
            'penjualan', 'sale', 'jual', 'sell', 'untung', 'profit',
            'bunga', 'interest', 'dividen', 'dividend', 'investasi', 'investment',
            'freelance', 'konsultan', 'consultant', 'royalti', 'royalty'
        ];
        
        // Expense keywords (Indonesian & English) 
        $expenseKeywords = [
            'pengeluaran', 'expense', 'keluar', 'bayar', 'pay', 'beli', 'buy',
            'belanja', 'shopping', 'beli', 'purchase', 'pembelian',
            'makan', 'food', 'makanan', 'minuman', 'drink', 'restaurant', 'restoran',
            'transport', 'transportasi', 'bensin', 'fuel', 'gas', 'ojek', 'taxi', 'grab',
            'listrik', 'electricity', 'air', 'water', 'internet', 'pulsa', 'credit',
            'sewa', 'rent', 'kost', 'boarding', 'rumah', 'house', 'apartment',
            'kesehatan', 'health', 'dokter', 'doctor', 'obat', 'medicine', 'rumah sakit', 'hospital',
            'pendidikan', 'education', 'sekolah', 'school', 'kuliah', 'university', 'kursus', 'course',
            'hiburan', 'entertainment', 'movie', 'bioskop', 'game', 'music', 'streaming',
            'pakaian', 'clothing', 'baju', 'celana', 'sepatu', 'shoes', 'fashion',
            'pajak', 'tax', 'denda', 'fine', 'administrasi', 'admin', 'bank', 'atm',
            'maintenance', 'service', 'perbaikan', 'repair', 'cleaning', 'laundry'
        ];
        
        $searchText = strtolower($category . ' ' . ($subcategory ?? ''));
        
        // Check for income keywords first (more specific)
        foreach ($incomeKeywords as $keyword) {
            if (strpos($searchText, $keyword) !== false) {
                return 'income';
            }
        }
        
        // Check for expense keywords
        foreach ($expenseKeywords as $keyword) {
            if (strpos($searchText, $keyword) !== false) {
                return 'expense';
            }
        }
        
        // If amount is negative, it's likely an expense
        // If amount is positive and no clear indication, default to expense for safety
        return 'expense';
    }
    
    /**
     * Find category or suggest creating it
     */
    private function findOrSuggestCategory($categoryName, $categoryMap, $type)
    {
        // Exact match
        if (isset($categoryMap[$categoryName])) {
            return $categoryMap[$categoryName];
        }
        
        // Try case-insensitive match
        foreach ($categoryMap as $name => $id) {
            if (strtolower($name) === strtolower($categoryName)) {
                return $id;
            }
        }
        
        // Could suggest creating category here, but for now return null
        return null;
    }
}

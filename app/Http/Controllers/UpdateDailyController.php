<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

ini_set('max_execution_time', 100000);

use App\Exports\BomWip;
use App\Exports\Delactual;
use App\Exports\Delsched;
use App\Exports\Delso;
use App\Exports\InventoryFg;
use App\Exports\InventoryMtr;
use App\Exports\LineProduction;
use App\Exports\SapReject;
use App\Imports\BomWipImport;
use App\Imports\DelactualImport;
use App\Imports\DelschedImport;
use App\Imports\DelsoImport;
use App\Imports\InventoryFgImport;
use App\Imports\InventoryMtrImport;
use App\Imports\LineProductionImport;
use App\Imports\SapRejectImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class UpdateDailyController extends Controller
{
    public function index()
    {
        return view('setting.indexupdatedata');
    }

    public function update(Request $request)
    {
        // Separate uploaded files based on selected option
        $selectedOption = $request->input('selected_option');
        $uploadedFiles = $request->file('excel_files');
        if ($selectedOption === 'sap_bom_wip') {
            DB::table('sap_bom_wip')->truncate();
            $excelFileName = $this->processBomWipFiles($uploadedFiles);
            try {
                $this->importBomWipFile($excelFileName);
            } catch (\Throwable $th) {
                // throw $th;"
                return redirect()
                    ->back()
                    ->with(['error' => 'Failed to Import Sap Bom Wip']);
            }

            return Redirect::route('indexupdatepage');
        } elseif ($selectedOption === 'sap_delactual') {
            DB::table('sap_delactual')->truncate();
            $excelFileName = $this->processDelactualFiles($uploadedFiles);
            try {
                $this->importDelactualFile($excelFileName);
            } catch (\Throwable $th) {
                // throw $th;
                return redirect()
                    ->back()
                    ->with(['error' => 'Failed to Import Sap Delactual']);
            }

            return Redirect::route('indexupdatepage');
        } elseif ($selectedOption === 'sap_delsched') {
            $excelFileName = $this->processDelschedFiles($uploadedFiles);
            try {
                $this->importDelschedFile($excelFileName);
            } catch (\Throwable $th) {
                // throw $th;
                return redirect()
                    ->back()
                    ->with(['error' => 'Failed to Import Sap Delsched']);
            }

            return Redirect::route('indexupdatepage');
        } elseif ($selectedOption === 'sap_delso') {
            DB::table('sap_delso')->truncate();
            $excelFileName = $this->processDelsoFiles($uploadedFiles);
            try {
                $this->importDelsoFile($excelFileName);
            } catch (\Throwable $th) {
                // throw $th;
                return redirect()
                    ->back()
                    ->with(['error' => 'Failed to Import Sap Delso']);
            }

            return Redirect::route('indexupdatepage');
        } elseif ($selectedOption === 'sap_inventoryfg') {
            DB::table('sap_inventory_fg')->truncate();
            $excelFileName = $this->processInventoryfgFiles($uploadedFiles);
            try {
                $this->importInventoryfgFile($excelFileName);
            } catch (\Throwable $th) {
                // throw $th;
                return redirect()
                    ->back()
                    ->with(['error' => 'Failed to Import Sap InventoryFg']);
            }

            return Redirect::route('indexupdatepage');
        } elseif ($selectedOption === 'sap_inventorymtr') {
            DB::table('sap_inventory_mtr')->truncate();
            $excelFileName = $this->processInventorymtrFiles($uploadedFiles);
            try {
                $this->importInventoryMtrFile($excelFileName);
            } catch (\Throwable $th) {
                // throw $th;
                return redirect()
                    ->back()
                    ->with(['error' => 'Failed to Import Sap InventoryMtr']);
            }

            return Redirect::route('indexupdatepage');
        } elseif ($selectedOption === 'sap_lineproduction') {
            DB::table('sap_lineproduction')->truncate();
            $excelFileName = $this->processLineproductionFiles($uploadedFiles);
            try {
                $this->importLineProductionFile($excelFileName);
            } catch (\Throwable $th) {
                // throw $th;
                return redirect()
                    ->back()
                    ->with(['error' => 'Failed to Import Sap LineProduction']);
            }

            return Redirect::route('indexupdatepage');
        } elseif ($selectedOption === 'sap_reject') {
            DB::table('sap_reject')->truncate();
            $excelFileName = $this->processSapRejectFiles($uploadedFiles);
            try {
                $this->importSapRejectFile($excelFileName);
            } catch (\Throwable $th) {
                // throw $th;
                return redirect()
                    ->back()
                    ->with(['error' => 'Failed to Import Sap LineProduction']);
            }

            return Redirect::route('indexupdatepage');
        }
    }

    public function processBomWipFiles($files)
    {
        // Initialize an array to store all data
        $allData = [];

        // Iterate through each file
        foreach ($files as $file) {
            // Read the XLS file
            $data = Excel::toArray([], $file);
            // Remove the first row (header)
            array_shift($data[0]);

            // Remove the first column
            foreach ($data[0] as &$row) {
                array_shift($row);
            }

            // Append data from this file to the allData array
            $allData = array_merge($allData, $data[0]);
        }

        try {
            // Convert array data into an Excel file
            $excelFileName = 'databomwip.xlsx';
            $excelFilePath = public_path($excelFileName);

            Excel::store(new BomWip($allData), 'public/AutomateFile/'.$excelFileName);

            // $filePath = Storage::url($fileName);
            return $excelFileName;
        } catch (\Exception $e) {
            // Log or handle the error
            return 'Error: '.$e->getMessage();
        }

        // Return the file name or path
        return $excelFileName;
    }

    public function importBomWipFile($excelFileName)
    {
        try {
            // dd(public_path('/storage/AutomateFile/' . $excelFileName));
            // Import the Excel file using the BomWipImport class
            Excel::import(
                new BomWipImport,
                public_path('/storage/AutomateFile/'.$excelFileName),
            );

            // If the import is successful, return a success message or any other response
            return 'Excel file imported successfully.';
        } catch (\Exception $e) {
            // If an error occurs during the import, log the error or handle it as needed
            return 'Error: '.$e->getMessage();
        }
    }

    private function processDelactualFiles($file)
    {
        try {
            // Get the uploaded file object
            $uploadedFile = $file[0];

            // Get the temporary path of the uploaded file
            $filePath = $uploadedFile->getRealPath();

            // Read the Excel file and process the data
            $data = Excel::toArray([], $filePath)[0];

            // Remove the first row (header)
            array_shift($data);

            // Remove the first column
            foreach ($data as &$row) {
                array_shift($row);
            }

            // Format date cells in column B to yyyy-mm-dd
            foreach ($data as &$row) {
                $row[1] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject(
                    $row[1],
                )->format('Y-m-d');
            }

            // Format number cells in column D to have zero decimal places
            foreach ($data as &$row) {
                $row[3] = number_format($row[3], 0, '.', '');
            }

            $excelFileName = 'delactual.xlsx';
            $excelFilePath = public_path($excelFileName);

            Excel::store(new Delactual($data), 'public/AutomateFile/'.$excelFileName);

            // $filePath = Storage::url($fileName);
            return $excelFileName;

            return 'Excel file processed and imported successfully.';
        } catch (\Exception $e) {
            // Handle any errors that occur during processing or importing
            return 'Error: '.$e->getMessage();
        }
    }

    public function importDelactualFile($excelFileName)
    {
        try {
            // dd(public_path('/storage/AutomateFile/' . $excelFileName));
            // Import the Excel file using the BomWipImport class
            Excel::import(
                new DelactualImport,
                public_path('/storage/AutomateFile/'.$excelFileName),
            );

            // If the import is successful, return a success message or any other response
            return 'Excel file imported successfully.';
        } catch (\Exception $e) {
            // If an error occurs during the import, log the error or handle it as needed
            return 'Error: '.$e->getMessage();
        }
    }

    private function processDelschedFiles($file)
    {
        try {
            // Get the uploaded file object
            $uploadedFile = $file[0];

            // Get the temporary path of the uploaded file
            $filePath = $uploadedFile->getRealPath();

            // Read the Excel file and process the data
            $data = Excel::toArray([], $filePath)[0];

            // Remove the first row (header)
            array_shift($data);

            // Remove the first column
            foreach ($data as &$row) {
                array_shift($row);
            }

            // Format date cells in column B to yyyy-mm-dd
            foreach ($data as &$row) {
                $row[1] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject(
                    $row[1],
                )->format('Y-m-d');
            }

            // Format number cells in column D to have zero decimal places
            foreach ($data as &$row) {
                $row[2] = number_format($row[2], 0, '.', '');
            }

            $excelFileName = 'delsched.csv';
            $excelFilePath = public_path($excelFileName);

            Excel::store(new Delsched($data), 'public/AutomateFile/'.$excelFileName);

            // $filePath = Storage::url($fileName);
            return $excelFileName;
        } catch (\Exception $e) {
            // Handle any errors that occur during processing or importing
            return 'Error: '.$e->getMessage();
        }
    }

    private function importDelschedFile($excelFileName)
    {
        // Get the file path
        $filePath = public_path('storage/AutomateFile/'.$excelFileName);

        // Read the CSV file into an array
        $data = array_map('str_getcsv', file($filePath));

        // Remove the first row (header)
        array_shift($data);

        // Initialize an array to hold the processed data
        $processedData = [];

        $isTableEmpty = DB::table('sap_delsched')->count() === 0;

        // Loop through the rows and process them
        foreach ($data as $row) {
            $itemCode = $row[0]; // Assuming item_code is in column 1
            $deliveryDate = $row[1]; // Assuming delivery_date is in column 2
            $deliveryQty = $row[2]; // Assuming delivery_qty is in column 3
            $soNumber = $row[3]; // Assuming so_number is in column 4, but it can be null

            // Cek apakah data sudah ada
            if ($isTableEmpty) {
                // Jika tabel kosong, langsung masukkan semua
                $insertData[] = [
                    'item_code' => $itemCode,
                    'delivery_date' => $deliveryDate,
                    'delivery_qty' => $deliveryQty,
                    'so_number' => $soNumber,
                ];
            } else {
                // Kalau tidak kosong, cek dulu apakah data sudah ada
                $exists = DB::table('sap_delsched')
                    ->where([
                        ['item_code', '=', $itemCode],
                        ['delivery_date', '=', $deliveryDate],
                        ['delivery_qty', '=', $deliveryQty],
                    ])
                    ->exists();

                if (! $exists) {
                    $insertData[] = [
                        'item_code' => $itemCode,
                        'delivery_date' => $deliveryDate,
                        'delivery_qty' => $deliveryQty,
                        'so_number' => $soNumber,
                    ];
                }
            }

            // // Prepare the data for insertion
            // $processedData[] = [
            //     'item_code' => $itemCode,
            //     'delivery_date' => $deliveryDate,
            //     'delivery_qty' => $deliveryQty,
            //     'so_number' => $soNumber,
            // ];
        }

        // Collect all unique combinations of item_code and delivery_date for fast checking
        // $uniqueCombination = collect($processedData)
        //     ->map(fn($row) => ['item_code' => $row['item_code'], 'delivery_date' => $row['delivery_date']])
        //     ->unique()
        //     ->values()
        //     ->toArray();

        // $filtered = array_filter($uniqueCombination, function ($item) {
        //     return $item['delivery_date'] === '2025-08-25';
        // });

        // // Get the existing records with the same item_code and delivery_date in a batch query
        // $existingRecords = DB::table('sap_delsched')
        //     ->whereIn(DB::raw('CONCAT(item_code, "-", delivery_date)'),
        //         collect($uniqueCombination)->map(fn($item) => $item['item_code'] . '-' . $item['delivery_date'])->toArray())
        //     ->get()
        //     ->keyBy(function ($item) {
        //         return $item->item_code . '-' . $item->delivery_date;
        //     });

        // // Filter out the rows that already exist in the database (using unique combination of item_code + delivery_date)
        // $insertData = [];
        // foreach ($processedData as $row) {
        //     $key = $row['item_code'] . '-' . $row['delivery_date'];

        //     // If this combination does not exist in the existing records, add it to insertData
        //     if (!isset($existingRecords[$key])) {
        //         $insertData[] = $row;
        //     }
        // }

        if (! empty($insertData)) {
            DB::table('sap_delsched')->insert($insertData);
        }

        // Insert all the new records in one batch
        // if (count($insertData) > 0) {
        //     DB::table('sap_delsched')->insert($insertData);
        // }

        return 'Excel file imported successfully.';
    }

    private function processDelsoFiles($file)
    {
        try {
            // Get the uploaded file object
            $uploadedFile = $file[0];

            // Get the temporary path of the uploaded file
            $filePath = $uploadedFile->getRealPath();

            // Read the Excel file and process the data
            $data = Excel::toArray([], $filePath)[0];

            // Remove the first row (header)
            array_shift($data);

            // Remove the first column
            foreach ($data as &$row) {
                array_shift($row);
            }

            // Format number cells in column D to have zero decimal places
            foreach ($data as &$row) {
                $row[3] = number_format($row[3], 0, '.', '');
            }

            foreach ($data as &$row) {
                $row[4] = number_format($row[4], 0, '.', '');
            }

            $excelFileName = 'delso.csv';
            $excelFilePath = public_path($excelFileName);

            Excel::store(new Delso($data), 'public/AutomateFile/'.$excelFileName);

            // $filePath = Storage::url($fileName);
            return $excelFileName;
        } catch (\Exception $e) {
            // Handle any errors that occur during processing or importing
            return 'Error: '.$e->getMessage();
        }
    }

    private function importDelsoFile($excelFileName)
    {
        // Import the Excel file using the DelschedImport class
        Excel::import(new DelsoImport, public_path('storage/AutomateFile/'.$excelFileName));

        // If the import is successful, return a success message
        return 'Excel file imported successfully.';
    }

    private function processInventoryfgFiles($files)
    {
        // Initialize an array to store all data
        $allData = [];

        // Iterate through each file
        foreach ($files as $file) {
            // Read the XLS file
            $data = Excel::toArray([], $file);
            // Remove the first row (header)
            array_shift($data[0]);

            // Remove the first column
            foreach ($data[0] as &$row) {
                array_shift($row);

                // Apply configuration to data in cell F (index 5)
                if (isset($row[5])) {
                    $row[5] = sprintf('%.5f', $row[5]);
                }

                for ($i = 6; $i <= 11; $i++) {
                    if (isset($row[$i])) {
                        // Ensure the value is numeric before applying the formatting
                        if (is_numeric($row[$i])) {
                            $row[$i] = number_format((float) $row[$i], 0, '.', '');
                        }
                    }
                }
            }

            // Append data from this file to the allData array
            $allData = array_merge($allData, $data[0]);
        }

        $excelFileName = 'inventoryfg.xlsx';
        $excelFilePath = public_path($excelFileName);

        Excel::store(new InventoryFg($allData), 'public/AutomateFile/'.$excelFileName);

        // $filePath = Storage::url($fileName);
        return $excelFileName;
    }

    private function importInventoryfgFile($excelFileName)
    {
        // Import the Excel file using the DelschedImport class
        Excel::import(
            new InventoryFgImport,
            public_path('storage/AutomateFile/'.$excelFileName),
        );

        // If the import is successful, return a success message
        return 'Excel file imported successfully.';
    }

    private function processInventorymtrFiles($files)
    {
        // Initialize an array to store all data
        $allData = [];

        // Iterate through each file
        foreach ($files as $file) {
            // Read the XLS file
            $data = Excel::toArray([], $file);
            // Remove the first row (header)
            array_shift($data[0]);

            // Remove the first column
            foreach ($data[0] as &$row) {
                array_shift($row);
            }

            foreach ($data[0] as &$row) {
                $row[3] = number_format($row[3], 5, '.', ''); // Column 3
                $row[4] = number_format($row[4], 5, '.', ''); // Column 4
            }

            // Append data from this file to the allData array
            $allData = array_merge($allData, $data[0]);
        }

        $excelFileName = 'inventorymtr.csv';
        $excelFilePath = public_path($excelFileName);

        Excel::store(new InventoryMtr($allData), 'public/AutomateFile/'.$excelFileName);

        // $filePath = Storage::url($fileName);
        return $excelFileName;
    }

    private function importInventoryMtrFile($excelFileName)
    {
        // Import the Excel file using the DelschedImport class
        Excel::import(
            new InventoryMtrImport,
            public_path('storage/AutomateFile/'.$excelFileName),
        );

        // If the import is successful, return a success message
        return 'Excel file imported successfully.';
    }

    private function processLineproductionFiles($files)
    {
        // Initialize an array to store all data
        $allData = [];

        // Iterate through each file
        foreach ($files as $file) {
            // Read the XLS file
            $data = Excel::toArray([], $file);
            // Remove the first row (header)
            array_shift($data[0]);

            // Remove the first column
            foreach ($data[0] as &$row) {
                array_shift($row);
            }

            // Append data from this file to the allData array
            $allData = array_merge($allData, $data[0]);
        }
        $excelFileName = 'lineproduction.xlsx';
        $excelFilePath = public_path($excelFileName);

        Excel::store(new LineProduction($allData), 'public/AutomateFile/'.$excelFileName);

        // $filePath = Storage::url($fileName);
        return $excelFileName;
    }

    private function importLineProductionFile($excelFileName)
    {
        // Import the Excel file using the DelschedImport class
        Excel::import(
            new LineProductionImport,
            public_path('storage/AutomateFile/'.$excelFileName),
        );

        // If the import is successful, return a success message
        return 'Excel file imported successfully.';
    }

    private function processSapRejectFiles($files)
    {
        // Initialize an array to store all data
        $allData = [];

        // Iterate through each file
        foreach ($files as $file) {
            // Read the XLS file
            $data = Excel::toArray([], $file);
            // Remove the first row (header)
            array_shift($data[0]);

            // Remove the first column
            foreach ($data[0] as &$row) {
                array_shift($row);

                $totalRejectIndex = count($row) - 1;
                if (isset($row[$totalRejectIndex])) {
                    // Ensure the value is numeric before applying the formatting
                    if (is_numeric($row[$totalRejectIndex])) {
                        $row[$totalRejectIndex] = number_format(
                            (float) $row[$totalRejectIndex],
                            0,
                            '.',
                            '',
                        );
                    }
                }
            }

            // Append data from this file to the allData array
            $allData = array_merge($allData, $data[0]);
        }

        $excelFileName = 'sapreject.xlsx';
        $excelFilePath = public_path($excelFileName);

        Excel::store(new SapReject($allData), 'public/AutomateFile/'.$excelFileName);

        // $filePath = Storage::url($fileName);
        return $excelFileName;
    }

    private function importSapRejectFile($excelFileName)
    {
        // Import the Excel file using the DelschedImport class
        Excel::import(new SapRejectImport, public_path('storage/AutomateFile/'.$excelFileName));

        // If the import is successful, return a success message
        return 'Excel file imported successfully.';
    }
}

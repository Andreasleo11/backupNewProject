<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Models\BarcodePackagingMaster;
use App\Models\BarcodePackagingDetail;
use Illuminate\Support\Str; 

use App\Models\MasterDataRogPartName;

class BarcodeController extends Controller
{

    public function index()
    {
        return view('barcodeinandout.index');
    }
    public function indexBarcode()
    {
        $barcodesFolder = public_path('barcodes');
        File::cleanDirectory($barcodesFolder);
        $datas = MasterDataRogPartName::get();
        
        return view('barcodeinandout.indexbarcode', compact('datas'));
    }

    public function missingbarcodeindex()
    {
        $datas = MasterDataRogPartName::get();
        
        return view('barcodeinandout.missingbarcodeindex', compact('datas'));
    }

    public function missingbarcodegenerator(Request $request)
    {
        $partNo = $request->input('partNo');
        $partDetails = explode('/', $partNo);
        $partNumber = $partDetails[0];
        $partName = $partDetails[1] ?? '';

        $barcodesFolder = public_path('barcodes');
        File::cleanDirectory($barcodesFolder);

        // Retrieve and convert missing numbers to an array
        $missingNumbers = explode(',', $request->input('missingnumber'));
    
        // Count the number of missing numbers
        $missingNumbersCount = count($missingNumbers);

        // dd($missingNumbersCount);

        foreach($missingNumbers as $missingNumber)
        {
            
            $barcodeData = $partNumber . "\t" . $missingNumber;

            $barcode = new DNS1D();
    
       
            // Use $spkNumber in the filename
            $filename = $partNumber . '-' . $missingNumber . '.png';


            // Save the barcode as a PNG image inside the barcodes folder
            $barcode->getBarcodePNGPath($barcodeData, 'C128', 2, 70, [0, 0, 0], false, $filename);
            
            // Generate the HTML for the barcode
            $barcodeHtml = $barcode->getBarcodeHTML($barcodeData, 'C128');
            // URL to the saved barcode image
            $barcodeUrl = asset('barcodes/' . $filename);
        //    dd($barcodeUrl);
    
            // Generate the HTML for the barcode
            $barcodeHtml = $barcode->getBarcodeHTML($barcodeData, 'C128', 2, 70);
    
            $barcodes[] = [
                'partno' => $partNumber,
                'partname' => $partName,
                'missingNumber' => $missingNumber,
                'barcodeHtml' => $barcodeHtml,
                'barcodeUrl' => $barcodeUrl,          
            ];
            
        }

        return view('barcodeinandout.missingbarcode', ['barcodes' => $barcodes,'partno' => $partNo]);
        
    }

    public function generateBarcode(Request $request)
    {
        // dd($request->all());
        // $spkNumbers = ['2201222', '2292422', '1299922'];
        // $quantities = ['200', '1200', '100'];
        // $warehouses = ['FG', 'RM', 'RW'];
        
        $partno = $request->partNo;

        $partDetails = explode('/', $partno);
        $partNumber = $partDetails[0];
        $partName = $partDetails[1] ?? '';
        $defaultquantity = 1;
        $defaultwarehouse = "IND";
        

        $startnum = $request->startNumber;
        $quantity = $request->quantity;
        $looping = $quantity - $startnum;
        $barcodes = [];
        
        $barcodesFolder = public_path('barcodes');
        File::cleanDirectory($barcodesFolder);

        for($i = 0; $i <= $looping; $i++)
        {
            
        // Format the data as required ( DI SAP HARUS MENGGUNAKAN TAB )

        // $barcodeData = $partno . "\t" . $startnum . "\t" . $warehouse . "\t" . $incrementNumber;

        $barcodeData = $partNumber . "\t" . $defaultquantity . "\t" . $defaultwarehouse . "\t" . $startnum;
        // Generate the barcode using DNS1D (1D Barcode)
        $barcode = new DNS1D();
    
       
        // Use $spkNumber in the filename
        $filename = preg_replace('/[()#,.\\s&]+(?<!png)/i', '', $partNumber). '-' .$defaultquantity . '-' . $defaultwarehouse . '-' . $startnum . '.png';
        $filename = preg_replace('/"/', '-', $filename);
        $filename = preg_replace('/-+/', '-', $filename);
        
        $lowercaseFilename = strtolower($filename);
        // dd($lowercaseFilename);

            
        if (!File::exists($barcodesFolder)) {
            File::makeDirectory($barcodesFolder, 0755, true); // 0755 is the permission, true for recursive creation
        }
       
        // Save the barcode as a PNG image inside the barcodes folder
        $barcode->getBarcodePNGPath($barcodeData, 'C128', 2, 70, [0, 0, 0], false);


        // Generate the HTML for the barcode
        $barcodeHtml = $barcode->getBarcodeHTML($barcodeData, 'C128');
        // URL to the saved barcode image
        $barcodeUrl = asset('barcodes/' . $lowercaseFilename);
        // Generate the HTML for the barcode
        $barcodeHtml = $barcode->getBarcodeHTML($barcodeData, 'C128', 2, 70);

        $barcodes[] = [
            'partno' => $partNumber,
            'partname' => $partName,
            'quantity' => $quantity,
            'startnum' => $startnum,
            'barcodeHtml' => $barcodeHtml,
            'barcodeUrl' => $barcodeUrl,      
        ];
        
        $startnum += 1;

        }

        return view('barcodeinandout.barcode', ['barcodes' => $barcodes,'partno' => $partno,
            'quantity' => $quantity,
            'startnum' => $startnum,]);
    }
    
    public function inandoutpage()
    {
        $masters = BarcodePackagingMaster::with('detailBarcode')->get();

        // Loop through each master record
        foreach ($masters as $master) {
            // Check if the detailBarcode relationship is empty
            if ($master->detailBarcode->isEmpty()) {
                // Delete the master record if it has no detailBarcode
                $master->delete();
            }
        }

        return view('barcodeinandout.inandoutpage');
    }

    public function processInAndOut(Request $request)
        {
            $barcodePackagingMaster = new BarcodePackagingMaster();
            $tanggalScanFull = Carbon::now('Asia/Bangkok')->format('Y-m-d H:i:s');
            $barcodePackagingMaster->dateScan = $tanggalScanFull;
            $warehouseType = $request->input('warehouseType');
            $location = $request->input('location');
            


            // Define prefix based on warehouse type and location
            switch ($warehouseType) {
                case 'in':
                    $prefix = 'IN';
                    break;
                case 'out':
                    $prefix = 'OUT';
                    break;
                default:
                    $prefix = '';
                    break;
            }

            // Add location suffix based on location
            switch ($location) {
                case 'jakarta':
                    $suffix = 'JKT';
                    break;
                case 'karawang':
                    $suffix = 'KRW';
                    break;
                default:
                    $suffix = '';
                    break;
            }

            // Merge prefix and suffix
            $prefixSuffix = $prefix . '/' . $suffix;

            // Validate and set position based on the merged prefix and suffix
            switch ($prefixSuffix) {
                case 'IN/JKT':
                    $position = 'Jakarta';
                    $HeaderScan = 'IN JAKARTA';
                    break;
                case 'IN/KRW':
                    $position = 'Karawang';
                    $HeaderScan = 'IN KARAWANG';
                    break;
                case 'OUT/JKT':
                    $position = 'CustomerJakarta';
                    $HeaderScan = 'OUT JAKARTA';
                    break;
                case 'OUT/KRW':
                    $position = 'CustomerKarawang';
                    $HeaderScan = 'OUT KARAWANG';
                    break;
                default:
                    $position = 'Unknown'; // Or handle default case as needed
                    break;
            }

            $barcodePackagingMaster->tipeBarcode = $warehouseType;
            $barcodePackagingMaster->location = $location;

            $barcodePackagingMaster->save();

            // Retrieve the id of the newly created entry
            $id = $barcodePackagingMaster->id;
            $currentDate = date('Ymd');

            // Combine prefix, suffix, and random string to form the document number
            $noDokumen = 'PKG'. '/' . $prefixSuffix . '/' . $id . '/' . $currentDate;

            $barcodePackagingMaster->noDokumen = $noDokumen;
            // Output the generated document number for testing purposes
            
            
            
            $barcodePackagingMaster->save();
        
            return view('barcodeinandout.scanpage', compact('noDokumen', 'tanggalScanFull', 'position', 'HeaderScan'));
        }

    public function storeInAndOut(Request $request)
    {
        $data = $request->all();
        // dd($request->all());
        $docnum = $request->noDokumen;

        $master = BarcodePackagingMaster::where('noDokumen', $docnum)->first();

        $idmaster = $master->id;

    
        
        $counter = 1;
        while (isset($data["partno" . $counter])) {
            if($data["label" . $counter] === "ADJUST")
            {
                $data["quantity" . $counter];
            }
            else
            {
                $data["quantity" . $counter] = 1;
            }

            $partNo = $data["partno" . $counter];
            $label = $data["label" . $counter];
    
            // Check for duplicates
            $exists = BarcodePackagingDetail::where('masterId', $idmaster)
                        ->where('partNo', $partNo)
                        ->where('label', $label)
                        ->exists();

            
            if (!$exists) {
                BarcodePackagingDetail::create([
                    'masterId' => $idmaster,
                    'noDokumen' => $data['noDokumen'],
                    'partNo' => $partNo,
                    'quantity' => $data['quantity'. $counter],
                    'label' => $label,
                    'position' => $data['position'],
                    'scantime' => \Carbon\Carbon::parse($data["scantime" . $counter])->format('Y-m-d H:i:s'),
                    ]);
                }
            $counter++;
        }
        return redirect()->route('inandout.index')->with('success', 'Data added successfully');
    }



    public function barcodelist()
    {
        $items = BarcodePackagingMaster::with('detailbarcode')->get();
        
        $result = [];

        foreach ($items as $item) {
            $masterId = $item->id;
            $dateScan = $item->dateScan;
            $noDokumen = $item->noDokumen;
            $finishDokumen = $item->finishDokumen;

            // Initialize the structure for this master record
            $result[$masterId] = [
                'dateScan' => $dateScan,
                'noDokumen' => $noDokumen,
                'tipeBarcode' => $item->tipeBarcode, // Add tipeBarcode here
                'location' => $item->location,   
            ];

            // Initialize arrays for noDokumen and finishDokumen if not already set
            if (!isset($result[$masterId][$noDokumen])) {
                $result[$masterId][$noDokumen] = [];
            }

            // Process detail records for noDokumen
            foreach ($item->detailbarcode as $detail) {
                if ($detail->noDokumen === $noDokumen) {
                    $result[$masterId][$noDokumen][] = [
                        'partNo' => $detail->partNo,
                        'quantity' => $detail->quantity,
                        'label' => $detail->label,
                        'scantime' => $detail->scantime,
                        'position' => $detail->position,
                    ];
                }
            }

        }

        // Convert associative array to simple array
        $result = array_values($result);
        
        return view('barcodeinandout.listfinishbarcode', compact('result'));
    }

    public function filter(Request $request)
    {
        $query = BarcodePackagingMaster::with('detailBarcode');

        if ($request->filled('tipeBarcode')) {
            $query->where('tipeBarcode', $request->tipeBarcode);
        }

        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        if ($request->filled('dateScan')) {
            $dateScan = \Carbon\Carbon::createFromFormat('Y-m-d', $request->input('dateScan'))->startOfDay();
            $query->whereDate('dateScan', $dateScan);
        }

        $result = $query->get()->map(function ($item) {
            return [
                'dateScan' => $item->dateScan,
                'noDokumen' => $item->noDokumen,
                'tipeBarcode' => $item->tipeBarcode,
                'location' => $item->location,
                $item->noDokumen => $item->detailBarcode->map(function ($detail) {
                    return [
                        'partNo' => $detail->partNo,
                        'label' => $detail->label,
                        'quantity' => $detail->quantity,
                        'position' => $detail->position,
                        'scantime' => $detail->scantime,
                    ];
                }),
            ];
        });

        return view('barcodeinandout.partials.barcode_table', ['result' => $result]);
    }



    public function latestitemdetails(Request $request)
    {
        // Fetch distinct part numbers for the dropdown
        $partNumbers = BarcodePackagingDetail::select('partNo')->distinct()->get();

        // Fetch all items
        $items = BarcodePackagingDetail::where('label', '!=', 'ADJUST')->get();

        // Create an associative array to hold the latest records
        $latestItems = [];

        // Iterate over each item
        foreach ($items as $item) {
            $key = $item->partNo . '|' . $item->label;

            // If the key doesn't exist or the current item's scantime is later, update the array
            if (!isset($latestItems[$key]) || $item->scantime > $latestItems[$key]->scantime) {
                $latestItems[$key] = $item;
            }
        }

        // Extract the values to get the final collection
        $latestItems = array_values($latestItems);

        // Group by partNo and sort by label within each group
        $groupedItems = [];
        foreach ($latestItems as $item) {
            $groupedItems[$item->partNo][] = $item;
        }

        // Sort each group by label
        foreach ($groupedItems as &$group) {
            usort($group, function($a, $b) {
                return $a->label <=> $b->label;
            });
        }

        // Flatten the groups into a single array
        $sortedItems = array_merge(...array_values($groupedItems));

        // Apply filters
        if ($request->filled('partNo')) {
            $sortedItems = array_filter($sortedItems, function($item) use ($request) {
                return $item->partNo == $request->input('partNo');
            });
        }

        if ($request->filled('scantime')) {
            $sortedItems = array_filter($sortedItems, function($item) use ($request) {
                return $item->scantime == $request->input('scantime');
            });
        }

        if ($request->filled('position')) {
            $sortedItems = array_filter($sortedItems, function($item) use ($request) {
                return $item->position == $request->input('position');
            });
        }
        

        return view('barcodeinandout.latestbarcodeitem', compact('sortedItems', 'partNumbers'));

    }

    public function historybarcodelist(Request $request)
    {
        $query = BarcodePackagingMaster::with(['detailbarcode' => function($query) use ($request) {
            if ($request->has('partNo') && $request->partNo != '') {
                $query->where('partNo', $request->partNo);
            }
        }]);

        // Apply filters
        if ($request->has('datescan') && $request->datescan != '') {
            $query->whereDate('dateScan', $request->datescan);
        }
    
        if ($request->has('barcode_type') && $request->barcode_type != '') {
            $query->where('tipeBarcode', $request->barcode_type);
        }
    
        if ($request->has('location') && $request->location != '') {
            $query->where('location', $request->location);
        }
    
        $items = $query->get();

        $distinctPartNos = BarcodePackagingDetail::select('partNo')->distinct()->get();

        return view('barcodeinandout.historylisttable', compact('items', 'distinctPartNos'));
    }
    

    public function stockall($location = 'Jakarta')
    {
        // Define position mappings based on location
        $positionMapping = [
            'Jakarta' => ['position' => 'Jakarta', 'customerPosition' => 'CustomerJakarta'],
            'Karawang' => ['position' => 'Karawang', 'customerPosition' => 'CustomerKarawang']
        ];

        // Check if the location exists in the mapping
        if (!array_key_exists($location, $positionMapping)) {
            abort(404, 'Location not found');
        }

        // Retrieve data based on location mapping
        $locationData = $positionMapping[$location];
        $datas = BarcodePackagingDetail::where('position', $locationData['position'])
                                    ->orWhere('position', $locationData['customerPosition'])
                                    ->get();

        $names = MasterDataRogPartName::get();

        $partNos = $datas->pluck('partNo')->unique();
        $balances = [];

        foreach ($partNos as $partNo) {
            // Calculate quantities based on location
            $locationQuantity = $datas->where('partNo', $partNo)
                                    ->where('position', $locationData['position'])
                                    ->sum('quantity');

            $customerQuantity = $datas->where('partNo', $partNo)
                                    ->where('position', $locationData['customerPosition'])
                                    ->sum('quantity');

            $balance = max($locationQuantity - $customerQuantity, 0);

            // Find the corresponding name data and extract the description
            $nameData = $names->first(function ($item) use ($partNo) {
                return strpos($item->name, "{$partNo}/") === 0;
            });

            // Extract description or provide default message
            $description = $nameData ? explode('/', $nameData->name, 2)[1] : 'No description available';

            // Add the partNo, description, and balance to the balances array
            $balances[] = [
                'partNo' => $partNo,
                'description' => $description,
                'balance' => $balance,
            ];
        }

        return view('barcodeinandout.stockallbarcode', compact('balances', 'location'));
    }
}

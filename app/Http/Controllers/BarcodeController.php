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

        $barcodeData = $partNumber . "\t" . $startnum;
        // Generate the barcode using DNS1D (1D Barcode)
        $barcode = new DNS1D();
    
       
        // Use $spkNumber in the filename
        $filename =  $partNumber . '-'. $startnum .'.png';
       
        $lowercaseFilename = strtolower($filename);
    
       
        // Save the barcode as a PNG image inside the barcodes folder
        $barcode->getBarcodePNGPath($barcodeData, 'C128', 2, 70, [0, 0, 0], false, $filename);


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
                $suffix = 'J';
                break;
            case 'karawang':
                $suffix = 'K';
                break;
            default:
                $suffix = '';
                break;
        }

        // Merge prefix and suffix
        $prefixSuffix = $prefix . $suffix;

        // Validate and set position based on the merged prefix and suffix
        switch ($prefixSuffix) {
            case 'INJ':
                $position = 'Jakarta';
                break;
            case 'INK':
                $position = 'Karawang';
                break;
            case 'OUTJ':
                $position = 'Customer';
                break;
            case 'OUTK':
                $position = 'Customer';
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

        // Generate random string part
        $randomString = substr(md5(microtime()), 0, 6); // Example of generating a random string

        // Combine prefix, suffix, and random string to form the document number
        $noDokumen = $prefix . $suffix . '-' . $randomString . '-' . $id;

        $barcodePackagingMaster->noDokumen = $noDokumen;
        // Output the generated document number for testing purposes
        
        
        
        $barcodePackagingMaster->save();
       
        return view('barcodeinandout.scanpage', compact('noDokumen', 'tanggalScanFull', 'position'));
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
            BarcodePackagingDetail::create([
                'masterId' => $idmaster,
                'noDokumen' => $data['noDokumen'],
                'partNo' => $data["partno" . $counter],
                'label' => $data["label" . $counter],
                'position' => $data['position'],
                'scantime' => \Carbon\Carbon::parse($data["scantime" . $counter])->format('Y-m-d H:i:s'),
            ]);
            $counter++;
        }
        return redirect()->route('barcode.base.index')->with('success', 'Data added successfully');
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


    public function latestitemdetails()
    {
        $items = BarcodePackagingDetail::all();

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

        return view('barcodeinandout.latestbarcodeitem', compact('sortedItems'));

    }
}
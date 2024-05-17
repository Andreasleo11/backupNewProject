<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;

class BarcodeController extends Controller
{
    public function generateBarcode()
    {
        $spkNumber = '24006267';
        $quantity = '1200';
        $warehouse = 'FG';
        $incrementNumber = '1';

        $barcodes = [];
        for($i = $incrementNumber; $i <= 6; $i++)
        {

        // Format the data as required ( DI SAP HARUS MENGGUNAKAN TAB )
        $barcodeData = $spkNumber . "\t" . $quantity . "\t" . $warehouse . "\t" . $incrementNumber;
    
        // Generate the barcode using DNS1D (1D Barcode)
        $barcode = new DNS1D();
    
       
        // Use $spkNumber in the filename
        $filename = 'barcode_' . $spkNumber . '.png';
    
       
        // Save the barcode as a PNG image inside the barcodes folder
        $test = $barcode->getBarcodePNGPath($barcodeData, 'C128', 2, 70, [0, 0, 0], false, $filename);
        $incrementNumber += 1;


        // Generate the HTML for the barcode
        $barcodeHtml = $barcode->getBarcodeHTML($barcodeData, 'C128');
        // URL to the saved barcode image
        $barcodeUrl = asset($test);

        // Generate the HTML for the barcode
        $barcodeHtml = $barcode->getBarcodeHTML($barcodeData, 'C128', 2, 70);

        $barcodes[] = [
            'barcodeHtml' => $barcodeHtml,
            'barcodeUrl' => $barcodeUrl,
            'incrementNumber' => $incrementNumber,
          
        ];
        

        }

        
        
    
        return view('barcode', ['barcodes' => $barcodes]);
    }
}

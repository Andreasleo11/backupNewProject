<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FormKerusakan;
use App\Models\SummaryFormKerusakan;

class FormKerusakanController extends Controller
{
    public function index()
    {
        $customers = FormKerusakan::select("customer")->distinct()->pluck("customer");
        $release_dates = FormKerusakan::distinct()->pluck("release_date");

        $datas = FormKerusakan::get();
        $summaries = SummaryFormKerusakan::all();
        // dd($customers);

        // dd($datas);

        return view("formkerusakan.index", compact("customers", "release_dates", "summaries"));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        // Validate the request data
        $validatedData = $request->validate([
            "customer" => "required|string|max:255",
            "release_date" => "required|date",
            "nama_barang" => "required|string|max:255",
            "proses" => "required|string|max:255",
            "masalah" => "required|string|max:255",
            "sebab" => "required|string|max:255",
            "penanggulangan" => "required|string|max:255",
            "pic" => "required|string|max:255",
            "target" => "required|string",
            "keterangan" => "required|string|max:5000",
        ]);

        // Create a new LaporanKerusakan instance and save the validated data
        FormKerusakan::create($validatedData);

        // Redirect back with a success message
        return redirect()->back()->with("success", "Laporan Kerusakan berhasil ditambahkan!");
    }

    public function report(Request $request)
    {
        $customer = $request->input("customer");
        $release_date = $request->input("release_date");

        // Check if a record already exists in SummaryFormKerusakan for the given customer and release date
        $summary = SummaryFormKerusakan::where("customer", $customer)
            ->where("release_date", $release_date)
            ->first();

        if ($summary) {
            // If the record exists, retrieve the existing doc_num
            $doc_num = $summary->doc_num;
        } else {
            // Generate the new doc_num
            $prefix = "DI-F-P/PR/12/BU-";
            $lastDoc = SummaryFormKerusakan::where("doc_num", "LIKE", "{$prefix}%")
                ->orderBy("id", "desc")
                ->first();

            if ($lastDoc) {
                // Extract the last number from the previous doc_num
                $lastNumber = (int) substr($lastDoc->doc_num, -3);
                $newNumber = str_pad($lastNumber + 1, 3, "0", STR_PAD_LEFT);
            } else {
                $newNumber = "001";
            }

            $doc_num = $prefix . $newNumber;

            // Save the new doc_num and other data to SummaryFormKerusakan
            $summary = SummaryFormKerusakan::create([
                "doc_num" => $doc_num,
                "customer" => $customer,
                "release_date" => $release_date,
            ]);
        }

        // Fetch all records for the selected customer and release date
        $reports = FormKerusakan::where("customer", $customer)
            ->where("release_date", $release_date)
            ->get();

        // Return a view with the report data
        return view(
            "formkerusakan.report",
            compact("reports", "customer", "release_date", "doc_num"),
        );
    }

    public function show($id)
    {
        // Fetch the report by its ID
        $summary = SummaryFormKerusakan::findOrFail($id);
        $customer = $summary->customer;
        $release_date = $summary->release_date;
        $doc_num = $summary->doc_num;

        $reports = FormKerusakan::where("customer", $customer)
            ->where("release_date", $release_date)
            ->get();

        return view(
            "formkerusakan.report",
            compact("reports", "customer", "release_date", "doc_num"),
        );
    }

    public function destroy($id)
    {
        $summary = SummaryFormKerusakan::findOrFail($id);
        $summary->delete();

        return redirect()
            ->route("formkerusakan.index")
            ->with("success", "Report deleted successfully.");
    }
}

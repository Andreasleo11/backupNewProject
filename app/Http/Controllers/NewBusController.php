<?php

namespace App\Http\Controllers;

use App\Domain\Production\Services\DeliverySchedulingService;
use App\Domain\Production\Services\WIPProcessingService;
use Illuminate\Support\Facades\DB;

class NewBusController extends Controller
{
    public function __construct(
        private readonly DeliverySchedulingService $deliveryService,
        private readonly WIPProcessingService $wipService
    ) {
        $this->middleware('auth');
    }

    public function delivery_schedule()
    {
        $deliverySchedule = DB::table('delsched_final')->get();
        $dateList = DB::table('uti_date_list')->where('id', 13)->first();

        return view('program/new_business/delivery_schedule/bus_del_delsched', [
            'delivery_schedule' => $deliverySchedule,
            'last_update' => $dateList->last_update,
        ]);
    }

    public function delivery_schedule_proses_1()
    {
        $this->deliveryService->processInitialSetup();

        return redirect()->action([self::class, 'delivery_schedule_proses_2']);
    }

    public function delivery_schedule_proses_2()
    {
        $this->deliveryService->filterByClosedSalesOrders();

        return redirect()->action([self::class, 'delivery_schedule_proses_3']);
    }

    public function delivery_schedule_proses_3()
    {
        $this->deliveryService->calculateOutstandingWithDeliveries();

        return redirect()->action([self::class, 'delivery_schedule_proses_4']);
    }

    public function delivery_schedule_proses_4()
    {
        $this->deliveryService->applyStockBalancing();

        return redirect()->action([self::class, 'delivery_schedule']);
    }

    public function delivery_schedule_wip()
    {
        $deliverySchedule = DB::table('delsched_finalwip')->get();
        $dateList = DB::table('uti_date_list')->where('id', 14)->first();

        return view('program/new_business/delivery_schedule/bus_del_delsched_wip', [
            'delivery_schedule' => $deliverySchedule,
            'last_update' => $dateList->last_update,
        ]);
    }

    public function delivery_schedule_wip_proses_1()
    {
        $this->wipService->processWIPData();

        return redirect()->action([self::class, 'delivery_schedule_wip_proses_2']);
    }

    public function delivery_schedule_wip_proses_2()
    {
        $this->wipService->processWIPFinalCalculations();

        return redirect()->action([self::class, 'delivery_schedule_wip']);
    }
}

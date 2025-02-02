<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\M_Qty;
use Yajra\DataTables\Facades\DataTables;
use App\Imports\SparepartsImports;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ErrorLogExport;
use App\Exports\ErrorLogRchExport;
use App\Exports\DashboardTransaksi;
use App\Exports\DashboardTransaksiDetail;
use App\Exports\DashboardRecehTransaksi;
use App\Exports\DashboardRecehTransaksiDetail;
use DB;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('dashboard');
    }
      


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboardreceh');
    }

    public function errorview()
    {
        return view('dashboarderror');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    
    
    }

    public function getDataAll(Request $request)
    {
        $query = DB::table('delivery')
            ->join('record', 'delivery.no_transaksi', '=', 'record.no_transaksi')
            ->join('master_model_part', function ($join) {
                $join->on('delivery.part_number', '=', 'master_model_part.part_number')
                    ->on('record.model', '=', 'master_model_part.model');
            })
            ->select(
                'delivery.no_transaksi', 
                'delivery.tgl_bln_thn', 
                'record.model', 
                'master_model_part.part_name', 
                'delivery.part_number', 
                'delivery.lot_number', 
                'record.tipe_delv', 
                'record.plant_dest', 
                'delivery.qty'
            )
            ->distinct()
            ->where('delivery.flag', 0)
            ->where('record.flag', 0)
            ->get();
            

        return DataTables::of($query)->make(true);
    }

    public function getDataAllRch(Request $request)
    {
        $query = DB::table('delivery_receh')
            ->join('record_receh', 'delivery_receh.no_transaksi', '=', 'record_receh.no_transaksi')
            ->join('master_model_part', function ($join) {
                $join->on('delivery_receh.part_number', '=', 'master_model_part.part_number')
                    ->on('record_receh.model', '=', 'master_model_part.model');
            })
            ->select(
                'delivery_receh.no_transaksi', 
                'delivery_receh.tgl_bln_thn', 
                'record_receh.model', 
                'master_model_part.part_name', 
                'delivery_receh.part_number', 
                'delivery_receh.serial_number', 
                'delivery_receh.lot_number', 
                'record_receh.tipe_delv', 
                'record_receh.plant_dest', 
                'delivery_receh.qty'
            )
            ->distinct()
            ->where('delivery_receh.flag', 0)
            ->where('record_receh.flag', 0)
            ->get();
            

        return DataTables::of($query)->make(true);
    }

    public function getDataRecord(Request $request)
    {
        $query = DB::table('delivery')
            ->join('record', 'delivery.no_transaksi', '=', 'record.no_transaksi')
            ->join('master_model_part', function ($join) {
                $join->on('delivery.part_number', '=', 'master_model_part.part_number')
                    ->on('record.model', '=', 'master_model_part.model');
            })
            ->select(
                'delivery.no_transaksi',
                'record.tgl_bln_thn',
                'record.tgl_bln_thn_dlv', 
                'record.model',
                'master_model_part.part_name',
                'delivery.part_number',
                'record.tipe_delv',
                'record.plant_dest',
                'record.qty',
                DB::raw("CASE WHEN record.flag = 1 THEN 'Proses' ELSE 'Berhasil' END AS status")
            )
            ->distinct()
            ->orderBy('record.tgl_bln_thn', 'desc')
            ->get();
    
        return DataTables::of($query)->make(true);
    }   
    
    public function getDataRecordRch(Request $request)
    {
        $query = DB::table('delivery_receh')
            ->join('record_receh', 'delivery_receh.no_transaksi', '=', 'record_receh.no_transaksi')
            ->join('master_model_part', function ($join) {
                $join->on('delivery_receh.part_number', '=', 'master_model_part.part_number')
                    ->on('record_receh.model', '=', 'master_model_part.model');
            })
            ->select(
                'delivery_receh.no_transaksi',
                'record_receh.tgl_bln_thn',
                'record_receh.tgl_bln_thn_dlv', 
                'record_receh.model',
                'master_model_part.part_name',
                'delivery_receh.part_number',
                'record_receh.tipe_delv',
                'record_receh.plant_dest',
                'record_receh.qty_receh',
                DB::raw("CASE WHEN record_receh.flag = 1 THEN 'Proses' ELSE 'Berhasil' END AS status")
            )
            ->distinct()
            ->orderBy('record_receh.tgl_bln_thn', 'desc')
            ->get();
    
        return DataTables::of($query)->make(true);
    }   

    public function getDataError(Request $request)
    {
        $query = DB::table('logserror')
            ->join('delivery', 'logserror.no_transaksi', '=', 'delivery.no_transaksi')
            ->join('record', 'logserror.no_transaksi', '=', 'record.no_transaksi')
            
            ->select(
                'logserror.no_transaksi',
                'logserror.tgl_bln_thn', 
                'delivery.lot_number', 
                'record.model',
                'delivery.part_number',
                'record.tipe_delv',
                'record.plant_dest',
                'logserror.note'
            )
            ->distinct()
            ->get();
    
        return DataTables::of($query)->make(true);
    }   

    public function getDataErrorRch(Request $request)
    {
        $query = DB::table('logserror')
            ->join('delivery_receh', 'logserror.no_transaksi', '=', 'delivery_receh.no_transaksi')
            ->join('record_receh', 'logserror.no_transaksi', '=', 'record_receh.no_transaksi')
            
            ->select(
                'logserror.no_transaksi',
                'logserror.tgl_bln_thn', 
                'delivery_receh.lot_number', 
                'record_receh.model',
                'delivery_receh.part_number',
                'record_receh.tipe_delv',
                'record_receh.plant_dest',
                'logserror.note'
            )
            ->distinct()
            ->get();
    
        return DataTables::of($query)->make(true);
    }   

    public function exportErrorLogs()
    {
        return Excel::download(new ErrorLogExport, 'error_logs.xlsx');
    }

    public function exportErrorLogsRch()
    {
        return Excel::download(new ErrorLogRchExport, 'error_logs_rch.xlsx');
    }

    public function dashboardTransaksi()
    {
        return Excel::download(new DashboardTransaksi, 'delivery.xlsx');
    }

    public function dashboardTransaksiDetail()
    {
        return Excel::download(new DashboardTransaksiDetail, 'delivery_detail.xlsx');
    }

    public function dashboardRecehTransaksi()
    {
        return Excel::download(new DashboardRecehTransaksi, 'deliveryreceh.xlsx');
    }

    public function dashboardRecehTransaksiDetail()
    {
        return Excel::download(new DashboardRecehTransaksiDetail, 'deliveryreceh_detail.xlsx');
    }
    
}

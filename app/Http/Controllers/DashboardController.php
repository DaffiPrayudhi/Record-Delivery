<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\M_Qty;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SparepartsImports;
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
            ->orderBy('delivery.tgl_bln_thn', 'desc')
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
    
}

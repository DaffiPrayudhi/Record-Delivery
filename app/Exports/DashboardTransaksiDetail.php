<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Facades\DB;

class DashboardTransaksiDetail implements FromCollection
{
    public function collection()
    {
        return DB::table('delivery')
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
    }
}

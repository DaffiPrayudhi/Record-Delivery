<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Facades\DB;

class DashboardRecehTransaksiDetail implements FromCollection
{
    public function collection()
    {
        return DB::table('delivery_receh')
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
    }
}

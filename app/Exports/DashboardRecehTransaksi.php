<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Facades\DB;

class DashboardRecehTransaksi implements FromCollection
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
    }
}

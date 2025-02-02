<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Facades\DB;

class DashboardTransaksi implements FromCollection
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
    }
}

<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Facades\DB;

class ErrorLogExport implements FromCollection
{
    public function collection()
    {
        return DB::table('logserror')
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
    }
}


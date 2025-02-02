<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Facades\DB;

class ErrorLogRchExport implements FromCollection
{
    public function collection()
    {
        return DB::table('logserror')
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
    }
}


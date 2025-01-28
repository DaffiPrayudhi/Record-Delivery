<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecordRch extends Model
{
    use HasFactory;

    protected $table = 'record_receh';
    protected $primaryKey = 'id';
    protected $fillable = [
        'no_transaksi', 
        'tgl_bln_thn', 
        'tgl_bln_thn_dlv', 
        'model',
        'plant_dest', 
        'lot_number', 
        'tipe_delv', 
        'pic',
        'qty_receh',
        'flag'
    ];

    public $timestamps = false; 
}

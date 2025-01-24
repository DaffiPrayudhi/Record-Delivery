<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecordSmpn extends Model
{
    use HasFactory;

    protected $table = 'record_simpanan';
    protected $primaryKey = 'id';
    protected $fillable = [
        'no_transaksi', 
        'tgl_bln_thn', 
        'model',
        'plant_dest', 
        'lot_number', 
        'tipe_delv', 
        'pic',
        'qty',
        'flag'
    ];

    public $timestamps = false; 
}

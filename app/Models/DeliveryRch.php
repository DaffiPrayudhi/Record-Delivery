<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryRch extends Model
{
    use HasFactory;

    protected $table = 'delivery_receh';
    protected $primaryKey = 'id';
    protected $fillable = [
        'no_transaksi', 
        'tgl_bln_thn', 
        'part_number',
        'lot_number', 
        'qty',
        'flag'
    ];

    public $timestamps = false; 
}

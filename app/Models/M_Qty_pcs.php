<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class M_Qty_pcs extends Model
{
    use HasFactory;

    protected $table = 'master_qty_pcs';
    protected $primaryKey = 'id';
    protected $fillable = [
        'no_transaksi',
        'qty_pcs',
        'flag',
    ];

    public $timestamps = false; 
}

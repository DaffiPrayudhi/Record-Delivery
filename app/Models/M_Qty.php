<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class M_Qty extends Model
{
    use HasFactory;

    protected $table = 'master_qty';
    protected $primaryKey = 'id';
    protected $fillable = [
        'no_transaksi',
        'total_qty',
        'flag',
    ];

    public $timestamps = false; 
}

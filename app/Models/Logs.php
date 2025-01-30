<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Logs extends Model
{
    protected $table = 'logserror';
    protected $primaryKey = 'id';
    protected $fillable = [
        'no_transaksi',
        'tgl_bln_thn',
        'note',
    ];

    public $timestamps = false; 
}

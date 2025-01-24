<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class M_Serial extends Model
{
    use HasFactory;

    protected $table = 'master_serial_number';
    protected $primaryKey = 'id';
    protected $fillable = [
        'model',
        'serial_number',
    ];

    public $timestamps = false; 
}

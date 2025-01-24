<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class M_Model_Part extends Model
{
    use HasFactory;

    protected $table = 'master_model_part';
    protected $primaryKey = 'id';
    protected $fillable = [
        'part_number',
        'part_name',
        'model',
        'tipe_delv',
        'plant_dest',
    ];

    public $timestamps = false; 
}

<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use App\Models\PurchaseRequest;

class StatusSubmitChanged
{
    use SerializesModels;

    public $data;
}




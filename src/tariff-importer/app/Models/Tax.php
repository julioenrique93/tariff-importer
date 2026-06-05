<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    public function productUnit() {
        return $this->belongsTo(ProductUnit::class);
    }
}

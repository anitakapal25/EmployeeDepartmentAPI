<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = ['employee_id', 'address_line1', 'address_line2', 'city', 'state', 'zip'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}


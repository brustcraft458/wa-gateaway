<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory, HasUuids;

    protected $table = "messages";
    protected $fillable = [
        'status',
        'phone',
        'hash',
        'text',
        'customer_id'
    ];
}

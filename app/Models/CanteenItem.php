<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CanteenItem extends Model
{
    protected $fillable = ['name','price','category','active','stock','blockable','image'];
    protected $casts = ['price' => 'decimal:2', 'active' => 'boolean', 'stock' => 'integer', 'blockable' => 'boolean'];
}

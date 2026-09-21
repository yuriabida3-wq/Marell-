<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Confession extends Model {
    protected $fillable = ['category','message','contact','ip_hash','read','flagged'];
}

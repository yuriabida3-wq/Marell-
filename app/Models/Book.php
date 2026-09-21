<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = ['isbn','title','author','category','publisher','year','total_copies','available_copies','shelf','cover','active'];
    protected $casts = ['active' => 'boolean'];

    public function loans() { return $this->hasMany(BookLoan::class); }
    public function activeLoans() { return $this->hasMany(BookLoan::class)->whereNull('returned_at'); }

    public function isAvailable(): bool { return $this->available_copies > 0; }
}

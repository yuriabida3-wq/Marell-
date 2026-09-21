<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibrarySetting extends Model
{
    protected $fillable = ['loan_days','fine_per_day','max_books_per_student'];
}

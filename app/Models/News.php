<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class News extends Model {
    use HasFactory;
    protected $table = 'news';
    protected $fillable = ['title','slug','excerpt','body','image','author_id','published'];
    protected static function booted(): void {
        static::creating(function ($news) {
            if (empty($news->slug)) $news->slug = Str::slug($news->title) . '-' . uniqid();
        });
    }
}

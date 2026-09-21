<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginActivity extends Model
{
    protected $fillable = ['user_id','email','event','ip','user_agent','location','reason'];

    public function user() { return $this->belongsTo(User::class); }

    public static function record(string $event, ?string $email = null, ?int $userId = null, ?string $reason = null): void
    {
        try {
            self::create([
                'user_id'    => $userId,
                'email'      => $email,
                'event'      => $event,
                'ip'         => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 500),
                'reason'     => $reason,
            ]);
        } catch (\Throwable $e) {
            \Log::warning('LoginActivity write failed: ' . $e->getMessage());
        }
    }
}

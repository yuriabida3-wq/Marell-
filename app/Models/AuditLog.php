<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = ['user_id','action','model_type','model_id','before','after','ip','user_agent','reason'];
    protected $casts = ['before' => 'array', 'after' => 'array'];

    public function user() { return $this->belongsTo(User::class); }

    public static function log(string $action, $model = null, array $before = [], array $after = [], ?string $reason = null): void
    {
        try {
            self::create([
                'user_id'    => auth()->id(),
                'action'     => $action,
                'model_type' => $model ? get_class($model) : null,
                'model_id'   => $model?->id,
                'before'     => $before ?: null,
                'after'      => $after ?: null,
                'ip'         => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 500),
                'reason'     => $reason,
            ]);
        } catch (\Throwable $e) {
            \Log::warning('AuditLog write failed: ' . $e->getMessage());
        }
    }
}

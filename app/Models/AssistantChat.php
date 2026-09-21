<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssistantChat extends Model
{
    protected $fillable = ['session_key','user_message','bot_reply','matched_intent','ip'];
}

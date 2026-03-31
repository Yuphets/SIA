<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'username',
        'action',
        'details',
    ];

    public static function log($userId, $username, $action, $details)
    {
        return self::create([
            'user_id' => $userId,
            'username' => $username,
            'action' => $action,
            'details' => $details,
        ]);
    }
}

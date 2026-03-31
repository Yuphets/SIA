<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'budget_limit',
        'last_budget_change',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_budget_change' => 'datetime',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->budget_limit)) {
                $user->budget_limit = 10000;
            }
            if (empty($user->role)) {
                $user->role = 'user';
            }
        });
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function canChangeBudget()
    {
        if ($this->isAdmin()) return true;
        if (!$this->last_budget_change) return true;

        $daysSinceLastChange = now()->diffInDays($this->last_budget_change);
        return $daysSinceLastChange >= 7;
    }

    public function getBudgetChangeCooldownDays()
    {
        if (!$this->last_budget_change || $this->isAdmin()) return 0;

        $daysSinceLastChange = now()->diffInDays($this->last_budget_change);
        if ($daysSinceLastChange >= 7) return 0;

        return 7 - $daysSinceLastChange;
    }

    public function getTotalExpenses()
    {
        return $this->expenses()->sum('amount') ?? 0;
    }

    public function getRemainingBudget()
    {
        return $this->budget_limit - $this->getTotalExpenses();
    }

    public function getBudgetPercentage()
    {
        if ($this->budget_limit == 0) return 0;
        return ($this->getTotalExpenses() / $this->budget_limit) * 100;
    }
}

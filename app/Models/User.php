<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Cashier\Billable;

use function Illuminate\Events\queueable;


class User extends Authenticatable
{
    use Billable;
    use HasApiTokens;
    
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $connection = 'mysql';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'nickname',
        'email',
        'password',
        'games_played',
        'games_won',
        'times_impostor',
        'role_user'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Para cashier stripe
    protected static function booted(): void
    {
        static::updated(queueable(function (User $customer) {
            if ($customer->hasStripeId()) {
                $customer->syncStripeCustomerDetails();
            }
        }));
    }

    // RELACIONES ELOQUENT
    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'game_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /** Roles */
    public function hasRole(string $role): bool
{
    return strtolower(trim($this->role_user)) === strtolower(trim($role));
}

    public function hasAnyRole(string ...$roles): bool
{
    return in_array(
        strtolower(trim($this->role_user)),
        array_map(fn($role) => strtolower(trim($role)), $roles)
    );
}

    public function isAdmin(): bool{
        return $this->role_user === 'admin'; 
    }
}

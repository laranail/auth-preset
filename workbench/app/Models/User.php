<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use Laravel\Passkeys\Passkey;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Workbench\App\Support\PasskeyMorphMany;
use Workbench\Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements PasskeyUser
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use PasskeyAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function passkeys(): HasMany
    {
        return new PasskeyMorphMany(
            query: Passkey::query(),
            parent: $this,
            morphType: 'passkeyable_type',
            foreignKey: 'passkeyable_id',
            localKey: $this->getKeyName(),
            morphClass: $this->getMorphClass(),
        );
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }
}

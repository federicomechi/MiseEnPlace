<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password', 'is_admin', 'role'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    public const ROLE_FULL_ACCESS = 'full_access';

    public const ROLE_EART_ADMIN = 'eart_admin';

    public const ROLE_CLIENT = 'client';

    public const ROLE_KITCHEN = 'kitchen';

    public const ROLE_BAR = 'bar';

    public const ROLE_ALL = 'all';

    public const ROLE_FIRST = 'first';

    public const ROLE_OPEN = 'open';

    /** @return array<string, string> */
    public static function roleLabels(): array
    {
        return [
            self::ROLE_FULL_ACCESS => 'Accesso completo',
            self::ROLE_EART_ADMIN => 'eARTadmin',
            self::ROLE_CLIENT => 'Cliente',
            self::ROLE_KITCHEN => 'Cucina',
            self::ROLE_BAR => 'Bar',
            self::ROLE_ALL => 'All',
            self::ROLE_FIRST => 'Primo avvio',
            self::ROLE_OPEN => 'Open',
        ];
    }

    public static function isAdministrativeRole(?string $role): bool
    {
        return in_array($role, [self::ROLE_FULL_ACCESS, self::ROLE_EART_ADMIN], true);
    }

    public function hasAdministrativeAccess(): bool
    {
        return $this->is_admin || self::isAdministrativeRole($this->role ?? self::ROLE_OPEN);
    }

    /** @return array<int, string> */
    public function permittedWorkspaceSections(): array
    {
        return match ($this->role ?? self::ROLE_OPEN) {
            self::ROLE_FULL_ACCESS, self::ROLE_EART_ADMIN => ['recipes', 'ingredients', 'menus', 'production', 'bar', 'suppliers', 'settings'],
            self::ROLE_ALL => ['recipes', 'ingredients', 'menus', 'production', 'bar'],
            self::ROLE_KITCHEN => ['recipes', 'ingredients', 'production'],
            self::ROLE_BAR => ['bar'],
            self::ROLE_CLIENT => ['menus'],
            self::ROLE_FIRST => ['setup'],
            default => [],
        };
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_admin' => 'boolean',
            'password' => 'hashed',
            /* @chisel-2fa */
            'two_factor_confirmed_at' => 'datetime',
            /* @end-chisel-2fa */
        ];
    }
}

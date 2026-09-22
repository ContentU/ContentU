<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case AccountManager = 'account_manager';
    case Copywriter = 'copywriter';
    case Client = 'client';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin agenzia',
            self::AccountManager => 'Account Manager',
            self::Copywriter => 'Copywriter',
            self::Client => 'Cliente',
        };
    }

    /** Ruoli che appartengono al team interno dell'agenzia. */
    public static function internal(): array
    {
        return [self::Admin, self::AccountManager, self::Copywriter];
    }

    public function isInternal(): bool
    {
        return in_array($this, self::internal(), true);
    }
}

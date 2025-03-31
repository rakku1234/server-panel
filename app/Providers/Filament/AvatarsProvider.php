<?php

namespace App\Providers\Filament;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Filament\AvatarProviders\Contracts\AvatarProvider;

class AvatarsProvider implements AvatarProvider
{
    public function get(Model|Authenticatable $record): string
    {
        return 'https://www.gravatar.com/avatar/'.hash('sha256', strtolower(trim($record->getAttribute('email'))));
    }
}

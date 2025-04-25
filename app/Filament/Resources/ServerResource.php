<?php

namespace App\Filament\Resources;

use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Resource;
use App\Filament\Resources\ServerResource\Pages;
use App\Models\Server;

class ServerResource extends Resource
{
    protected static ?string $model = Server::class;
    protected static string | \BackedEnum | null $navigationIcon = 'tabler-brand-docker';
    protected static ?string $navigationLabel = 'サーバー';
    protected static string | \UnitEnum | null $navigationGroup = 'サーバー管理';
    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        if (auth()->user()->hasRole('admin')) {
            return $query;
        }
        return $query->where('user', auth()->id());
    }

    public static function getNavigationBadge(): ?string
    {
        if (auth()->user()->hasRole('admin')) {
            return (string)Server::count();
        }
        return (string)Server::where('user', auth()->id())->count();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServer::route('/'),
            'create' => Pages\CreateServer::route('/create'),
            'edit' => Pages\EditServer::route('/{record:slug}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->hasPermissionTo('server.view');
    }
}

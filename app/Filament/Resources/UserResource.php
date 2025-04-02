<?php

namespace App\Filament\Resources;

use Illuminate\Support\Str;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Fieldset;
use Filament\Tables\Actions;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Providers\Filament\AvatarsProvider;
use App\Components\NumberConverter;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationLabel = 'ユーザー';
    protected static ?string $navigationGroup = 'ユーザー管理';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function getNavigationBadge(): ?string
    {
        return User::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('基本情報')
                    ->schema([
                        TextInput::make('name')
                            ->label('ユーザー名')
                            ->required()
                            ->readOnly(),
                        TextInput::make('email')
                            ->label('メールアドレス')
                            ->email()
                            ->required()
                            ->readOnly(),
                        TextInput::make('password')
                            ->label('パスワード')
                            ->required(fn ($livewire) => $livewire instanceof CreateRecord)
                            ->dehydrated(fn ($state) => filled($state))
                            ->suffixAction(
                        Action::make('password')
                                    ->label('パスワード生成')
                                    ->icon('tabler-password')
                                    ->action(fn (callable $set) => $set('password', Str::password()))
                            ),
                    ])
                    ->columns(3),
                Section::make('権限')
                    ->schema([
                        Select::make('roles')
                            ->label('ロール')
                            ->preload()
                            ->relationship('roles', 'name'),
                        Select::make('permissions')
                            ->label('パーミッション')
                            ->preload()
                            ->reactive()
                            ->multiple()
                            ->relationship('permissions', 'name', function ($query, callable $get) {
                                $query->whereIn('id', function ($subquery) {
                                    $subquery->select('permission_id')
                                        ->from('role_has_permissions');
                                });
                                $selectedRoles = $get('roles');
                                if (is_array($selectedRoles)) {
                                    $excludedPermissionIds = Role::whereIn('id', $selectedRoles)->with('permissions')->get()->pluck('permissions')->flatten()->pluck('id')->toArray();
                                    if (!empty($excludedPermissionIds)) {
                                        $query->whereNotIn('id', $excludedPermissionIds);
                                    }
                                }
                                return $query;
                            }),
                    ])
                    ->columns(),

                Fieldset::make('resource_limits')
                    ->label('リソース制限')
                    ->schema([
                        TextInput::make('resource_limits.max_cpu')
                            ->label('最大CPU容量')
                            ->numeric()
                            ->suffix('コア')
                            ->required()
                            ->formatStateUsing(fn ($state) => $state === null ? null : ($state === -1 ? -1 : NumberConverter::convertCpuCore($state)))
                            ->dehydrateStateUsing(fn ($state) => (int)$state === -1 ? -1 : NumberConverter::convertCpuCore((float)$state, false)),
                        TextInput::make('resource_limits.max_memory')
                            ->label('最大メモリ容量')
                            ->numeric()
                            ->suffix('MB')
                            ->required()
                            ->formatStateUsing(fn ($state) => $state === null ? null : ($state === -1 ? -1 : NumberConverter::convert($state, 'MiB', 'MB')))
                            ->dehydrateStateUsing(fn ($state) => (int)$state === -1 ? -1 : NumberConverter::convert((float)$state, 'MB', 'MiB')),
                        TextInput::make('resource_limits.max_disk')
                            ->label('最大ディスク容量')
                            ->numeric()
                            ->suffix('MB')
                            ->required()
                            ->formatStateUsing(fn ($state) => $state === null ? null : ($state === -1 ? -1 : NumberConverter::convert($state, 'MiB', 'MB')))
                            ->dehydrateStateUsing(fn ($state) => (int)$state === -1 ? -1 : NumberConverter::convert((float)$state, 'MB', 'MiB')),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar')
                    ->label('')
                    ->circular()
                    ->state(fn ($record) => (new AvatarsProvider())->get($record))
                    ->width(40),
                TextColumn::make('name')
                    ->label('ユーザー名'),
                TextColumn::make('roles')
                    ->label('ロール')
                    ->formatStateUsing(fn ($record) => $record->getRoleNames()->join(', ')),
                TextColumn::make('google2fa_enabled')
                    ->label('2段階認証')
                    ->formatStateUsing(fn ($record) => $record->google2fa_enabled ? '有効' : '無効'),
                TextColumn::make('timezone')
                    ->label('タイムゾーン')
                    ->formatStateUsing(fn ($record) => $record->timezone),
                TextColumn::make('created_at')
                    ->label('作成日時')
                    ->dateTime('Y年m月d日 H時i分')
                    ->timezone(auth()->user()->timezone),
            ])
            ->actions([
                Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->hasPermissionTo('user.view');
    }
}

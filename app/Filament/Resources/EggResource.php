<?php

namespace App\Filament\Resources;

use Illuminate\Support\Str;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use App\Filament\Resources\EggResource\Pages;
use App\Models\Egg;
use TypeError;

class EggResource extends Resource
{
    protected static ?string $model = Egg::class;
    protected static ?string $navigationIcon = 'tabler-eggs';
    protected static ?string $navigationLabel = 'Egg';
    protected static ?string $navigationGroup = 'サーバー管理';
    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        return Egg::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('')
                    ->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->disabled(),
                        TextInput::make('url')
                            ->label('Egg URL')
                            ->url(),
                        Textarea::make('description')
                            ->label('Description')
                            ->disabled(),
                        Toggle::make('public')
                            ->label('Public')
                            ->inline(false)
                    ])
                    ->columns(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('egg_id')
                    ->label('Egg ID'),
                TextColumn::make('name')
                    ->label('Name')
                    ->description(function($record) {
                        try {
                            return Str::limit($record->description);
                        } catch (TypeError) {
                            return null;
                        }
                    }),
                ToggleColumn::make('public')
                    ->label('Public'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEgg::route('/'),
            'edit' => Pages\EditEgg::route('/{record:slug}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->hasPermissionTo('egg.view');
    }
}

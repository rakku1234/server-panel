<?php

namespace App\Filament\Resources\EggResource\Pages;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use App\Filament\Resources\EggResource;
use Exception;

class EditEgg extends EditRecord
{
    protected static string $resource = EggResource::class;

    public function mount($record): void
    {
        if (!auth()->user()->can('egg.edit')) {
            abort(403);
        }
        parent::mount($record);
    }

    /**
     *
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if (isset($data['url'])) {
            try {
                $response = Http::get($data['url']);
                if ($response->successful()) {
                    $eggData = $response->json();
                    $data['startup']   = $eggData['startup'];
                    $data['variables'] = $eggData['variables'];
                }
            } catch (Exception $e) {
                Notification::make()
                    ->title('エラーが発生しました')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();
                return $record;
            }
        }
        $record->update($data);
        return $record;
    }

    public function getTitle(): string
    {
        return 'Egg編集';
    }
}

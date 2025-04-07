<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Node;
use App\Models\Server;
use App\Components\NumberConverter;

class ResourceLimit extends BaseWidget
{
    public static function canView(): bool
    {
        $controller = request()->route()?->getController();

        if ($controller && str_contains(get_class($controller), 'Dashboard')) {
            return false;
        }

        return true;
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        $limit = $user->resource_limits ?? [
            'max_cpu' => 0,
            'max_memory' => 0,
            'max_disk' => 0
        ];
        $stats = [];
        $unit = $user->unit;
        $servers = Server::where('user', $user->id)->get();
        $used_cpu = 0;
        $used_memory = 0;
        $used_disk = 0;
        foreach ($servers as $server) {
            $limits = $server->limits;
            $used_cpu += $limits['cpu'];
            $used_memory += $limits['memory'];
            $used_disk += $limits['disk'];
        }
        $stats[] = Stat::make("CPU", false)
            ->description(NumberConverter::convertCpuCore($used_cpu)." コア / ".($limit['max_cpu'] === -1 ? "無制限" : NumberConverter::convertCpuCore($limit['max_cpu'])." コア"))
            ->icon('heroicon-o-cpu-chip')
            ->color('');

        $stats[] = Stat::make("memory", false)
            ->label("メモリ")
            ->description(NumberConverter::convert($used_memory, 'MiB', $unit, true, 0)." / ".($limit['max_memory'] === -1 ? "無制限" : NumberConverter::convert($limit['max_memory'], 'MiB', $unit, true, 2)))
            ->icon('tabler-desk')
            ->color('');

        $stats[] = Stat::make("disk", false)
            ->label("ディスク")
            ->description(NumberConverter::convert($used_disk, 'MiB', $unit, true, 0)." / ".($limit['max_disk'] === -1 ? "無制限" : NumberConverter::convert($limit['max_disk'], 'MiB', $unit, true, 2)))
            ->icon('tabler-device-sd-card')
            ->color('');
        return $stats;
    }

    protected static function shouldPoll(): bool
    {
        return true;
    }
}

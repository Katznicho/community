<?php

namespace App\Filament\Widgets;

use App\Models\Community;
use App\Models\transaction;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            //
            Stat::make('Total Users', User::count())
                ->icon('heroicon-o-arrow-trending-up')
                ->description('Total number of users')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 9])
                // ->url(route("filament.admin.resources.districts.index"))
                ->extraAttributes([
                    'class' => 'text-white text-lg cursor-pointer',
                ]),

            Stat::make('Total Communities', Community::count())
                ->icon('heroicon-o-arrow-trending-up')
                ->description('Total number of communities')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 9])
                // ->url(route("filament.admin.resources.districts.index"))
                ->extraAttributes([
                    'class' => 'text-white text-lg cursor-pointer',
                ]),

            Stat::make('Total Transactions', transaction::count())
                ->icon('heroicon-o-arrow-trending-up')
                ->description('Total number of transactions')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 9])
                // ->url(route("filament.admin.resources.districts.index"))
                ->extraAttributes([
                    'class' => 'text-white text-lg cursor-pointer',
                ]),
        ];
    }
}

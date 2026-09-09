<?php

namespace App\Filament\Staff\Resources\StaffAttendanceOverviewResource\Pages;

use App\Filament\Staff\Resources\StaffAttendanceOverviewResource;
use App\Filament\Staff\Widgets\StaffAttendanceOverviewStatsWidget;
use App\Filament\Staff\Widgets\StaffAttendanceTrendsWidget;
use Filament\Resources\Pages\ListRecords;

class ListStaffAttendanceOverview extends ListRecords
{
    protected static string $resource = StaffAttendanceOverviewResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            StaffAttendanceOverviewStatsWidget::class,
            StaffAttendanceTrendsWidget::class,
        ];
    }
}

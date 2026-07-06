<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class DateRangeFilterWidget extends Widget
{
    protected string $view = 'filament.widgets.date-range-filter';

    protected static ?int $sort = 0;

    protected int | string | array $columnSpan = 12;

    public ?string $from = null;

    public ?string $to = null;

    public function mount(): void
    {
        $this->from = session('dashboard_date_from', now()->startOfMonth()->format('Y-m-d'));
        $this->to = session('dashboard_date_to', now()->format('Y-m-d'));
    }

    public function updated($property): void
    {
        $this->applyFilter();
    }

    public function applyFilter(): void
    {
        session([
            'dashboard_date_from' => $this->from,
            'dashboard_date_to' => $this->to,
        ]);
    }

    public function setToday(): void
    {
        $this->from = now()->format('Y-m-d');
        $this->to = now()->format('Y-m-d');
        $this->applyFilter();
    }

    public function setLast7Days(): void
    {
        $this->from = now()->subDays(7)->format('Y-m-d');
        $this->to = now()->format('Y-m-d');
        $this->applyFilter();
    }

    public function setThisMonth(): void
    {
        $this->from = now()->startOfMonth()->format('Y-m-d');
        $this->to = now()->format('Y-m-d');
        $this->applyFilter();
    }

    public function setThisYear(): void
    {
        $this->from = now()->startOfYear()->format('Y-m-d');
        $this->to = now()->format('Y-m-d');
        $this->applyFilter();
    }

    public function clearFilter(): void
    {
        $this->from = null;
        $this->to = null;
        session()->forget(['dashboard_date_from', 'dashboard_date_to']);
    }
}

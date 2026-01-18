<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Application;
use App\Models\Service;
use App\Models\User;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Report extends Component
{
    public $totalRevenue;
    public $totalApplications;
    public $reviewsCount;
    public $topDays = [];
    public $topMasters = [];
    public $mastersStats;
    public $topServices = [];
    public $statusStats = [];
    public $popularHours = [];
    public $newClientsCount;
    public $repeatClientsCount;
    public $reportStartDate;
    public $reportEndDate;
    public $reportMaster;
    public $reportData;
    public $showReportModal = false;
    public $listeners = ['openCustomReport', 'closeCustomReport'];

    protected function loadStatistics()
    {
        $this->mastersStats = Application::with('service')
            ->select('master_id', DB::raw('COUNT(*) as total'), DB::raw('SUM(services.price) as revenue'))
            ->leftJoin('services', 'applications.service_id', '=', 'services.id')
            ->groupBy('master_id')
            ->orderByDesc('revenue')
            ->get()
            ->map(function ($item) {
                $item->master_name = User::find($item->master_id)?->name ?? 'Неизвестно';
                $item->average = $item->total > 0 ? round($item->revenue / $item->total, 2) : 0;
                $item->percentage = $this->totalRevenue > 0
                    ? round(($item->revenue / $this->totalRevenue) * 100, 1)
                    : 0;
                return $item;
            });

        $this->topServices = Application::select(
                'services.name',
                DB::raw('count(*) as total'),
                DB::raw('sum(services.price) as revenue')
            )
            ->join('services', 'applications.service_id', '=', 'services.id')
            ->groupBy('services.name')
            ->orderByDesc('total')
            ->take(5)
            ->get();
    }

    public function mount()
    {
        $this->totalApplications = Application::count();

        $this->totalRevenue = Application::with('service')
            ->get()
            ->sum(fn($app) => $app->service->price ?? 0);

        $this->reviewsCount = Review::count();

        $this->topDays = Application::select(DB::raw("DAYNAME(date) as day"), DB::raw("count(*) as total"))
            ->groupBy('day')
            ->orderByDesc('total')
            ->take(3)
            ->pluck('total', 'day')
            ->toArray();

        // 🔧 Исправлено — теперь используем master_id, а не master
        $this->topMasters = Application::select('master_id', DB::raw('count(*) as total'))
            ->groupBy('master_id')
            ->orderByDesc('total')
            ->take(5)
            ->get()
            ->mapWithKeys(function ($item) {
                $masterName = User::find($item->master_id)?->name ?? 'Неизвестно';
                return [$masterName => $item->total];
            })
            ->toArray();

        $this->statusStats = Application::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $this->popularHours = Application::select(DB::raw('HOUR(created_at) as hour'), DB::raw('count(*) as total'))
            ->groupBy('hour')
            ->orderByDesc('total')
            ->take(5)
            ->pluck('total', 'hour')
            ->toArray();

        $clients = Application::select('user_id', DB::raw('count(*) as total'))
            ->groupBy('user_id')
            ->get();

        $this->repeatClientsCount = $clients->where('total', '>', 1)->count();
        $this->newClientsCount = $clients->where('total', 1)->count();

        $this->loadStatistics(); 
    }

    public function openCustomReport()
    {
        $this->validate([
            'reportStartDate' => 'required|date',
            'reportEndDate' => 'required|date|after_or_equal:reportStartDate',
        ]);

        $savedTotalRevenue = $this->totalRevenue;

        $query = Application::with('service')
            ->whereBetween('date', [$this->reportStartDate, $this->reportEndDate]);

        if ($this->reportMaster) {
            $query->where('master_id', $this->reportMaster);
        }

        $applications = $query->get();

        $totalRevenue = $applications->sum(fn($a) => $a->service->price ?? 0);
        $servicesStats = $applications->groupBy('service.name')->map(fn($group) => [
            'count' => $group->count(),
            'revenue' => $group->sum(fn($a) => $a->service->price ?? 0),
        ]);

        $this->reportData = [
            'master' => $this->reportMaster
                ? User::find($this->reportMaster)?->name
                : 'Все мастера',
            'period' => [
                'start' => $this->reportStartDate,
                'end' => $this->reportEndDate,
            ],
            'totals' => [
                'applications' => $applications->count(),
                'revenue' => $totalRevenue,
                'average_check' => $applications->count() > 0
                    ? round($totalRevenue / $applications->count(), 2)
                    : 0,
            ],
            'clients' => [
                'new' => $newClients = $applications->groupBy('user_id')
                    ->filter(fn($group) => $group->count() === 1)
                    ->count(),
                'repeat' => $repeatClients = $applications->groupBy('user_id')
                    ->filter(fn($group) => $group->count() > 1)
                    ->count(),
                'repeat_percent' => $applications->count() > 0
                    ? round($repeatClients / $applications->groupBy('user_id')->count() * 100, 1)
                    : 0,
                'new_percent' => $applications->count() > 0
                    ? round($newClients / $applications->groupBy('user_id')->count() * 100, 1)
                    : 0,
            ],
            'canceled' => $applications->where('status', 'canceled')->count(),
            'services' => $servicesStats,
            'top_hours' => $applications->groupBy(fn($a) => Carbon::parse($a->created_at)->format('H'))
                ->map(fn($group) => $group->count())
                ->sortDesc()
                ->take(5),
            'top_days' => $applications->groupBy(fn($a) => Carbon::parse($a->date)->format('l'))
                ->map(fn($group) => $group->count())
                ->sortDesc()
                ->take(3),
        ];

        $this->loadStatistics();
        $this->totalRevenue = $savedTotalRevenue;
        $this->showReportModal = true;
    }

    public function closeCustomReport()
    {
        $this->showReportModal = false;
    }

    public function render()
    {
        return view('livewire.report')->layout('layouts.app');
    }
}

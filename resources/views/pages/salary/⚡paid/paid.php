<?php

namespace App\Livewire;

use App\Models\Profile;
use App\Models\Salary;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Morilog\Jalali\Jalalian;

new class extends Component
{
    use WithPagination;

    public $selected_year_id = null;
    public $selected_month_id = null;
    public $detail_month_id = null;
    public $print_month_id = null;

    public function mount(): void
    {
        $latestYear = DB::table('payroll_years')->orderByDesc('year')->first();
        if ($latestYear) {
            $this->selected_year_id = $latestYear->id;
        }
    }

    #[Computed]
    public function profile()
    {
        if (!auth()->check()) return null;
        return Profile::where('user_id', auth()->id())->first();
    }

    #[Computed]
    public function years()
    {
        return DB::table('payroll_years')->orderByDesc('year')->get();
    }

    #[Computed]
    public function months()
    {
        if (!$this->selected_year_id) return collect();
        return DB::table('payroll_months')
            ->where('payroll_year_id', $this->selected_year_id)
            ->orderBy('month')
            ->get();
    }

    #[Computed]
    public function salaries()
    {
        if (!$this->profile) return collect();

        return DB::table('payroll_months')
            ->join('payroll_years', 'payroll_months.payroll_year_id', '=', 'payroll_years.id')
            ->when($this->selected_year_id, fn ($q) => $q->where('payroll_months.payroll_year_id', $this->selected_year_id))
            ->when($this->selected_month_id, fn ($q) => $q->where('payroll_months.id', $this->selected_month_id))
            ->select('payroll_months.id', 'payroll_months.month_name', 'payroll_years.year')
            ->orderByDesc('payroll_years.year')
            ->orderByDesc('payroll_months.month')
            ->paginate(10)
            ->through(function ($month) {
                $records = Salary::where('profile_id', $this->profile->id)
                    ->where('payroll_month_id', $month->id)
                    ->get();

                $totalHours = (float) $records->sum('overtime_hours');
                $baseRate = (float) ($this->profile->hourly_rate ?? 70000);

                $netSalary = $records->sum(function($r) use ($baseRate) {
                    $rate = ($r->hourly_rate > 0) ? (float)$r->hourly_rate : $baseRate;
                    return ($r->net_salary > 0) ? (float)$r->net_salary : round((float)$r->overtime_hours * $rate);
                });

                $paidRecord = $records->firstWhere('status', 'paid');
                $approvedRecord = $records->firstWhere('status', 'approved');

                return (object) [
                    'month_id' => $month->id,
                    'year' => $month->year,
                    'month_name' => $month->month_name,
                    'total_hours' => $totalHours,
                    'net_salary' => $netSalary,
                    'status' => $paidRecord ? 'paid' : ($approvedRecord ? 'issued' : 'pending'),
                    'issued_at_jalali' => ($paidRecord?->created_at ?? $approvedRecord?->created_at)
                        ? Jalalian::fromCarbon(\Illuminate\Support\Carbon::parse($paidRecord?->created_at ?? $approvedRecord?->created_at))->format('Y/m/d')
                        : null,
                ];
            });
    }

    #[Computed]
    public function weeklyDetails()
    {
        if (!$this->detail_month_id || !$this->profile) return null;

        $weeks = DB::table('payroll_weeks')->where('payroll_month_id', $this->detail_month_id)->get();
        $salaries = Salary::where('profile_id', $this->profile->id)
            ->where('payroll_month_id', $this->detail_month_id)
            ->get()
            ->keyBy('payroll_week_id');

        $baseRate = (float) ($this->profile->hourly_rate ?? 70000);
        $weeksData = [];
        $totalHours = 0;
        $totalAmount = 0;

        foreach ($weeks as $week) {
            $rec = $salaries->get($week->id);
            $hours = $rec ? (float) $rec->overtime_hours : 0;
            $rate = ($rec && (float)$rec->hourly_rate > 0) ? (float)$rec->hourly_rate : $baseRate;
            $amount = ($rec && (float) $rec->net_salary > 0) ? (float) $rec->net_salary : round($hours * $rate);

            $totalHours += $hours;
            $totalAmount += $amount;
            $weeksData[] = (object) [
                'week_name' => $week->week_name,
                'hours' => $hours,
                'rate' => $rate,
                'amount' => $amount
            ];
        }

        $month = DB::table('payroll_months')->find($this->detail_month_id);
        return (object) [
            'month_name' => $month->month_name,
            'weeks' => $weeksData,
            'total_hours' => $totalHours,
            'total_amount' => $totalAmount
        ];
    }

    #[Computed]
    public function printableSalary()
    {
        if (!$this->print_month_id || !$this->profile) return null;

        $records = Salary::where('profile_id', $this->profile->id)
            ->where('payroll_month_id', $this->print_month_id)
            ->get();

        $baseRate = (float) ($this->profile->hourly_rate ?? 70000);
        $netSalary = $records->sum(function($r) use ($baseRate) {
            $rate = ($r->hourly_rate > 0) ? (float)$r->hourly_rate : $baseRate;
            return ($r->net_salary > 0) ? (float)$r->net_salary : round((float)$r->overtime_hours * $rate);
        });

        return (object) [
            'profile' => $this->profile,
            'total_hours' => $records->sum('overtime_hours'),
            'net_salary' => $netSalary,
            'status' => $records->contains('status', 'paid') ? 'پرداخت شده' : 'در جریان'
        ];
    }

    public function openPerformanceModal($id) { $this->detail_month_id = $id; \Flux\Flux::modal('performance-detail-modal')->show(); }
    public function openPrintModal($id) { $this->print_month_id = $id; \Flux\Flux::modal('print-salary-modal')->show(); }
};

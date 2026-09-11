<?php

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

    public $print_month_id = null;
    public $detail_month_id = null;

    public function mount(): void
    {
        if (! $this->profile) {
            return;
        }

        $latestYear = DB::table('payroll_years')
            ->orderByDesc('year')
            ->first();

        if ($latestYear) {
            $this->selected_year_id = $latestYear->id;
        }
    }

    #[Computed]
    public function profile()
    {
        if (! auth()->check()) {
            return null;
        }

        return Profile::query()
            ->where('user_id', auth()->id())
            ->first();
    }

    #[Computed]
    public function years()
    {
        return DB::table('payroll_years')
            ->select('id', 'year')
            ->orderByDesc('year')
            ->get();
    }

    #[Computed]
    public function months()
    {
        if (! $this->selected_year_id) {
            return collect();
        }

        return DB::table('payroll_months')
            ->where('payroll_year_id', $this->selected_year_id)
            ->select('id', 'month', 'month_name', 'payroll_year_id')
            ->orderBy('month')
            ->get();
    }

    #[Computed]
    public function salaries()
    {
        if (! $this->profile) {
            return collect();
        }

        $profileId = $this->profile->id;

        $monthlySalaries = Salary::query()
            ->where('profile_id', $profileId)
            ->get()
            ->groupBy('payroll_month_id');

        $monthsQuery = DB::table('payroll_months')
            ->join(
                'payroll_years',
                'payroll_months.payroll_year_id',
                '=',
                'payroll_years.id'
            )
            ->when(
                $this->selected_year_id,
                fn ($query) => $query->where(
                    'payroll_months.payroll_year_id',
                    $this->selected_year_id
                )
            )
            ->when(
                $this->selected_month_id,
                fn ($query) => $query->where(
                    'payroll_months.id',
                    $this->selected_month_id
                )
            )
            ->select(
                'payroll_months.id',
                'payroll_months.id as month_id',
                'payroll_months.month',
                'payroll_months.month_name',
                'payroll_years.year'
            )
            ->orderByDesc('payroll_years.year')
            ->orderByDesc('payroll_months.month');

        $paginatedMonths = $monthsQuery->paginate(15);

        return $paginatedMonths->through(
            function ($month) use ($monthlySalaries) {
                $records = $monthlySalaries->get(
                    $month->month_id,
                    collect()
                );

                $totalHours = (float) $records->sum('overtime_hours');

                $paidRecord = $records->firstWhere('status', 'paid');
                $approvedRecord = $records->firstWhere('status', 'approved');
                $pendingRecord = $records->firstWhere('status', 'pending');
                $anyRecord = $records->first();

                // تشخیص وضعیت
                $isPaid = $paidRecord !== null;
                $isIssued = ! $isPaid && ($approvedRecord !== null);

                // محاسبه مبلغ
                $netSalary = 0;
                if ($isPaid) {
                    $netSalary = (float) ($paidRecord->net_salary > 0
                        ? $paidRecord->net_salary
                        : $records->sum('net_salary'));
                } elseif ($approvedRecord) {
                    $netSalary = (float) ($approvedRecord->net_salary > 0
                        ? $approvedRecord->net_salary
                        : $records->sum('net_salary'));
                } else {
                    $netSalary = (float) $records->sum('net_salary');
                }

                // اگر همه رکوردها pending هستند اما ساعتی ثبت شده، مبلغ را محاسبه کن
                if ($netSalary <= 0 && $totalHours > 0) {
                    $netSalary = $totalHours * 70000;
                }

                $rawIssuedAt = $paidRecord?->created_at ?? $approvedRecord?->created_at ?? $anyRecord?->created_at;
                $jalaliIssuedAt = null;

                if ($rawIssuedAt) {
                    try {
                        $jalaliIssuedAt = Jalalian::fromCarbon(
                            \Illuminate\Support\Carbon::parse($rawIssuedAt)->timezone('Asia/Tehran')
                        )->format('Y/m/d - H:i');
                    } catch (\Throwable $e) {
                        $jalaliIssuedAt = \Illuminate\Support\Carbon::parse($rawIssuedAt)->timezone('Asia/Tehran')->format('Y/m/d H:i');
                    }
                }

                return (object) [
                    'id' => $month->id,
                    'month_id' => $month->month_id,
                    'month' => $month->month,
                    'month_name' => $month->month_name ?? 'ماه ' . $month->month,
                    'year' => $month->year,
                    'total_hours' => $totalHours,
                    'net_salary' => $netSalary,
                    'salary_id' => $paidRecord?->id ?? $approvedRecord?->id ?? $pendingRecord?->id ?? $anyRecord?->id,
                    'issued_at' => $rawIssuedAt,
                    'issued_at_jalali' => $jalaliIssuedAt,
                    'is_issued' => $isIssued,
                    'is_paid' => $isPaid,
                ];
            }
        );
    }

    #[Computed]
    public function printableSalary()
    {
        if (
            ! $this->print_month_id ||
            ! $this->profile
        ) {
            return null;
        }

        $profileId = $this->profile->id;

        $month = DB::table('payroll_months')
            ->join(
                'payroll_years',
                'payroll_months.payroll_year_id',
                '=',
                'payroll_years.id'
            )
            ->where('payroll_months.id', $this->print_month_id)
            ->select(
                'payroll_months.id',
                'payroll_months.id as month_id',
                'payroll_months.month',
                'payroll_months.month_name',
                'payroll_years.year'
            )
            ->first();

        if (! $month) {
            return null;
        }

        $records = Salary::query()
            ->where('profile_id', $profileId)
            ->where('payroll_month_id', $this->print_month_id)
            ->get();

        $totalHours = (float) $records->sum('overtime_hours');

        $paidRecord = $records->firstWhere('status', 'paid');
        $approvedRecord = $records->firstWhere('status', 'approved');
        $anyRecord = $records->first();

        $netSalary = 0;
        if ($paidRecord) {
            $netSalary = (float) ($paidRecord->net_salary > 0
                ? $paidRecord->net_salary
                : $records->sum('net_salary'));
        } elseif ($approvedRecord) {
            $netSalary = (float) ($approvedRecord->net_salary > 0
                ? $approvedRecord->net_salary
                : $records->sum('net_salary'));
        } else {
            $netSalary = (float) $records->sum('net_salary');
        }

        if ($netSalary <= 0 && $totalHours > 0) {
            $netSalary = $totalHours * 70000;
        }

        $isPaid = $paidRecord !== null;
        $rawIssuedAt = $paidRecord?->created_at ?? $approvedRecord?->created_at ?? $anyRecord?->created_at;
        $jalaliIssuedAt = null;

        if ($rawIssuedAt) {
            try {
                $jalaliIssuedAt = Jalalian::fromCarbon(
                    \Illuminate\Support\Carbon::parse($rawIssuedAt)->timezone('Asia/Tehran')
                )->format('Y/m/d - H:i');
            } catch (\Throwable $e) {
                $jalaliIssuedAt = \Illuminate\Support\Carbon::parse($rawIssuedAt)->timezone('Asia/Tehran')->format('Y/m/d H:i');
            }
        }

        return (object) [
            'month_name' => $month->month_name ?? 'ماه ' . $month->month,
            'year' => $month->year,
            'profile' => $this->profile,
            'total_hours' => $totalHours,
            'net_salary' => $netSalary,
            'issued_at' => $rawIssuedAt,
            'issued_at_jalali' => $jalaliIssuedAt,
            'is_paid' => $isPaid,
        ];
    }

    #[Computed]
    public function monthlyPerformanceDetails()
    {
        if (! $this->detail_month_id || ! $this->profile) {
            return null;
        }

        $profileId = $this->profile->id;

        $month = DB::table('payroll_months')
            ->join('payroll_years', 'payroll_months.payroll_year_id', '=', 'payroll_years.id')
            ->where('payroll_months.id', $this->detail_month_id)
            ->select(
                'payroll_months.id as month_id',
                'payroll_months.month',
                'payroll_months.month_name',
                'payroll_years.year'
            )
            ->first();

        if (! $month) {
            return null;
        }

        $weeks = DB::table('payroll_weeks')
            ->where('payroll_month_id', $this->detail_month_id)
            ->orderBy('id')
            ->get();

        $salaries = Salary::query()
            ->where('profile_id', $profileId)
            ->where('payroll_month_id', $this->detail_month_id)
            ->get()
            ->keyBy('payroll_week_id');

        $weekDetails = [];
        $totalHours = 0;
        $totalAmount = 0;

        foreach ($weeks as $week) {
            $salaryRecord = $salaries->get($week->id);
            $hours = $salaryRecord ? (float) $salaryRecord->overtime_hours : 0;
            $rate = $salaryRecord && (float) $salaryRecord->overtime_rate > 0
                ? (float) $salaryRecord->overtime_rate
                : 70000;
            $amount = $salaryRecord && (float) $salaryRecord->net_salary > 0
                ? (float) $salaryRecord->net_salary
                : round($hours * $rate);

            $totalHours += $hours;
            $totalAmount += $amount;

            $weekDetails[] = (object) [
                'week_name' => $week->week_name ?? ('هفته ' . $week->id),
                'hours' => $hours,
                'rate' => $rate,
                'amount' => $amount,
                'status' => $salaryRecord?->status ?? 'ثبت نشده',
            ];
        }

        return (object) [
            'month_name' => $month->month_name ?? 'ماه ' . $month->month,
            'year' => $month->year,
            'profile' => $this->profile,
            'total_hours' => $totalHours,
            'total_amount' => $totalAmount,
            'weeks' => $weekDetails,
        ];
    }

    public function openPerformanceModal(int $monthId): void
    {
        $this->detail_month_id = $monthId;
        \Flux\Flux::modal('performance-detail-modal')->show();
    }

    public function openPrintModal(int $monthId): void
    {
        $this->print_month_id = $monthId;
        \Flux\Flux::modal('print-salary-modal')->show();
    }

    public function updatedSelectedYearId(): void
    {
        $this->selected_month_id = null;
        $this->resetPage();
    }

    public function updatedSelectedMonthId(): void
    {
        $this->resetPage();
    }
};

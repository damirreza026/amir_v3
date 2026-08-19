<?php

use App\Models\Month;
use App\Models\Profile;
use App\Models\Salary;
use App\Models\Week;
use App\Models\Year;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public $selected_year_id = null;
    public $selected_month_id = null;
    public $selected_week_id = null;

    public $hourly_rate = 70000;

    public $salary_id = null;
    public $profile_id = null;
    public $profile_name = '';

    public $modal_total_hours = 0.0;
    public $total_salary = 0;

    public function mount(?Year $year = null): void
    {
        $this->selected_year_id = $year?->exists
            ? $year->id
            : Year::query()->latest('year')->value('id');

        $this->loadDefaultMonth();
    }

    public function updatedSelectedYearId(): void
    {
        $this->selected_month_id = null;
        $this->selected_week_id = null;
        $this->loadDefaultMonth();
    }

    public function updatedSelectedMonthId(): void
    {
        $this->selected_week_id = null;
        $this->loadDefaultWeek();
        unset($this->currentMonthData);
    }

    public function updatedSelectedWeekId(): void
    {
        unset($this->currentMonthData);
    }

    public function updatedHourlyRate(): void
    {
        $this->recalculateSalary();
    }

    public function updatedModalTotalHours(): void
    {
        $this->recalculateSalary();
    }

    private function loadDefaultMonth(): void
    {
        if (! $this->selected_year_id) {
            return;
        }

        $this->selected_month_id = Month::query()
            ->where('payroll_year_id', $this->selected_year_id)
            ->orderBy('month')
            ->value('id');

        $this->loadDefaultWeek();
    }

    private function loadDefaultWeek(): void
    {
        if (! $this->selected_month_id) {
            $this->selected_week_id = null;

            return;
        }

        $this->selected_week_id = Week::query()
            ->where('payroll_month_id', $this->selected_month_id)
            ->orderBy('week')
            ->value('id');
    }

    private function recalculateSalary(): void
    {
        $this->total_salary = $this->calculateSalary(
            (float) $this->modal_total_hours
        );
    }

    private function calculateSalary(float $hours): int
    {
        return (int) round(
            $hours * (float) $this->hourly_rate
        );
    }

    private function selectedWeeks()
    {
        return Week::query()
            ->where('payroll_month_id', $this->selected_month_id)
            ->orderBy('week')
            ->get();
    }

    private function profileWeeklySalaries(int $profileId)
    {
        return Salary::query()
            ->where('profile_id', $profileId)
            ->where('payroll_year_id', $this->selected_year_id)
            ->where('payroll_month_id', $this->selected_month_id)
            ->get()
            ->keyBy('payroll_week_id');
    }

    #[Computed]
    public function years()
    {
        return Year::query()
            ->orderByDesc('year')
            ->get();
    }

    #[Computed]
    public function currentYear()
    {
        return $this->selected_year_id
            ? Year::find($this->selected_year_id)
            : null;
    }

    #[Computed]
    public function months()
    {
        if (! $this->selected_year_id) {
            return collect();
        }

        return Month::query()
            ->where('payroll_year_id', $this->selected_year_id)
            ->orderBy('month')
            ->get();
    }

    #[Computed]
    public function weeks()
    {
        if (! $this->selected_month_id) {
            return collect();
        }

        return $this->selectedWeeks();
    }

    #[Computed]
    public function currentMonthData()
    {
        if (
            ! $this->selected_year_id ||
            ! $this->selected_month_id
        ) {
            return collect();
        }

        $weeks = $this->selectedWeeks();

        return Profile::query()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(function ($profile) use ($weeks) {
                $salaries = $this->profileWeeklySalaries($profile->id);

                $completedWeeks = $weeks->filter(
                    fn ($week) => $salaries->has($week->id)
                )->count();

                $totalHours = $weeks->sum(
                    fn ($week) => (float) (
                        $salaries->get($week->id)?->overtime_hours ?? 0
                    )
                );

                $isComplete = $weeks->isNotEmpty()
                    && $completedWeeks === $weeks->count();

                $isPaid = $salaries->contains(
                    fn ($salary) => $salary->status === 'paid'
                );

                $isApproved = ! $isPaid
                    && $salaries->contains(
                        fn ($salary) => $salary->status === 'approved'
                    );

                $finalSalary = $isComplete
                    ? $this->calculateSalary($totalHours)
                    : 0;

                return [
                    'profile' => $profile,
                    'weeks' => $weeks,
                    'salaries' => $salaries,
                    'completed_weeks' => $completedWeeks,
                    'total_weeks' => $weeks->count(),
                    'total_hours' => $totalHours,
                    'calculated_salary' => $finalSalary,
                    'is_complete' => $isComplete,
                    'is_approved' => $isApproved,
                    'is_paid' => $isPaid,
                ];
            });
    }

    public function openSalaryModal(int $profileId, int $weekId): void
    {
        if (
            ! $this->selected_year_id ||
            ! $this->selected_month_id ||
            ! $weekId
        ) {
            $this->addError(
                'salary_error',
                'ابتدا سال، ماه و هفته را انتخاب کنید.'
            );

            return;
        }

        $this->resetValidation();

        $profile = Profile::findOrFail($profileId);
        $week = Week::query()
            ->where('id', $weekId)
            ->where('payroll_month_id', $this->selected_month_id)
            ->firstOrFail();

        $this->profile_id = $profile->id;
        $this->profile_name = trim(
            ($profile->first_name ?? '') . ' ' .
            ($profile->last_name ?? '')
        );
        $this->selected_week_id = $week->id;

        $salary = Salary::query()
            ->where('profile_id', $profileId)
            ->where('payroll_year_id', $this->selected_year_id)
            ->where('payroll_month_id', $this->selected_month_id)
            ->where('payroll_week_id', $week->id)
            ->first();

        $this->salary_id = $salary?->id;
        $this->modal_total_hours = (float) (
            $salary?->overtime_hours ?? 0
        );
        $this->recalculateSalary();

        Flux::modal('salary-modal')->show();
    }

    public function saveSalary(): void
    {
        $this->validate([
            'selected_year_id' => 'required|exists:payroll_years,id',
            'selected_month_id' => 'required|exists:payroll_months,id',
            'selected_week_id' => 'required|exists:payroll_weeks,id',
            'profile_id' => 'required|exists:profiles,id',
            'hourly_rate' => 'required|numeric|min:0',
            'modal_total_hours' => 'required|numeric|min:0',
        ], [
            'selected_year_id.required' => 'انتخاب سال الزامی است.',
            'selected_month_id.required' => 'انتخاب ماه الزامی است.',
            'selected_week_id.required' => 'انتخاب هفته الزامی است.',
            'profile_id.required' => 'انتخاب پرسنل الزامی است.',
            'hourly_rate.required' => 'نرخ هر ساعت را وارد نمایید.',
            'hourly_rate.numeric' => 'نرخ ساعتی باید عدد باشد.',
            'modal_total_hours.required' => 'ساعات کارکرد را وارد نمایید.',
            'modal_total_hours.numeric' => 'ساعات باید عدد باشد.',
        ]);

        $hours = (float) $this->modal_total_hours;

        try {
            DB::transaction(function () use ($hours) {
                // اگر قبلاً پرداخت شده، اجازه ندهیم
                $alreadyPaid = Salary::query()
                    ->where('profile_id', $this->profile_id)
                    ->where('payroll_year_id', $this->selected_year_id)
                    ->where('payroll_month_id', $this->selected_month_id)
                    ->where('status', 'paid')
                    ->exists();

                if ($alreadyPaid) {
                    throw new \Exception(
                        'فیش حقوقی قبلاً پرداخت شده و قابل ویرایش نیست.'
                    );
                }

                // ذخیره رکورد هفته (همیشه pending)
                Salary::updateOrCreate(
                    [
                        'profile_id' => $this->profile_id,
                        'payroll_year_id' => $this->selected_year_id,
                        'payroll_month_id' => $this->selected_month_id,
                        'payroll_week_id' => $this->selected_week_id,
                    ],
                    [
                        'base_salary' => 0,
                        'overtime_hours' => $hours,
                        'overtime_amount' => 0,
                        'deduction_amount' => 0,
                        'net_salary' => 0,
                        'status' => 'pending',
                    ]
                );

                // بعد از ذخیره، اگر فیش قبلاً approved شده بود،
                // آن را به pending برگردان تا دوباره باید صادر شود
                Salary::query()
                    ->where('profile_id', $this->profile_id)
                    ->where('payroll_year_id', $this->selected_year_id)
                    ->where('payroll_month_id', $this->selected_month_id)
                    ->where('status', 'approved')
                    ->update([
                        'net_salary' => 0,
                        'status' => 'pending',
                    ]);
            });

            session()->flash(
                'success',
                'ساعات هفته با موفقیت ذخیره شد.'
            );

            Flux::modal('salary-modal')->close();

            $this->reset([
                'salary_id',
                'profile_id',
                'profile_name',
                'modal_total_hours',
                'total_salary',
            ]);

            unset($this->currentMonthData);
        } catch (\Throwable $e) {
            $this->addError(
                'salary_error',
                'خطا در ذخیره اطلاعات: ' . $e->getMessage()
            );
        }
    }

    public function issueSalary(int $profileId): void
    {
        if (
            ! $this->selected_year_id ||
            ! $this->selected_month_id
        ) {
            return;
        }

        try {
            DB::transaction(function () use ($profileId) {
                // بررسی اینکه همه هفته‌ها پر شده باشد
                $weeks = $this->selectedWeeks();
                $salaries = $this->profileWeeklySalaries($profileId);

                $isComplete = $weeks->isNotEmpty()
                    && $weeks->every(
                        fn ($week) => $salaries->has($week->id)
                    );

                if (! $isComplete) {
                    throw new \Exception(
                        'هنوز همه هفته‌های این ماه تکمیل نشده است.'
                    );
                }

                $totalHours = $weeks->sum(
                    fn ($week) => (float) (
                        $salaries->get($week->id)?->overtime_hours ?? 0
                    )
                );

                $finalSalary = $this->calculateSalary($totalHours);

                // همه رکوردهای ماه را pending کن
                Salary::query()
                    ->where('profile_id', $profileId)
                    ->where('payroll_year_id', $this->selected_year_id)
                    ->where('payroll_month_id', $this->selected_month_id)
                    ->update([
                        'net_salary' => 0,
                        'status' => 'pending',
                    ]);

                // یک رکورد (هفته اول) را approved با مبلغ کامل کن
                $firstWeek = $weeks->first();
                Salary::query()
                    ->where('profile_id', $profileId)
                    ->where('payroll_year_id', $this->selected_year_id)
                    ->where('payroll_month_id', $this->selected_month_id)
                    ->where('payroll_week_id', $firstWeek->id)
                    ->update([
                        'base_salary' => $finalSalary,
                        'net_salary' => $finalSalary,
                        'status' => 'approved',
                    ]);
            });

            session()->flash(
                'success',
                'فیش حقوقی با موفقیت صادر شد.'
            );

            unset($this->currentMonthData);
        } catch (\Throwable $e) {
            $this->addError(
                'salary_error',
                'خطا در صدور فیش: ' . $e->getMessage()
            );
        }
    }

    public function markAsPaid(int $profileId): void
    {
        if (
            ! $this->selected_year_id ||
            ! $this->selected_month_id
        ) {
            return;
        }

        try {
            DB::transaction(function () use ($profileId) {
                // مبلغ فیش نهایی را از رکورد approved می‌خوانیم
                $finalSalary = (float) Salary::query()
                    ->where('profile_id', $profileId)
                    ->where('payroll_year_id', $this->selected_year_id)
                    ->where('payroll_month_id', $this->selected_month_id)
                    ->where('status', 'approved')
                    ->value('net_salary');

                if ($finalSalary <= 0) {
                    $finalSalary = (float) Salary::query()
                        ->where('profile_id', $profileId)
                        ->where('payroll_year_id', $this->selected_year_id)
                        ->where('payroll_month_id', $this->selected_month_id)
                        ->sum('net_salary');
                }

                // همه رکوردهای ماه را paid و با مبلغ فیش نهایی کن
                Salary::query()
                    ->where('profile_id', $profileId)
                    ->where('payroll_year_id', $this->selected_year_id)
                    ->where('payroll_month_id', $this->selected_month_id)
                    ->update([
                        'net_salary' => $finalSalary,
                        'status' => 'paid',
                    ]);
            });

            session()->flash(
                'success',
                'فیش حقوقی با موفقیت پرداخت شد.'
            );

            unset($this->currentMonthData);
        } catch (\Throwable $e) {
            $this->addError(
                'salary_error',
                'خطا در پرداخت: ' . $e->getMessage()
            );
        }
    }
};

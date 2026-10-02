<?php

namespace App\Livewire\Salary;

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

    public $search = '';

    public $salary_id = null;
    public $profile_id = null;
    public $profile_name = '';
    public $is_record_locked = false;

    // متغیرهای فرم ثبت هفته
    public $modal_total_hours = 0.0;
    public $modal_hourly_rate = 70000;
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

    public function updatedSearch(): void
    {
        unset($this->currentMonthData);
    }

    public function updatedModalHourlyRate(): void
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
        $this->total_salary = (int) round(
            (float) $this->modal_total_hours * (float) $this->modal_hourly_rate
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
        if (! $this->selected_year_id || ! $this->selected_month_id) {
            return collect();
        }

        $weeks = $this->selectedWeeks();
        $searchTerms = array_filter(explode(' ', trim($this->search)));

        return Profile::query()
            ->when(! empty($searchTerms), function ($query) use ($searchTerms) {
                $query->where(function ($q) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $q->where(function ($sub) use ($term) {
                            $sub->where('first_name', 'like', "%{$term}%")
                                ->orWhere('last_name', 'like', "%{$term}%");
                        });
                    }
                });
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(function ($profile) use ($weeks) {
                $salaries = $this->profileWeeklySalaries($profile->id);

                $completedWeeks = $weeks->filter(
                    fn ($week) => $salaries->has($week->id) && (float)($salaries->get($week->id)->overtime_hours ?? 0) >= 0
                )->count();

                $totalHours = $weeks->sum(
                    fn ($week) => (float) ($salaries->get($week->id)?->overtime_hours ?? 0)
                );

                $calculatedMonthSalary = $weeks->sum(function ($week) use ($salaries, $profile) {
                    $sal = $salaries->get($week->id);
                    if (! $sal) return 0;
                    $h = (float) ($sal->overtime_hours ?? 0);
                    $r = (float) ($sal->hourly_rate > 0 ? $sal->hourly_rate : ($profile->hourly_rate ?? 70000));
                    return (int) round($h * $r);
                });

                $isComplete = $weeks->isNotEmpty() && $completedWeeks === $weeks->count();

                $isPaid = $salaries->contains(
                    fn ($salary) => $salary->status === 'paid'
                );

                $isApproved = ! $isPaid && $salaries->contains(
                        fn ($salary) => $salary->status === 'approved'
                    );

                $finalSalary = $isComplete ? $calculatedMonthSalary : 0;

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
        if (! $this->selected_year_id || ! $this->selected_month_id || ! $weekId) {
            $this->addError('salary_error', 'ابتدا سال، ماه و هفته را انتخاب کنید.');
            return;
        }

        $this->resetValidation();

        $profile = Profile::findOrFail($profileId);
        $week = Week::query()
            ->where('id', $weekId)
            ->where('payroll_month_id', $this->selected_month_id)
            ->firstOrFail();

        $this->profile_id = $profile->id;
        $this->profile_name = trim(($profile->first_name ?? '') . ' ' . ($profile->last_name ?? ''));
        $this->selected_week_id = $week->id;

        $salary = Salary::query()
            ->where('profile_id', $profileId)
            ->where('payroll_year_id', $this->selected_year_id)
            ->where('payroll_month_id', $this->selected_month_id)
            ->where('payroll_week_id', $week->id)
            ->first();

        $alreadyPaidOrApproved = Salary::query()
            ->where('profile_id', $profileId)
            ->where('payroll_year_id', $this->selected_year_id)
            ->where('payroll_month_id', $this->selected_month_id)
            ->whereIn('status', ['paid', 'approved'])
            ->exists();

        $this->is_record_locked = $alreadyPaidOrApproved;
        $this->salary_id = $salary?->id;
        $this->modal_total_hours = (float) ($salary?->overtime_hours ?? 0);
        $this->modal_hourly_rate = (float) ($salary?->hourly_rate > 0 ? $salary->hourly_rate : ($profile->hourly_rate ?? 70000));

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
            'modal_hourly_rate' => 'required|numeric|min:0',
            'modal_total_hours' => 'required|numeric|min:0',
        ], [
            'selected_year_id.required' => 'انتخاب سال الزامی است.',
            'selected_month_id.required' => 'انتخاب ماه الزامی است.',
            'selected_week_id.required' => 'انتخاب هفته الزامی است.',
            'profile_id.required' => 'انتخاب پرسنل الزامی است.',
            'modal_hourly_rate.required' => 'نرخ هر ساعت را وارد نمایید.',
            'modal_hourly_rate.numeric' => 'نرخ ساعتی باید عدد باشد.',
            'modal_total_hours.required' => 'ساعات کارکرد را وارد نمایید.',
            'modal_total_hours.numeric' => 'ساعات باید عدد باشد.',
        ]);

        $hours = (float) $this->modal_total_hours;
        $rate = (float) $this->modal_hourly_rate;
        $amount = (int) round($hours * $rate);

        try {
            DB::transaction(function () use ($hours, $rate, $amount) {
                $alreadyPaid = Salary::query()
                    ->where('profile_id', $this->profile_id)
                    ->where('payroll_year_id', $this->selected_year_id)
                    ->where('payroll_month_id', $this->selected_month_id)
                    ->where('status', 'paid')
                    ->exists();

                if ($alreadyPaid) {
                    throw new \Exception('فیش حقوقی قبلاً پرداخت شده و قابل ویرایش نیست.');
                }

                Salary::updateOrCreate(
                    [
                        'profile_id' => $this->profile_id,
                        'payroll_year_id' => $this->selected_year_id,
                        'payroll_month_id' => $this->selected_month_id,
                        'payroll_week_id' => $this->selected_week_id,
                    ],
                    [
                        'base_salary' => $amount,
                        'hourly_rate' => $rate,
                        'overtime_hours' => $hours,
                        'overtime_amount' => 0,
                        'deduction_amount' => 0,
                        'net_salary' => $amount,
                        'status' => 'pending',
                    ]
                );

                Salary::query()
                    ->where('profile_id', $this->profile_id)
                    ->where('payroll_year_id', $this->selected_year_id)
                    ->where('payroll_month_id', $this->selected_month_id)
                    ->where('status', 'approved')
                    ->update([
                        'status' => 'pending',
                    ]);
            });

            session()->flash('success', 'اطلاعات ساعت کارکرد و نرخ هفتگی ذخیره شد.');

            Flux::modal('salary-modal')->close();

            $this->reset([
                'salary_id',
                'profile_id',
                'profile_name',
                'modal_total_hours',
                'modal_hourly_rate',
                'total_salary',
                'is_record_locked',
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
        if (! $this->selected_year_id || ! $this->selected_month_id) {
            return;
        }

        try {
            DB::transaction(function () use ($profileId) {
                $weeks = $this->selectedWeeks();
                $salaries = $this->profileWeeklySalaries($profileId);

                $isComplete = $weeks->isNotEmpty()
                    && $weeks->every(fn ($week) => $salaries->has($week->id));

                if (! $isComplete) {
                    throw new \Exception('هنوز همه هفته‌های این ماه تکمیل نشده است.');
                }

                Salary::query()
                    ->where('profile_id', $profileId)
                    ->where('payroll_year_id', $this->selected_year_id)
                    ->where('payroll_month_id', $this->selected_month_id)
                    ->update([
                        'status' => 'approved',
                    ]);
            });

            session()->flash('success', 'فیش حقوقی ماه با موفقیت صادر گردید.');
            unset($this->currentMonthData);
        } catch (\Throwable $e) {
            $this->addError('salary_error', 'خطا در صدور فیش: ' . $e->getMessage());
        }
    }

    public function markAsPaid(int $profileId): void
    {
        if (! $this->selected_year_id || ! $this->selected_month_id) {
            return;
        }

        try {
            DB::transaction(function () use ($profileId) {
                Salary::query()
                    ->where('profile_id', $profileId)
                    ->where('payroll_year_id', $this->selected_year_id)
                    ->where('payroll_month_id', $this->selected_month_id)
                    ->update([
                        'status' => 'paid',
                    ]);
            });

            session()->flash('success', 'پرداخت فیش حقوقی ثبت شد.');
            unset($this->currentMonthData);
        } catch (\Throwable $e) {
            $this->addError('salary_error', 'خطا در پرداخت: ' . $e->getMessage());
        }
    }
};

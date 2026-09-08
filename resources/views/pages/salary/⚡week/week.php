<?php

use App\Models\Month;
use App\Models\Week;
use App\Models\Year;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public Year $year;

    public $selected_month_id = null;

    public $week = '';

    public $week_name = '';

    public $week_id = null;

    public array $weekNames = [
        1 => 'هفته اول',
        2 => 'هفته دوم',
        3 => 'هفته سوم',
        4 => 'هفته چهارم',
        5 => 'هفته پنجم',
    ];

    public function mount(Year $year): void
    {
        $this->year = $year;

        $monthIdFromRequest = request()->get('month');

        if ($monthIdFromRequest && Month::where('id', $monthIdFromRequest)->where('payroll_year_id', $year->id)->exists()) {
            $this->selected_month_id = (int) $monthIdFromRequest;
        } else {
            $firstMonth = Month::query()
                ->where('payroll_year_id', $year->id)
                ->orderBy('month')
                ->first();

            if ($firstMonth) {
                $this->selected_month_id = $firstMonth->id;
            }
        }
    }

    #[Computed]
    public function currentMonth()
    {
        if (! $this->selected_month_id) {
            return null;
        }

        return Month::where('id', $this->selected_month_id)
            ->where('payroll_year_id', $this->year->id)
            ->first();
    }

    public function getWeeksProperty()
    {
        if (! $this->selected_month_id) {
            return Week::query()
                ->whereRaw('1 = 0')
                ->paginate(15);
        }

        return Week::query()
            ->where('payroll_month_id', $this->selected_month_id)
            ->orderBy('week')
            ->paginate(15);
    }

    public function updatedWeek($value): void
    {
        $weekNumber = (int) $value;

        if ($weekNumber && isset($this->weekNames[$weekNumber])) {
            $this->week_name = $this->weekNames[$weekNumber];
        }
    }

    public function resetForm(): void
    {
        $this->week = '';
        $this->week_name = '';
        $this->week_id = null;

        $this->resetValidation();
    }

    public function openSaveModal(): void
    {
        $this->resetForm();
        Flux::modal('save-week')->show();
    }

    public function save(): void
    {
        $this->validate([
            'selected_month_id' => [
                'required',
                'integer',
                Rule::exists('payroll_months', 'id')
                    ->where(function ($query) {
                        $query->where(
                            'payroll_year_id',
                            $this->year->id
                        );
                    }),
            ],

            'week' => [
                'required',
                'integer',
                'min:1',
                'max:5',
                Rule::unique('payroll_weeks', 'week')
                    ->where(function ($query) {
                        $query->where(
                            'payroll_month_id',
                            $this->selected_month_id
                        );
                    }),
            ],
            'week_name' => ['required', 'string', 'max:255'],
        ], [
            'selected_month_id.required' => 'ماه معتبر یافت نشد.',
            'selected_month_id.exists' => 'ماه انتخاب‌شده معتبر نیست.',
            'week.required' => 'لطفاً هفته را انتخاب کنید.',
            'week.integer' => 'شماره هفته باید عدد باشد.',
            'week.min' => 'شماره هفته باید بین ۱ تا ۵ باشد.',
            'week.max' => 'شماره هفته باید بین ۱ تا ۵ باشد.',
            'week.unique' => 'این هفته قبلاً برای این ماه ثبت شده است.',
            'week_name.required' => 'نام هفته الزامی است.',
        ]);

        $weekNumber = (int) $this->week;

        try {
            DB::transaction(function () use ($weekNumber) {
                Week::create([
                    'payroll_month_id' => $this->selected_month_id,
                    'week' => $weekNumber,
                    'week_name' => $this->week_name ?: $this->weekNames[$weekNumber],
                ]);
            });

            session()->flash('success', 'هفته با موفقیت ثبت شد.');

            $this->resetForm();

            Flux::modal('save-week')->close();
        } catch (\Throwable $e) {
            $this->addError(
                'save_error',
                'خطا در ثبت هفته: ' . $e->getMessage()
            );
        }
    }

    public function edit(int $weekId): void
    {
        $week = Week::query()
            ->where('id', $weekId)
            ->where('payroll_month_id', $this->selected_month_id)
            ->firstOrFail();

        $this->week_id = $week->id;
        $this->week = (string) $week->week;
        $this->week_name = $week->week_name;

        $this->resetValidation();

        Flux::modal('edit-week')->show();
    }

    public function update(): void
    {
        $this->validate([
            'selected_month_id' => [
                'required',
                'integer',
                Rule::exists('payroll_months', 'id')
                    ->where(function ($query) {
                        $query->where(
                            'payroll_year_id',
                            $this->year->id
                        );
                    }),
            ],

            'week' => [
                'required',
                'integer',
                'min:1',
                'max:5',
                Rule::unique('payroll_weeks', 'week')
                    ->where(function ($query) {
                        $query->where(
                            'payroll_month_id',
                            $this->selected_month_id
                        );
                    })
                    ->ignore($this->week_id),
            ],
            'week_name' => ['required', 'string', 'max:255'],
        ], [
            'selected_month_id.required' => 'ماه معتبر یافت نشد.',
            'selected_month_id.exists' => 'ماه انتخاب‌شده معتبر نیست.',
            'week.required' => 'لطفاً هفته را انتخاب کنید.',
            'week.integer' => 'شماره هفته باید عدد باشد.',
            'week.min' => 'شماره هفته باید بین ۱ تا ۵ باشد.',
            'week.max' => 'شماره هفته باید بین ۱ تا ۵ باشد.',
            'week.unique' => 'این هفته قبلاً برای این ماه ثبت شده است.',
            'week_name.required' => 'نام هفته الزامی است.',
        ]);

        $weekNumber = (int) $this->week;

        try {
            $week = Week::query()
                ->where('id', $this->week_id)
                ->where('payroll_month_id', $this->selected_month_id)
                ->firstOrFail();

            $week->update([
                'week' => $weekNumber,
                'week_name' => $this->week_name,
            ]);

            session()->flash('success', 'هفته با موفقیت ویرایش شد.');

            $this->resetForm();

            Flux::modal('edit-week')->close();
        } catch (\Throwable $e) {
            $this->addError(
                'update_error',
                'خطا در ویرایش هفته: ' . $e->getMessage()
            );
        }
    }

    public function deleteForm(int $weekId): void
    {
        $week = Week::query()
            ->where('id', $weekId)
            ->where('payroll_month_id', $this->selected_month_id)
            ->firstOrFail();

        $this->week_id = $week->id;
        $this->week = (string) $week->week;
        $this->week_name = $week->week_name;

        Flux::modal('delete-week')->show();
    }

    public function delete(): void
    {
        try {
            Week::query()
                ->where('id', $this->week_id)
                ->where('payroll_month_id', $this->selected_month_id)
                ->delete();

            session()->flash('success', 'هفته با موفقیت حذف شد.');

            $this->resetForm();

            Flux::modal('delete-week')->close();
        } catch (\Throwable $e) {
            session()->flash(
                'error',
                'خطا در حذف هفته: ' . $e->getMessage()
            );
        }
    }
};

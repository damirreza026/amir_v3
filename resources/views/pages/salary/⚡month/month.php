<?php

use App\Models\Month;
use App\Models\Year;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public Year $year;

    public int $year_id;

    public $month = '';

    public $month_id = null;

    public $month_name = '';

    public $sortBy = 'month';

    public $sortDirection = 'desc';

    public array $monthNames = [
        1 => 'فروردین',
        2 => 'اردیبهشت',
        3 => 'خرداد',
        4 => 'تیر',
        5 => 'مرداد',
        6 => 'شهریور',
        7 => 'مهر',
        8 => 'آبان',
        9 => 'آذر',
        10 => 'دی',
        11 => 'بهمن',
        12 => 'اسفند',
    ];

    public function mount(Year $year): void
    {
        $this->year = $year;
        $this->year_id = $year->id;
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    #[Computed]
    public function months()
    {
        return Month::query()
            ->where('payroll_year_id', $this->year_id)
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(15);
    }

    public function openSaveModal(): void
    {
        $this->reset_data();
        Flux::modal('save')->show();
    }

    public function reset_data(): void
    {
        $this->month = '';
        $this->month_id = null;
        $this->month_name = '';

        $this->resetValidation();
    }

    protected function monthRules(): array
    {
        return [
            'month' => [
                'required',
                'integer',
                'min:1',
                'max:12',
                Rule::unique('payroll_months', 'month')
                    ->where(fn ($query) => $query->where('payroll_year_id', $this->year_id))
                    ->ignore($this->month_id),
            ],
        ];
    }

    protected function monthMessages(): array
    {
        return [
            'month.required' => 'انتخاب ماه الزامی است.',
            'month.integer' => 'ماه باید عدد باشد.',
            'month.min' => 'ماه باید بین ۱ تا ۱۲ باشد.',
            'month.max' => 'ماه باید بین ۱ تا ۱۲ باشد.',
            'month.unique' => 'این ماه قبلاً برای این سال ثبت شده است.',
        ];
    }

    public function save(): void
    {
        $this->validate($this->monthRules(), $this->monthMessages());

        $monthNumber = (int) $this->month;

        try {
            DB::transaction(function () use ($monthNumber) {
                Month::create([
                    'payroll_year_id' => $this->year_id,
                    'month' => $monthNumber,
                    'month_name' => $this->monthNames[$monthNumber],
                ]);
            });

            session()->flash('success', 'ماه جدید با موفقیت ثبت شد.');
            Flux::modal('save')->close();
            $this->reset_data();
        } catch (\Throwable $e) {
            $this->addError('save_error', 'خطایی در ثبت اطلاعات رخ داد: ' . $e->getMessage());
        }
    }

    public function edit(int $monthId): void
    {
        $this->resetValidation();

        $month = Month::query()
            ->where('payroll_year_id', $this->year_id)
            ->where('id', $monthId)
            ->firstOrFail();

        $this->month_id = $month->id;
        $this->month = (string) $month->month;
        $this->month_name = $month->month_name ?: ($this->monthNames[$month->month] ?? '');

        Flux::modal('edit-month')->show();
    }

    public function update(): void
    {
        $this->validate($this->monthRules(), $this->monthMessages());

        $monthNumber = (int) $this->month;

        try {
            DB::transaction(function () use ($monthNumber) {
                $month = Month::query()
                    ->where('payroll_year_id', $this->year_id)
                    ->where('id', $this->month_id)
                    ->firstOrFail();

                $month->update([
                    'month' => $monthNumber,
                    'month_name' => $this->monthNames[$monthNumber],
                ]);
            });

            session()->flash('success', 'ماه با موفقیت ویرایش شد.');
            Flux::modal('edit-month')->close();
            $this->reset_data();
        } catch (\Throwable $e) {
            $this->addError('update_error', 'خطایی در ویرایش اطلاعات رخ داد: ' . $e->getMessage());
        }
    }

    public function delete_form(int $monthId): void
    {
        $this->resetValidation();

        $month = Month::query()
            ->where('payroll_year_id', $this->year_id)
            ->where('id', $monthId)
            ->firstOrFail();

        $this->month_id = $month->id;
        $this->month = (string) $month->month;
        $this->month_name = $month->month_name ?: ($this->monthNames[$month->month] ?? '');

        Flux::modal('delete-month')->show();
    }

    public function delete(): void
    {
        try {
            DB::transaction(function () {
                Month::query()
                    ->where('payroll_year_id', $this->year_id)
                    ->where('id', $this->month_id)
                    ->delete();
            });

            session()->flash('success', 'ماه با موفقیت حذف شد.');
            Flux::modal('delete-month')->close();
            $this->reset_data();
        } catch (\Throwable $e) {
            session()->flash('error', 'خطا در حذف ماه: ' . $e->getMessage());
            Flux::modal('delete-month')->close();
        }
    }
};

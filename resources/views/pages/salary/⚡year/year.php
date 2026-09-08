<?php

namespace App\Livewire\Salary;

use App\Models\Month;
use App\Models\Year;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public $year = '';
    public $year_id = null;
    public $search = '';

    public $sortBy = 'year';
    public $sortDirection = 'desc';

    protected $persianMonths = [
        ['number' => 1, 'name' => 'فروردین'],
        ['number' => 2, 'name' => 'اردیبهشت'],
        ['number' => 3, 'name' => 'خرداد'],
        ['number' => 4, 'name' => 'تیر'],
        ['number' => 5, 'name' => 'مرداد'],
        ['number' => 6, 'name' => 'شهریور'],
        ['number' => 7, 'name' => 'مهر'],
        ['number' => 8, 'name' => 'آبان'],
        ['number' => 9, 'name' => 'آذر'],
        ['number' => 10, 'name' => 'دی'],
        ['number' => 11, 'name' => 'بهمن'],
        ['number' => 12, 'name' => 'اسفند'],
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function sort($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function reset_data()
    {
        $this->reset(['year', 'year_id']);
        $this->resetValidation();
    }

    public function openAddModal()
    {
        $this->reset_data();
        \Flux\Flux::modal('add-year')->show();
    }

    #[Computed]
    public function years()
    {
        $query = Year::query();

        if (!empty(trim($this->search))) {
            $query->where('year', 'like', '%' . trim($this->search) . '%');
        }

        return $query
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);
    }

    public function save()
    {
        $this->validate([
            'year' => ['required', 'digits:4', 'unique:payroll_years,year'],
        ], [
            'year.required' => 'وارد کردن سال الزامی است.',
            'year.digits'   => 'سال باید یک عدد ۴ رقمی باشد (مثلاً ۱۴۰۳).',
            'year.unique'   => 'این سال مالی قبلاً ثبت شده است.',
        ]);

        try {
            DB::transaction(function () {
                $yearRecord = Year::create([
                    'year' => (int) $this->year,
                ]);

                $monthsData = [];
                foreach ($this->persianMonths as $month) {
                    $monthsData[] = [
                        'payroll_year_id' => $yearRecord->id,
                        'month'           => $month['number'],
                        'month_name'      => $month['name'],
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ];
                }

                Month::insert($monthsData);
            });

            session()->flash('success', 'سال مالی جدید همراه با ۱۲ ماه با موفقیت ایجاد شد.');
            \Flux\Flux::modal('add-year')->close();
            $this->reset_data();

        } catch (\Throwable $e) {
            $this->addError('save_error', 'خطایی در ثبت سال رخ داد: ' . $e->getMessage());
        }
    }

    public function delete_form(int $yearId)
    {
        $yearRecord = Year::findOrFail($yearId);
        $this->year_id = $yearRecord->id;
        $this->year = (string) $yearRecord->year;

        \Flux\Flux::modal('delete-year')->show();
    }

    public function delete()
    {
        try {
            DB::transaction(function () {
                Month::where('payroll_year_id', $this->year_id)->delete();
                Year::destroy($this->year_id);
            });

            session()->flash('success', 'سال مالی و ماه‌های مرتبط با آن حذف گردید.');
            \Flux\Flux::modal('delete-year')->close();
            $this->reset_data();

        } catch (\Throwable $e) {
            session()->flash('error', 'خطا در حذف سال مالی: ' . $e->getMessage());
            \Flux\Flux::modal('delete-year')->close();
        }
    }
};

<?php

namespace App\Livewire\Salary;

use App\Models\Month;
use App\Models\Year;
use Flux\Flux;
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
        Flux::modal('add-year')->show();
    }

    #[Computed]
    public function years()
    {
        $query = Year::query();

        if (! empty(trim($this->search))) {
            $query->where('year', 'like', '%'.trim($this->search).'%');
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
            'year.digits' => 'سال باید یک عدد ۴ رقمی باشد (مثلاً ۱۴۰۳).',
            'year.unique' => 'این سال مالی قبلاً ثبت شده است.',
        ]);

        try {
            DB::transaction(function () {
                // ۱. ایجاد سال مالی
                $yearRecord = Year::create([
                    'year' => (int) $this->year,
                ]);

                foreach ($this->persianMonths as $pMonth) {
                    // ۲. ایجاد ماه
                    $monthRecord = Month::create([
                        'payroll_year_id' => $yearRecord->id,
                        'month' => $pMonth['number'],
                        'month_name' => $pMonth['name'],
                    ]);

                    // ۳. محاسبه تعداد هفته (۶ ماه اول ۵ هفته، ۶ ماه دوم ۴ هفته)
                    $weekCount = ($pMonth['number'] <= 6) ? 5 : 4;

                    $weeksData = [];
                    for ($i = 1; $i <= $weekCount; $i++) {
                        $weeksData[] = [
                            'payroll_month_id' => $monthRecord->id,
                            'week' => $i,
                            'week_name' => 'هفته '.$this->getPersianWeekName($i),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    // ۴. درج دسته‌جمعی هفته‌ها برای این ماه
                    DB::table('payroll_weeks')->insert($weeksData);
                }
            });

            session()->flash('success', 'سال مالی، ۱۲ ماه و تمامی هفته‌ها (۶ ماه اول ۵ هفته، ۶ ماه دوم ۴ هفته) با موفقیت ایجاد شدند.');
            Flux::modal('add-year')->close();
            $this->reset_data();

        } catch (\Throwable $e) {
            $this->addError('save_error', 'خطایی در ثبت سال رخ داد: '.$e->getMessage());
        }
    }

    private function getPersianWeekName($number)
    {
        $names = [1 => 'اول', 2 => 'دوم', 3 => 'سوم', 4 => 'چهارم', 5 => 'پنجم'];

        return $names[$number] ?? $number;
    }

    public function delete_form(int $yearId)
    {
        $yearRecord = Year::findOrFail($yearId);
        $this->year_id = $yearRecord->id;
        $this->year = (string) $yearRecord->year;

        Flux::modal('delete-year')->show();
    }

    public function delete()
    {
        try {
            DB::transaction(function () {
                // ابتدا هفته‌ها باید حذف شوند (اگر کلید خارجی Restrict باشد)
                $monthIds = Month::where('payroll_year_id', $this->year_id)->pluck('id');
                DB::table('payroll_weeks')->whereIn('payroll_month_id', $monthIds)->delete();

                Month::where('payroll_year_id', $this->year_id)->delete();
                Year::destroy($this->year_id);
            });

            session()->flash('success', 'سال مالی، ماه‌ها و تمامی هفته‌های مرتبط با آن حذف گردید.');
            Flux::modal('delete-year')->close();
            $this->reset_data();

        } catch (\Throwable $e) {
            session()->flash('error', 'خطا در حذف سال مالی: '.$e->getMessage());
            Flux::modal('delete-year')->close();
        }
    }
};

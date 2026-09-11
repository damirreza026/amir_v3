<?php

use App\Models\Category;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Morilog\Jalali\Jalalian;

new class extends Component
{
    use WithPagination;

    // متغیرهای فرم
    public string $name = '';

    public ?int $cat_id = null;

    // متغیر جست‌وجوی زنده
    public string $search = '';

    // متغیرهای فیلتر آبشاری تاریخ شمسی
    public string $selected_year = '';

    public string $selected_month = '';

    public string $selected_day = '';

    // متغیرهای مرتب‌سازی جدول
    public string $sortBy = 'name';

    public string $sortDirection = 'asc';

    // نام ماه‌های شمسی جهت نمایش
    public array $persianMonths = [
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

    // ریست صفحه‌بندی هنگام تغییر عبارت جست‌وجو
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedYear(): void
    {
        $this->selected_month = '';
        $this->selected_day = '';
        $this->resetPage();
    }

    public function updatedSelectedMonth(): void
    {
        $this->selected_day = '';
        $this->resetPage();
    }

    public function updatedSelectedDay(): void
    {
        $this->resetPage();
    }

    public function clearDateFilters(): void
    {
        $this->reset(['selected_year', 'selected_month', 'selected_day']);
        $this->resetPage();
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

    /**
     * استخراج تمام تاریخ‌های متمایز و تبدیل امن آن‌ها به تقویم جلالی (مطابق منطق کامپوننت پروفایل)
     */
    #[Computed]
    public function distinctJalaliDates()
    {
        $dates = Category::query()
            ->select(DB::raw('DATE(created_at) as created_date'))
            ->whereNotNull('created_at')
            ->groupBy('created_date')
            ->orderBy('created_date', 'desc')
            ->pluck('created_date');

        $result = [];
        foreach ($dates as $dateStr) {
            try {
                $carbon = Carbon::parse($dateStr);
                $jalali = Jalalian::fromCarbon($carbon);
                $result[] = [
                    'year' => (int) $jalali->getYear(),
                    'month' => (int) $jalali->getMonth(),
                    'day' => (int) $jalali->getDay(),
                ];
            } catch (\Throwable $e) {
                continue;
            }
        }

        return collect($result);
    }

    #[Computed]
    public function availableYears(): array
    {
        return $this->distinctJalaliDates
            ->pluck('year')
            ->unique()
            ->sortDesc()
            ->values()
            ->all();
    }

    #[Computed]
    public function availableMonths(): array
    {
        if (empty($this->selected_year)) {
            return [];
        }

        return $this->distinctJalaliDates
            ->where('year', (int) $this->selected_year)
            ->pluck('month')
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    #[Computed]
    public function availableDays(): array
    {
        if (empty($this->selected_year) || empty($this->selected_month)) {
            return [];
        }

        return $this->distinctJalaliDates
            ->where('year', (int) $this->selected_year)
            ->where('month', (int) $this->selected_month)
            ->pluck('day')
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    #[Computed]
    public function categories()
    {
        $query = Category::query()->with('user.profile');

        if (filled(trim($this->search))) {
            $query->where('name', 'like', '%'.trim($this->search).'%');
        }

        // اعمال فیلتر آبشاری تاریخ شمسی
        if (! empty($this->selected_year)) {
            $year = (int) $this->selected_year;

            if (! empty($this->selected_month) && ! empty($this->selected_day)) {
                $month = (int) $this->selected_month;
                $day = (int) $this->selected_day;

                $startGDate = (new Jalalian($year, $month, $day, 0, 0, 0))->toCarbon();
                $endGDate = (new Jalalian($year, $month, $day, 23, 59, 59))->toCarbon();

                $query->whereBetween('created_at', [$startGDate, $endGDate]);
            } elseif (! empty($this->selected_month)) {
                $month = (int) $this->selected_month;
                $daysInMonth = ($month <= 6) ? 31 : (($month <= 11) ? 30 : 29);

                $startGDate = (new Jalalian($year, $month, 1, 0, 0, 0))->toCarbon();
                $endGDate = (new Jalalian($year, $month, $daysInMonth, 23, 59, 59))->toCarbon();

                $query->whereBetween('created_at', [$startGDate, $endGDate]);
            } else {
                $startGDate = (new Jalalian($year, 1, 1, 0, 0, 0))->toCarbon();
                $endGDate = (new Jalalian($year, 12, 29, 23, 59, 59))->toCarbon();

                $query->whereBetween('created_at', [$startGDate, $endGDate]);
            }
        }

        return $query
            ->tap(fn ($q) => $this->sortBy ? $q->orderBy($this->sortBy, $this->sortDirection) : $q)
            ->paginate(15);
    }

    // متد باز کردن مودال ثبت دسته‌بندی جدید
    public function openSaveModal(): void
    {
        $this->reset_data();
        Flux::modal('save-category')->show();
    }

    // پاک‌سازی فرم و خطاها
    public function reset_data(): void
    {
        $this->reset(['name', 'cat_id']);
        $this->resetValidation();
    }

    // ثبت دسته‌بندی جدید
    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:100', 'unique:categories,name'],
        ], [
            'name.required' => 'وارد کردن نام دسته‌بندی الزامی است.',
            'name.string' => 'نام دسته‌بندی باید متنی باشد.',
            'name.min' => 'نام دسته‌بندی باید حداقل ۲ کاراکتر باشد.',
            'name.max' => 'نام دسته‌بندی نباید بیشتر از ۱۰۰ کاراکتر باشد.',
            'name.unique' => 'این نام دسته‌بندی قبلاً ثبت شده است.',
        ]);

        try {
            DB::transaction(function () {
                Category::create([
                    'user_id' => Auth::id(),
                    'name' => trim($this->name),
                ]);
            });

            session()->flash('success', 'دسته‌بندی جدید با موفقیت ثبت شد.');
            Flux::modal('save-category')->close();
            $this->reset_data();

        } catch (\Throwable $e) {
            $this->addError('save_error', 'خطایی در ثبت اطلاعات رخ داد: '.$e->getMessage());
        }
    }

    // باز کردن مودال ویرایش
    public function edit(int $categoryId): void
    {
        $this->reset_data();

        $category = Category::findOrFail($categoryId);
        $this->cat_id = $category->id;
        $this->name = $category->name;

        Flux::modal('edit-category')->show();
    }

    // ذخیره تغییرات ویرایش
    public function update(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:100', 'unique:categories,name,'.$this->cat_id],
        ], [
            'name.required' => 'وارد کردن نام دسته‌بندی الزامی است.',
            'name.string' => 'نام دسته‌بندی باید متنی باشد.',
            'name.min' => 'نام دسته‌بندی باید حداقل ۲ کاراکتر باشد.',
            'name.max' => 'نام دسته‌بندی نباید بیشتر از ۱۰۰ کاراکتر باشد.',
            'name.unique' => 'این نام دسته‌بندی قبلاً ثبت شده است.',
        ]);

        try {
            DB::transaction(function () {
                $category = Category::findOrFail($this->cat_id);
                $category->name = trim($this->name);
                $category->save();
            });

            session()->flash('success', 'دسته‌بندی با موفقیت ویرایش شد.');
            Flux::modal('edit-category')->close();
            $this->reset_data();

        } catch (\Throwable $e) {
            $this->addError('update_error', 'خطایی در ویرایش اطلاعات رخ داد: '.$e->getMessage());
        }
    }

    // باز کردن مودال حذف
    public function delete_form(int $categoryId): void
    {
        $this->reset_data();

        $category = Category::findOrFail($categoryId);
        $this->cat_id = $category->id;
        $this->name = $category->name;

        Flux::modal('delete-category')->show();
    }

    // عملیات حذف
    public function delete(): void
    {
        try {
            DB::transaction(function () {
                $category = Category::findOrFail($this->cat_id);
                $category->delete();
            });

            session()->flash('success', 'دسته‌بندی با موفقیت حذف شد.');
            Flux::modal('delete-category')->close();
            $this->reset_data();

        } catch (\Throwable $e) {
            session()->flash('error', 'خطا در حذف دسته‌بندی: '.$e->getMessage());
            Flux::modal('delete-category')->close();
        }
    }
};

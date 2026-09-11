<?php

use App\Models\Category;
use App\Models\Product;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Morilog\Jalali\Jalalian;

new class extends Component {
    use WithPagination;

    public Category $category;
    public int $category_id;

    // متغیر جست‌وجو
    public string $search = '';

    // متغیرهای فیلتر تاریخ شمسی
    public $selected_year = '';
    public $selected_month = '';
    public $selected_day = '';

    // نام ماه‌های شمسی جهت نمایش در تب‌ها/دکمه‌های فیلتر
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

    // متغیرهای فرم
    public $name = '';
    public $pro_id = null;

    // متغیرهای مرتب‌سازی
    public $sortBy = 'name';
    public $sortDirection = 'desc';

    public function mount(Category $category)
    {
        $this->category = $category;
        $this->category_id = $category->id;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedYear()
    {
        $this->selected_month = '';
        $this->selected_day = '';
        $this->resetPage();
    }

    public function updatedSelectedMonth()
    {
        $this->selected_day = '';
        $this->resetPage();
    }

    public function updatedSelectedDay()
    {
        $this->resetPage();
    }

    public function clearDateFilters()
    {
        $this->selected_year = '';
        $this->selected_month = '';
        $this->selected_day = '';
        $this->resetPage();
    }

    public function sort($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc'
                ? 'desc'
                : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    #[Computed]
    public function distinctJalaliDates()
    {
        return Product::query()
            ->where('category_id', $this->category->id)
            ->whereNotNull('created_at')
            ->select('created_at')
            ->get()
            ->map(function ($item) {
                try {
                    $carbonTehran = \Carbon\Carbon::parse($item->created_at)->setTimezone('Asia/Tehran');
                    $jalali = Jalalian::fromCarbon($carbonTehran);
                    return [
                        'year' => (int)$jalali->format('Y'),
                        'month' => (int)$jalali->format('m'),
                        'day' => (int)$jalali->format('d'),
                    ];
                } catch (\Throwable $e) {
                    return null;
                }
            })
            ->filter();
    }

    #[Computed]
    public function availableYears()
    {
        return $this->distinctJalaliDates
            ->pluck('year')
            ->unique()
            ->sortDesc()
            ->values()
            ->all();
    }

    #[Computed]
    public function availableMonths()
    {
        if (empty($this->selected_year)) {
            return [];
        }

        return $this->distinctJalaliDates
            ->where('year', (int)$this->selected_year)
            ->pluck('month')
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    #[Computed]
    public function availableDays()
    {
        if (empty($this->selected_year) || empty($this->selected_month)) {
            return [];
        }

        return $this->distinctJalaliDates
            ->where('year', (int)$this->selected_year)
            ->where('month', (int)$this->selected_month)
            ->pluck('day')
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    #[Computed]
    public function products()
    {
        return Product::query()
            ->with(['user.profile'])
            ->where('category_id', $this->category->id)
            ->when($this->search !== '', function ($query) {
                $query->where(
                    'name',
                    'like',
                    '%' . trim($this->search) . '%'
                );
            })
            ->when(!empty($this->selected_year), function ($query) {
                $year = (int)$this->selected_year;

                if (!empty($this->selected_month) && !empty($this->selected_day)) {
                    $month = (int)$this->selected_month;
                    $day = (int)$this->selected_day;

                    $startMiladi = (new Jalalian($year, $month, $day, 0, 0, 0))->toCarbon('Asia/Tehran')->setTimezone('UTC');
                    $endMiladi = (new Jalalian($year, $month, $day, 23, 59, 59))->toCarbon('Asia/Tehran')->setTimezone('UTC');

                    $query->whereBetween('created_at', [$startMiladi, $endMiladi]);
                } elseif (!empty($this->selected_month)) {
                    $month = (int)$this->selected_month;
                    $daysInMonth = $month <= 6 ? 31 : ($month <= 11 ? 30 : ((new Jalalian($year, 1, 1))->isLeapYear() ? 30 : 29));

                    $startMiladi = (new Jalalian($year, $month, 1, 0, 0, 0))->toCarbon('Asia/Tehran')->setTimezone('UTC');
                    $endMiladi = (new Jalalian($year, $month, $daysInMonth, 23, 59, 59))->toCarbon('Asia/Tehran')->setTimezone('UTC');

                    $query->whereBetween('created_at', [$startMiladi, $endMiladi]);
                } else {
                    $lastMonth = 12;
                    $lastDay = (new Jalalian($year, 1, 1))->isLeapYear() ? 30 : 29;

                    $startMiladi = (new Jalalian($year, 1, 1, 0, 0, 0))->toCarbon('Asia/Tehran')->setTimezone('UTC');
                    $endMiladi = (new Jalalian($year, $lastMonth, $lastDay, 23, 59, 59))->toCarbon('Asia/Tehran')->setTimezone('UTC');

                    $query->whereBetween('created_at', [$startMiladi, $endMiladi]);
                }
            })
            ->tap(function ($query) {
                if ($this->sortBy) {
                    $query->orderBy(
                        $this->sortBy,
                        $this->sortDirection
                    );
                }
            })
            ->paginate(15);
    }

    // متد باز کردن مودال ثبت محصول جدید
    public function openSaveModal()
    {
        $this->reset_data();
        Flux::modal('save')->show();
    }

    // پاک‌سازی فرم و خطاهای اعتبارسنجی
    public function reset_data()
    {
        $this->reset(['name', 'pro_id']);
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate([
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                \Illuminate\Validation\Rule::unique('products', 'name')
                    ->where(function ($query) {
                        return $query->where(
                            'category_id',
                            $this->category_id
                        );
                    }),
            ],
        ], [
            'name.required' => 'وارد کردن نام محصول الزامی است.',
            'name.min' => 'نام محصول باید حداقل ۲ کاراکتر باشد.',
            'name.max' => 'نام محصول نباید بیشتر از ۱۰۰ کاراکتر باشد.',
            'name.unique' => 'این نام محصول قبلاً در این دسته‌بندی ثبت شده است.',
        ]);

        try {
            DB::transaction(function () {
                $product = new Product();
                $product->name = $this->name;
                $product->category_id = $this->category_id;
                if (\Illuminate\Support\Facades\Schema::hasColumn('products', 'user_id')) {
                    $product->user_id = Auth::id();
                }
                $product->save();
            });

            session()->flash(
                'success',
                'محصول جدید با موفقیت ثبت شد.'
            );

            Flux::modal('save')->close();
            $this->reset_data();
        } catch (\Throwable $e) {
            $this->addError(
                'save_error',
                'خطایی در ثبت اطلاعات رخ داد: ' . $e->getMessage()
            );
        }
    }

    public function edit(Product $product)
    {
        $this->resetValidation();

        $this->name = $product->name;
        $this->pro_id = $product->id;

        Flux::modal('edit-user')->show();
    }

    public function update()
    {
        $this->validate([
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                \Illuminate\Validation\Rule::unique('products', 'name')
                    ->ignore($this->pro_id)
                    ->where(function ($query) {
                        return $query->where(
                            'category_id',
                            $this->category_id
                        );
                    }),
            ],
        ], [
            'name.required' => 'وارد کردن نام محصول الزامی است.',
            'name.min' => 'نام محصول باید حداقل ۲ کاراکتر باشد.',
            'name.max' => 'نام محصول نباید بیشتر از ۱۰۰ کاراکتر باشد.',
            'name.unique' => 'این نام محصول قبلاً در این دسته‌بندی ثبت شده است.',
        ]);

        try {
            DB::transaction(function () {
                $product = Product::findOrFail($this->pro_id);
                $product->name = $this->name;
                $product->save();
            });

            session()->flash(
                'success',
                'محصول با موفقیت ویرایش شد.'
            );

            Flux::modal('edit-user')->close();
            $this->reset_data();
        } catch (\Throwable $e) {
            $this->addError(
                'update_error',
                'خطایی در ویرایش اطلاعات رخ داد: ' . $e->getMessage()
            );
        }
    }

    public function delete_form(Product $product)
    {
        $this->reset_data();

        $this->name = $product->name;
        $this->pro_id = $product->id;

        Flux::modal('delete-user')->show();
    }

    public function delete()
    {
        try {
            DB::transaction(function () {
                $product = Product::findOrFail($this->pro_id);
                $product->delete();
            });

            session()->flash(
                'success',
                'محصول با موفقیت حذف شد.'
            );

            Flux::modal('delete-user')->close();
            $this->reset_data();
        } catch (\Throwable $e) {
            session()->flash(
                'error',
                'خطا در حذف محصول: ' . $e->getMessage()
            );

            Flux::modal('delete-user')->close();
        }
    }
};

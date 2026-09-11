<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBatch;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Morilog\Jalali\Jalalian;

new class extends Component
{
    use WithPagination;

    public Category $category;

    // متغیرهای جست‌وجوی تفکیک‌شده
    public string $search_product = '';

    public string $search_employee = '';

    // متغیرهای فیلتر تاریخ شمسی بالای جدول
    public $selected_year = '';
    public $selected_month = '';
    public $selected_day = '';

    // اطلاعات فرم
    public string $product_name = '';

    public ?int $u_id = null;

    public ?int $batch_id = null;

    public ?int $cat_id = null;

    public ?int $pro_id = null;

    public $sale_price = '';

    // فیلدهای تاریخ تولید (تفکیک‌شده برای flux:select)
    public $p_day = '';
    public $p_month = '';
    public $p_year = '';

    // فیلدهای تاریخ انقضا (تفکیک‌شده برای flux:select)
    public $ex_day = '';
    public $ex_month = '';
    public $ex_year = '';

    public $quantity = '';

    // مرتب‌سازی
    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public function mount(Category $category): void
    {
        $this->category = $category;
        $this->cat_id = $category->id;
        $this->u_id = auth()->user()?->profile?->id;
        $this->pro_id = $category->products()->value('id');
    }

    #[Computed]
    public function persianMonths(): array
    {
        return [
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
    }

    #[Computed]
    public function products()
    {
        return Product::query()
            ->where('category_id', $this->category->id)
            ->orderBy('name')
            ->get();
    }

    public function updatedSearchProduct(): void
    {
        $this->resetPage();
    }

    public function updatedSearchEmployee(): void
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
        $this->selected_year = '';
        $this->selected_month = '';
        $this->selected_day = '';
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'desc';
        }

        $this->resetPage();
    }

    #[Computed]
    public function distinctJalaliDates(): Collection
    {
        return ProductBatch::query()
            ->whereHas('product', function ($query) {
                $query->where('category_id', $this->category->id);
            })
            ->whereNotNull('created_at')
            ->select('created_at')
            ->get()
            ->map(function ($item) {
                try {
                    $carbonTehran = Carbon::parse($item->created_at)->setTimezone('Asia/Tehran');
                    $jalali = Jalalian::fromCarbon($carbonTehran);

                    return [
                        'year' => (int) $jalali->format('Y'),
                        'month' => (int) $jalali->format('m'),
                        'day' => (int) $jalali->format('d'),
                    ];
                } catch (\Throwable $e) {
                    return null;
                }
            })
            ->filter()
            ->values();
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
    public function productbatchs()
    {
        return ProductBatch::query()
            ->with([
                'product.category',
                'profile.user',
            ])
            ->whereHas('product', function ($query) {
                $query->where('category_id', $this->category->id);
            })
            // فیلتر اختصاصی محصول
            ->when(filled($this->search_product), function ($query) {
                $term = '%' . trim($this->search_product) . '%';
                $query->whereHas('product', function ($pQuery) use ($term) {
                    $pQuery->where('name', 'like', $term);
                });
            })
            // فیلتر اختصاصی کارمند
            ->when(filled($this->search_employee), function ($query) {
                $term = '%' . trim($this->search_employee) . '%';
                $query->whereHas('profile', function ($profQuery) use ($term) {
                    $profQuery->where(function ($subQuery) use ($term) {
                        $subQuery->where('first_name', 'like', $term)
                            ->orWhere('last_name', 'like', $term)
                            ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', $term);
                    });
                });
            })
            // فیلتر آبشاری تاریخ شمسی
            ->when(!empty($this->selected_year), function ($query) {
                $year = (int) $this->selected_year;

                if (!empty($this->selected_month) && !empty($this->selected_day)) {
                    $month = (int) $this->selected_month;
                    $day = (int) $this->selected_day;

                    $startMiladi = (new Jalalian($year, $month, $day, 0, 0, 0))->toCarbon('Asia/Tehran')->setTimezone('UTC');
                    $endMiladi = (new Jalalian($year, $month, $day, 23, 59, 59))->toCarbon('Asia/Tehran')->setTimezone('UTC');

                    $query->whereBetween('created_at', [$startMiladi, $endMiladi]);
                } elseif (!empty($this->selected_month)) {
                    $month = (int) $this->selected_month;
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
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(15);
    }

    protected function formRules(int $minimumQuantity = 1): array
    {
        return [
            'pro_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $exists = $this->category
                        ->products()
                        ->whereKey($value)
                        ->exists();

                    if (! $exists) {
                        $fail('محصول انتخاب‌شده به این دسته‌بندی تعلق ندارد.');
                    }
                },
            ],
            'sale_price' => ['required', 'numeric', 'min:0'],

            'p_year' => ['required', 'numeric'],
            'p_month' => ['required', 'numeric', 'between:1,12'],
            'p_day' => ['required', 'numeric', 'between:1,31'],

            'ex_year' => ['required', 'numeric'],
            'ex_month' => ['required', 'numeric', 'between:1,12'],
            'ex_day' => [
                'required',
                'numeric',
                'between:1,31',
                function ($attribute, $value, $fail) {
                    try {
                        if ($this->p_year && $this->p_month && $this->p_day && $this->ex_year && $this->ex_month && $this->ex_day) {
                            $pCarbon = (new Jalalian((int)$this->p_year, (int)$this->p_month, (int)$this->p_day, 0, 0, 0))->toCarbon();
                            $exCarbon = (new Jalalian((int)$this->ex_year, (int)$this->ex_month, (int)$this->ex_day, 0, 0, 0))->toCarbon();

                            if ($exCarbon->lt($pCarbon)) {
                                $fail('تاریخ انقضا باید برابر یا بعد از تاریخ تولید باشد.');
                            }
                        }
                    } catch (\Throwable $e) {
                        $fail('تاریخ انقضا یا تولید وارد شده نامعتبر است.');
                    }
                },
            ],

            'quantity' => ['required', 'integer', 'min:'.$minimumQuantity],
        ];
    }

    protected function validationMessages(): array
    {
        return [
            'pro_id.required' => 'انتخاب محصول الزامی است.',
            'pro_id.integer' => 'محصول انتخاب‌شده نامعتبر است.',

            'sale_price.required' => 'وارد کردن قیمت فروش الزامی است.',
            'sale_price.numeric' => 'قیمت فروش باید عدد باشد.',
            'sale_price.min' => 'قیمت فروش نمی‌تواند منفی باشد.',

            'p_year.required' => 'انتخاب سال تولید الزامی است.',
            'p_month.required' => 'انتخاب ماه تولید الزامی است.',
            'p_day.required' => 'انتخاب روز تولید الزامی است.',

            'ex_year.required' => 'انتخاب سال انقضا الزامی است.',
            'ex_month.required' => 'انتخاب ماه انقضا الزامی است.',
            'ex_day.required' => 'انتخاب روز انقضا الزامی است.',

            'quantity.required' => 'وارد کردن تعداد الزامی است.',
            'quantity.integer' => 'تعداد باید یک عدد صحیح باشد.',
            'quantity.min' => 'تعداد باید حداقل ۱ باشد.',
        ];
    }

    public function openSaveModal(): void
    {
        $this->reset_data();
        $this->pro_id = $this->category->products()->value('id');

        // تنظیم تاریخ پیش‌فرض روز جاری
        $now = Jalalian::now();
        $this->p_year = (int) $now->getYear();
        $this->p_month = (int) $now->getMonth();
        $this->p_day = (int) $now->getDay();

        $this->ex_year = (int) $now->getYear() + 1;
        $this->ex_month = (int) $now->getMonth();
        $this->ex_day = (int) $now->getDay();

        Flux::modal('save-modal')->show();
    }

    public function reset_data(): void
    {
        $this->reset([
            'product_name',
            'batch_id',
            'pro_id',
            'sale_price',
            'p_day',
            'p_month',
            'p_year',
            'ex_day',
            'ex_month',
            'ex_year',
            'quantity',
        ]);

        $this->resetValidation();
    }

    public function save(): void
    {
        $this->validate(
            $this->formRules(1),
            $this->validationMessages()
        );

        try {
            DB::transaction(function () {
                $productBatch = new ProductBatch;

                $productBatch->product_id = $this->pro_id;
                $productBatch->profile_id = auth()->user()?->profile?->id;
                $productBatch->sale_price = $this->sale_price;

                // تبدیل تاریخ شمسی به میلادی
                $productBatch->production_date = (new Jalalian((int)$this->p_year, (int)$this->p_month, (int)$this->p_day, 0, 0, 0))
                    ->toCarbon()
                    ->toDateString();

                $productBatch->expiry_date = (new Jalalian((int)$this->ex_year, (int)$this->ex_month, (int)$this->ex_day, 0, 0, 0))
                    ->toCarbon()
                    ->toDateString();

                $productBatch->quantity = $this->quantity;

                $productBatch->save();
            });

            unset($this->productbatchs);
            unset($this->distinctJalaliDates);

            session()->flash('success', 'موجودی جدید با موفقیت ثبت شد.');
            Flux::modal('save-modal')->close();
            $this->reset_data();
        } catch (\Throwable $e) {
            report($e);
            $this->addError(
                'save_error',
                'خطایی هنگام ثبت موجودی رخ داد. لطفاً دوباره تلاش کنید.'
            );
        }
    }

    public function edit(ProductBatch $productBatch): void
    {
        abort_unless(
            $productBatch->product?->category_id === $this->category->id,
            404
        );

        $this->resetValidation();

        $this->batch_id = $productBatch->id;
        $this->pro_id = $productBatch->product_id;
        $this->sale_price = $productBatch->sale_price;
        $this->quantity = $productBatch->quantity;

        // تفکیک تاریخ تولید شمسی به روز/ماه/سال
        if ($productBatch->production_date) {
            try {
                $pJalali = Jalalian::fromCarbon(Carbon::parse($productBatch->production_date));
                $this->p_year = (int) $pJalali->getYear();
                $this->p_month = (int) $pJalali->getMonth();
                $this->p_day = (int) $pJalali->getDay();
            } catch (\Throwable $e) {
                $this->p_year = $this->p_month = $this->p_day = '';
            }
        }

        // تفکیک تاریخ انقضا شمسی به روز/ماه/سال
        if ($productBatch->expiry_date) {
            try {
                $exJalali = Jalalian::fromCarbon(Carbon::parse($productBatch->expiry_date));
                $this->ex_year = (int) $exJalali->getYear();
                $this->ex_month = (int) $exJalali->getMonth();
                $this->ex_day = (int) $exJalali->getDay();
            } catch (\Throwable $e) {
                $this->ex_year = $this->ex_month = $this->ex_day = '';
            }
        }

        Flux::modal('edit-modal')->show();
    }

    public function update(): void
    {
        $this->validate(
            $this->formRules(1),
            $this->validationMessages()
        );

        try {
            DB::transaction(function () {
                $productBatch = ProductBatch::findOrFail($this->batch_id);

                abort_unless(
                    $productBatch->product?->category_id === $this->category->id,
                    404
                );

                $productBatch->product_id = $this->pro_id;
                $productBatch->sale_price = $this->sale_price;

                // تبدیل تاریخ شمسی به میلادی
                $productBatch->production_date = (new Jalalian((int)$this->p_year, (int)$this->p_month, (int)$this->p_day, 0, 0, 0))
                    ->toCarbon()
                    ->toDateString();

                $productBatch->expiry_date = (new Jalalian((int)$this->ex_year, (int)$this->ex_month, (int)$this->ex_day, 0, 0, 0))
                    ->toCarbon()
                    ->toDateString();

                $productBatch->quantity = $this->quantity;

                $productBatch->save();
            });

            unset($this->productbatchs);
            unset($this->distinctJalaliDates);

            session()->flash('success', 'اطلاعات موجودی با موفقیت ویرایش شد.');
            Flux::modal('edit-modal')->close();
            $this->reset_data();
        } catch (\Throwable $e) {
            report($e);
            $this->addError(
                'update_error',
                'خطایی هنگام ویرایش موجودی رخ داد. لطفاً دوباره تلاش کنید.'
            );
        }
    }

    public function delete_form(ProductBatch $productBatch): void
    {
        abort_unless(
            $productBatch->product?->category_id === $this->category->id,
            404
        );

        $this->reset_data();

        $this->batch_id = $productBatch->id;
        $this->product_name = $productBatch->product?->name ?? 'این محصول';

        Flux::modal('delete-modal')->show();
    }

    public function delete(): void
    {
        try {
            DB::transaction(function () {
                $productBatch = ProductBatch::findOrFail($this->batch_id);

                abort_unless(
                    $productBatch->product?->category_id === $this->category->id,
                    404
                );

                $productBatch->delete();
            });

            unset($this->productbatchs);
            unset($this->distinctJalaliDates);

            session()->flash('success', 'موجودی با موفقیت حذف شد.');
            Flux::modal('delete-modal')->close();
            $this->reset_data();
        } catch (\Throwable $e) {
            report($e);
            session()->flash(
                'error',
                'خطایی هنگام حذف موجودی رخ داد. لطفاً دوباره تلاش کنید.'
            );
            Flux::modal('delete-modal')->close();
        }
    }
};

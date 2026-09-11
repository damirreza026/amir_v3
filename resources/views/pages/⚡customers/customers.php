<?php

use App\Models\Customer;
use Carbon\Carbon;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Morilog\Jalali\Jalalian;

new class extends Component
{
    use WithPagination;

    public ?int $cust_id = null;

    // متغیرهای جست‌وجو
    public string $search = '';
    public string $search_employee = '';

    // متغیرهای فیلتر تاریخ شمسی
    public string $selected_year = '';
    public string $selected_month = '';
    public string $selected_day = '';

    // متغیرهای فرم
    public string $shop_name = '';
    public string $phone = '';
    public string $address = '';

    // مرتب‌سازی
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';

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

    public function updatedSearch(): void
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
        $allowed = ['shop_name', 'phone', 'address', 'id', 'created_at'];
        if (! in_array($column, $allowed, true)) {
            return;
        }

        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    #[Computed]
    public function distinctJalaliDates(): array
    {
        $dates = Customer::query()
            ->whereNotNull('created_at')
            ->pluck('created_at');

        $result = [];

        foreach ($dates as $date) {
            try {
                $carbon = Carbon::parse($date)->setTimezone('Asia/Tehran');
                $jalali = Jalalian::fromCarbon($carbon);
                $year = (int) $jalali->format('Y');
                $month = (int) $jalali->format('m');
                $day = (int) $jalali->format('d');

                $result[$year][$month][$day] = true;
            } catch (\Throwable $e) {
                // نادیده گرفتن مقادیر نامعتبر تاریخ
            }
        }

        return $result;
    }

    #[Computed]
    public function availableYears(): array
    {
        $years = array_keys($this->distinctJalaliDates);
        rsort($years);
        return $years;
    }

    #[Computed]
    public function availableMonths(): array
    {
        if (empty($this->selected_year) || !isset($this->distinctJalaliDates[(int) $this->selected_year])) {
            return [];
        }

        $months = array_keys($this->distinctJalaliDates[(int) $this->selected_year]);
        sort($months);
        return $months;
    }

    #[Computed]
    public function availableDays(): array
    {
        if (
            empty($this->selected_year) ||
            empty($this->selected_month) ||
            !isset($this->distinctJalaliDates[(int) $this->selected_year][(int) $this->selected_month])
        ) {
            return [];
        }

        $days = array_keys($this->distinctJalaliDates[(int) $this->selected_year][(int) $this->selected_month]);
        sort($days);
        return $days;
    }

    #[Computed]
    public function customers()
    {
        return Customer::query()
            ->with(['user.profile'])
            ->when(filled($this->search), function ($query) {
                $query->where('shop_name', 'like', '%' . trim($this->search) . '%');
            })
            ->when(filled($this->search_employee), function ($query) {
                $term = trim($this->search_employee);
                $query->whereHas('user.profile', function ($q) use ($term) {
                    $q->where('first_name', 'like', "%{$term}%")
                        ->orWhere('last_name', 'like', "%{$term}%")
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$term}%"]);
                });
            })
            ->when(filled($this->selected_year), function ($query) {
                $year = (int) $this->selected_year;

                if (filled($this->selected_month) && filled($this->selected_day)) {
                    $month = (int) $this->selected_month;
                    $day = (int) $this->selected_day;

                    $startCarbon = (new Jalalian($year, $month, $day, 0, 0, 0))->toCarbon('Asia/Tehran');
                    $endCarbon = (new Jalalian($year, $month, $day, 23, 59, 59))->toCarbon('Asia/Tehran');
                } elseif (filled($this->selected_month)) {
                    $month = (int) $this->selected_month;
                    $daysInMonth = $month <= 6 ? 31 : ($month <= 11 ? 30 : 29);

                    $startCarbon = (new Jalalian($year, $month, 1, 0, 0, 0))->toCarbon('Asia/Tehran');
                    $endCarbon = (new Jalalian($year, $month, $daysInMonth, 23, 59, 59))->toCarbon('Asia/Tehran');
                } else {
                    $startCarbon = (new Jalalian($year, 1, 1, 0, 0, 0))->toCarbon('Asia/Tehran');
                    $endCarbon = (new Jalalian($year, 12, 29, 23, 59, 59))->toCarbon('Asia/Tehran');
                }

                $query->whereBetween('created_at', [
                    $startCarbon->setTimezone('UTC')->toDateTimeString(),
                    $endCarbon->setTimezone('UTC')->toDateTimeString(),
                ]);
            })
            ->when($this->sortBy, fn ($q) => $q->orderBy($this->sortBy, $this->sortDirection))
            ->paginate(15);
    }

    /** قوانین اعتبارسنجی */
    protected function rules(): array
    {
        return [
            'shop_name' => ['required', 'string', 'min:2', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^\d{11}$/'],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function messages(): array
    {
        return [
            'shop_name.required' => 'نام فروشگاه الزامی است.',
            'shop_name.min' => 'نام فروشگاه حداقل باید ۲ کاراکتر باشد.',
            'shop_name.max' => 'نام فروشگاه حداکثر ۱۰۰ کاراکتر باشد.',

            'phone.regex' => 'شماره تماس شما باید ۱۱ رقم باشد.',
            'phone.max' => 'شماره تلفن حداکثر ۳۰ کاراکتر باشد.',

            'address.max' => 'آدرس حداکثر ۲۵۵ کاراکتر باشد.',
        ];
    }

    /** ریست فرم + خطاها */
    public function resetForm(): void
    {
        $this->reset(['cust_id', 'shop_name', 'phone', 'address']);
        $this->resetValidation();
    }

    public function openSaveModal(): void
    {
        $this->resetForm();
        Flux::modal('save')->show();
    }

    public function save(): void
    {
        $data = $this->validate();

        try {
            $data['user_id'] = auth()->id();
            Customer::create($data);

            unset($this->customers, $this->distinctJalaliDates, $this->availableYears, $this->availableMonths, $this->availableDays);
            session()->flash('success', 'مشتری جدید با موفقیت ثبت شد.');
            Flux::modal('save')->close();
            $this->resetForm();
        } catch (\Throwable $e) {
            report($e);
            $this->addError('save_error', 'خطایی هنگام ثبت اطلاعات رخ داد. لطفاً دوباره تلاش کنید.');
        }
    }

    public function edit(int $id): void
    {
        $customer = Customer::findOrFail($id);

        $this->cust_id = $customer->id;
        $this->shop_name = (string) $customer->shop_name;
        $this->phone = (string) ($customer->phone ?? '');
        $this->address = (string) ($customer->address ?? '');

        $this->resetValidation();
        Flux::modal('edit')->show();
    }

    public function update(): void
    {
        if (! $this->cust_id) {
            $this->addError('cust_id', 'شناسه مشتری نامعتبر است.');
            return;
        }

        $data = $this->validate();

        try {
            $customer = Customer::findOrFail($this->cust_id);
            $customer->update($data);

            unset($this->customers, $this->distinctJalaliDates, $this->availableYears, $this->availableMonths, $this->availableDays);
            session()->flash('success', 'اطلاعات مشتری با موفقیت ویرایش شد.');
            Flux::modal('edit')->close();
            $this->resetForm();
        } catch (\Throwable $e) {
            report($e);
            $this->addError('update_error', 'خطایی هنگام ویرایش اطلاعات رخ داد. لطفاً دوباره تلاش کنید.');
        }
    }

    public function del_form(int $id): void
    {
        $customer = Customer::findOrFail($id);

        $this->cust_id = $customer->id;
        $this->shop_name = (string) $customer->shop_name;

        $this->resetValidation();
        Flux::modal('delete')->show();
    }

    public function delete(): void
    {
        if (! $this->cust_id) {
            $this->addError('cust_id', 'شناسه مشتری نامعتبر است.');
            return;
        }

        try {
            Customer::whereKey($this->cust_id)->delete();

            unset($this->customers, $this->distinctJalaliDates, $this->availableYears, $this->availableMonths, $this->availableDays);
            session()->flash('success', 'مشتری با موفقیت حذف شد.');
            Flux::modal('delete')->close();
            $this->resetForm();
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'خطایی هنگام حذف مشتری رخ داد. لطفاً دوباره تلاش کنید.');
            Flux::modal('delete')->close();
        }
    }
};

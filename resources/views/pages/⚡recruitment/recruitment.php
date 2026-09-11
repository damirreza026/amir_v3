<?php

use App\Models\Profile;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Morilog\Jalali\Jalalian;

new class extends Component
{
    use WithPagination;

    // متغیرهای فرم
    public $user_name = '';

    public $password = '';

    public $confirm_password = '';

    public $f_name = '';

    public $l_name = '';

    public $phone = '';

    public $national_code = '';

    public $address = '';

    // متغیرهای جستجوی متنی
    public $search_national_code = '';

    public $search_last_name = '';

    // متغیرهای فیلتر آبشاری تاریخ شمسی
    public $selected_year = '';

    public $selected_month = '';

    public $selected_day = '';

    // شناسه نقش انتخابی
    public $role_id = '';

    // شناسه پروفایل و کاربر در حال ویرایش یا حذف
    public $profile_id = null;

    public $user_id = null;

    // آیا در حال ویرایش پروفایل خودِ کاربر لاگین‌شده هستیم؟
    public bool $isEditingSelf = false;

    // متغیرهای مرتب‌سازی جدول
    public $sortBy = 'first_name';

    public $sortDirection = 'asc';

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

    public function updatedSearchNationalCode(): void
    {
        $this->resetPage();
    }

    public function updatedSearchLastName(): void
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

    public function sort($column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function currentUserRoleId(): int
    {
        return (int) (Auth::user()->profile->role_id ?? 0);
    }

    public function isSuperAdmin(): bool
    {
        return $this->currentUserRoleId() === 1;
    }

    public function isAdmin(): bool
    {
        return $this->currentUserRoleId() === 2;
    }

    /**
     * استخراج تمام تاریخ‌های متمایز و تبدیل آن‌ها به تقویم جلالی
     */
    #[Computed]
    public function distinctJalaliDates()
    {
        $dates = Profile::query()
            ->selectRaw('DATE(created_at) as date_created')
            ->groupBy('date_created')
            ->orderBy('date_created', 'desc')
            ->pluck('date_created');

        return $dates->map(function ($date) {
            $carbon = Carbon::parse($date);
            $jalali = class_exists(Jalalian::class)
                ? Jalalian::fromCarbon($carbon)
                : null;

            return [
                'gregorian' => $date,
                'year' => $jalali ? (int) $jalali->getYear() : (int) $carbon->year,
                'month' => $jalali ? (int) $jalali->getMonth() : (int) $carbon->month,
                'day' => $jalali ? (int) $jalali->getDay() : (int) $carbon->day,
            ];
        });
    }

    #[Computed]
    public function availableYears()
    {
        return $this->distinctJalaliDates
            ->pluck('year')
            ->unique()
            ->sortDesc()
            ->values();
    }

    #[Computed]
    public function availableMonths()
    {
        if (empty($this->selected_year)) {
            return collect();
        }

        return $this->distinctJalaliDates
            ->where('year', (int) $this->selected_year)
            ->pluck('month')
            ->unique()
            ->sort()
            ->values();
    }

    #[Computed]
    public function availableDays()
    {
        if (empty($this->selected_year) || empty($this->selected_month)) {
            return collect();
        }

        return $this->distinctJalaliDates
            ->where('year', (int) $this->selected_year)
            ->where('month', (int) $this->selected_month)
            ->pluck('day')
            ->unique()
            ->sort()
            ->values();
    }

    #[Computed]
    public function profiles()
    {
        $query = Profile::query()->with([
            'user',
            'creator.profile',
            'updater.profile',
        ]);

        if ($this->isAdmin()) {
            $query->where('role_id', '!=', 1);
        }

        if (! empty(trim($this->search_national_code))) {
            $query->where('national_code', 'like', '%'.trim($this->search_national_code).'%');
        }

        if (! empty(trim($this->search_last_name))) {
            $query->where('last_name', 'like', '%'.trim($this->search_last_name).'%');
        }

        // اعمال فیلتر آبشاری تاریخ بر اساس تقویم جلالی
        if (! empty($this->selected_year)) {
            $year = (int) $this->selected_year;

            if (! empty($this->selected_month) && ! empty($this->selected_day)) {
                // ۱. فیلتر دقیق روز
                $month = (int) $this->selected_month;
                $day = (int) $this->selected_day;

                $start = (new Jalalian($year, $month, $day, 0, 0, 0))->toCarbon();
                $end = (new Jalalian($year, $month, $day, 23, 59, 59))->toCarbon();

                $query->whereBetween('created_at', [$start, $end]);
            } elseif (! empty($this->selected_month)) {
                // ۲. فیلتر کل ماه انتخابی
                $month = (int) $this->selected_month;
                $daysInMonth = ($month <= 6) ? 31 : (($month <= 11) ? 30 : 29);

                $start = (new Jalalian($year, $month, 1, 0, 0, 0))->toCarbon();
                $end = (new Jalalian($year, $month, $daysInMonth, 23, 59, 59))->toCarbon();

                $query->whereBetween('created_at', [$start, $end]);
            } else {
                // ۳. فیلتر کل سال انتخابی
                $start = (new Jalalian($year, 1, 1, 0, 0, 0))->toCarbon();
                $end = (new Jalalian($year, 12, 29, 23, 59, 59))->toCarbon();

                $query->whereBetween('created_at', [$start, $end]);
            }
        }

        return $query
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);
    }

    #[Computed]
    public function roles()
    {
        $query = Role::query()->orderBy('name');

        if ($this->isSuperAdmin()) {
            $query->where('id', '!=', 1);
        } elseif ($this->isAdmin()) {
            $query->whereNotIn('id', [1, 2]);
        } else {
            $query->whereNotIn('id', [1, 2]);
        }

        return $query->get(['id', 'name']);
    }

    public function openAddModal(): void
    {
        $this->reset_deta();
        Flux::modal('add-user')->show();
    }

    public function reset_deta(): void
    {
        $this->reset([
            'user_name',
            'password',
            'confirm_password',
            'f_name',
            'l_name',
            'phone',
            'national_code',
            'address',
            'role_id',
            'profile_id',
            'user_id',
            'isEditingSelf',
        ]);

        $this->resetValidation();
        Flux::modals()->close();
    }

    public function save(): void
    {
        $rules = [
            'user_name' => ['required', 'string', 'min:4', 'unique:users,user_name'],
            'password' => ['required', 'string', 'min:6'],
            'confirm_password' => ['required', 'same:password'],
            'f_name' => ['required', 'string', 'max:100'],
            'l_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'regex:/^09[0-9]{9}$/'],
            'national_code' => ['required', 'digits:10', 'unique:profiles,national_code'],
            'address' => ['required', 'string', 'max:500'],
        ];

        if ($this->isAdmin()) {
            $rules['role_id'] = ['required', 'exists:roles,id', 'not_in:1,2'];
        } else {
            $rules['role_id'] = ['required', 'exists:roles,id', 'not_in:1'];
        }

        $this->validate($rules, [
            'user_name.required' => 'نام کاربری الزامی است.',
            'user_name.min' => 'نام کاربری باید حداقل ۴ کاراکتر باشد.',
            'user_name.unique' => 'این نام کاربری قبلاً ثبت شده است.',
            'password.required' => 'رمز عبور الزامی است.',
            'password.min' => 'رمز عبور باید حداقل ۶ کاراکتر باشد.',
            'confirm_password.required' => 'تکرار رمز عبور الزامی است.',
            'confirm_password.same' => 'تکرار رمز عبور با رمز عبور مطابقت ندارد.',
            'f_name.required' => 'نام الزامی است.',
            'l_name.required' => 'نام خانوادگی الزامی است.',
            'phone.required' => 'شماره تلفن الزامی است.',
            'phone.regex' => 'شماره تلفن وارد شده معتبر نیست.',
            'national_code.required' => 'کد ملی الزامی است.',
            'national_code.digits' => 'کد ملی باید دقیقاً ۱۰ رقم باشد.',
            'national_code.unique' => 'این کد ملی قبلاً ثبت شده است.',
            'address.required' => 'آدرس الزامی است.',
            'role_id.required' => 'انتخاب نقش الزامی است.',
            'role_id.exists' => 'نقش انتخاب شده معتبر نیست.',
            'role_id.not_in' => 'شما مجاز به تعریف پرسنل با این سطح دسترسی نیستید.',
        ]);

        try {
            DB::transaction(function () {
                $user = User::create([
                    'user_name' => $this->user_name,
                    'password' => Hash::make($this->password),
                ]);

                $user->profile()->create([
                    'first_name' => $this->f_name,
                    'last_name' => $this->l_name,
                    'phone' => $this->phone,
                    'national_code' => $this->national_code,
                    'address' => $this->address,
                    'role_id' => $this->role_id,
                    'created_by' => Auth::id(),
                ]);
            });

            session()->flash('success', 'کاربر جدید با موفقیت استخدام و ثبت شد.');
            Flux::modal('add-user')->close();
            $this->reset_deta();

        } catch (Throwable $e) {
            $this->addError('save_error', 'خطایی در ثبت اطلاعات رخ داد: '.$e->getMessage());
        }
    }

    public function edit(int $profileId): void
    {
        $this->resetValidation();

        $profile = Profile::with(['user'])->findOrFail($profileId);

        if ($this->isAdmin() && (int) $profile->role_id === 1) {
            session()->flash('error', 'شما دسترسی ویرایش سوپرادمین را ندارید.');

            return;
        }

        $this->profile_id = $profile->id;
        $this->user_id = $profile->user_id;
        $this->isEditingSelf = ((int) $profile->user_id === (int) Auth::id());

        $this->f_name = $profile->first_name;
        $this->l_name = $profile->last_name;
        $this->phone = $profile->phone;
        $this->national_code = $profile->national_code;
        $this->address = $profile->address;
        $this->role_id = $profile->role_id;

        $user = $profile->user;
        if ($user) {
            $this->user_name = $user->user_name;
        }

        $this->password = '';
        $this->confirm_password = '';

        Flux::modal('edit-user')->show();
    }

    public function update(): void
    {
        $profile = Profile::findOrFail($this->profile_id);

        if ($this->isAdmin() && (int) $profile->role_id === 1) {
            $this->addError('update_error', 'شما اجازه تغییر اطلاعات سوپرادمین را ندارید.');

            return;
        }

        $rules = [
            'user_name' => ['required', 'string', 'min:4', 'unique:users,user_name,'.$this->user_id],
            'password' => ['nullable', 'string', 'min:6'],
            'confirm_password' => ['nullable', 'required_with:password', 'same:password'],
            'f_name' => ['required', 'string', 'max:100'],
            'l_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'regex:/^09[0-9]{9}$/'],
            'national_code' => ['required', 'digits:10', 'unique:profiles,national_code,'.$this->profile_id],
            'address' => ['required', 'string', 'max:500'],
        ];

        $isSelf = ((int) $profile->user_id === (int) Auth::id());
        if (! $isSelf) {
            if ($this->isAdmin()) {
                $rules['role_id'] = ['required', 'exists:roles,id', 'not_in:1,2'];
            } else {
                $rules['role_id'] = ['required', 'exists:roles,id', 'not_in:1'];
            }
        }

        $this->validate($rules, [
            'user_name.required' => 'نام کاربری الزامی است.',
            'user_name.unique' => 'این نام کاربری قبلاً ثبت شده است.',
            'password.min' => 'رمز عبور جدید باید حداقل ۶ کاراکتر باشد.',
            'confirm_password.required_with' => 'لطفاً تکرار رمز عبور جدید را وارد کنید.',
            'confirm_password.same' => 'تکرار رمز عبور با رمز عبور جدید مطابقت ندارد.',
            'f_name.required' => 'نام الزامی است.',
            'l_name.required' => 'نام خانوادگی الزامی است.',
            'phone.required' => 'شماره تلفن الزامی است.',
            'phone.regex' => 'فرمت شماره تلفن صحیح نیست.',
            'national_code.required' => 'کد ملی الزامی است.',
            'national_code.digits' => 'کد ملی باید ۱۰ رقم باشد.',
            'national_code.unique' => 'این کد ملی قبلاً ثبت شده است.',
            'address.required' => 'آدرس الزامی است.',
            'role_id.required' => 'انتخاب نقش الزامی است.',
            'role_id.exists' => 'نقش انتخاب شده معتبر نیست.',
            'role_id.not_in' => 'شما مجاز به انتخاب این نقش نیستید.',
        ]);

        try {
            DB::transaction(function () use ($profile, $isSelf) {
                $user = User::findOrFail($this->user_id);

                $userData = [
                    'user_name' => $this->user_name,
                ];

                if (! empty($this->password)) {
                    $userData['password'] = Hash::make($this->password);
                }

                $user->update($userData);

                $profileData = [
                    'first_name' => $this->f_name,
                    'last_name' => $this->l_name,
                    'phone' => $this->phone,
                    'national_code' => $this->national_code,
                    'address' => $this->address,
                    'updated_by' => Auth::id(),
                    'last_modified_at' => now(),
                ];

                if (! $isSelf) {
                    $profileData['role_id'] = $this->role_id;
                }

                $profile->update($profileData);
            });

            session()->flash('success', 'اطلاعات کاربر با موفقیت ویرایش شد.');
            Flux::modal('edit-user')->close();
            $this->reset_deta();

        } catch (Throwable $e) {
            $this->addError('update_error', 'خطایی در ویرایش اطلاعات رخ داد: '.$e->getMessage());
        }
    }

    public function delete_form(int $profileId): void
    {
        $profile = Profile::findOrFail($profileId);

        if ((int) $profile->user_id === (int) Auth::id()) {
            session()->flash('error', 'شما نمی‌توانید حساب کاربری خودتان را حذف کنید.');

            return;
        }

        if ($this->isAdmin() && in_array((int) $profile->role_id, [1, 2])) {
            session()->flash('error', 'شما اجازه حذف این سطح کاربری را ندارید.');

            return;
        }

        $this->profile_id = $profile->id;
        $this->user_id = $profile->user_id;
        $this->f_name = $profile->first_name;
        $this->l_name = $profile->last_name;

        Flux::modal('delete-user')->show();
    }

    public function delete(): void
    {
        try {
            $profile = Profile::findOrFail($this->profile_id);

            if ((int) $profile->user_id === (int) Auth::id()) {
                session()->flash('error', 'امکان حذف حساب کاربری خودتان وجود ندارد.');
                Flux::modal('delete-user')->close();

                return;
            }

            if ($this->isAdmin() && in_array((int) $profile->role_id, [1, 2])) {
                session()->flash('error', 'شما اجازه حذف این کاربر را ندارید.');
                Flux::modal('delete-user')->close();

                return;
            }

            DB::transaction(function () {
                $user = User::find($this->user_id);
                if ($user) {
                    $user->delete();
                } else {
                    Profile::destroy($this->profile_id);
                }
            });

            session()->flash('success', 'کاربر با موفقیت از سیستم حذف گردید.');
            Flux::modal('delete-user')->close();
            $this->reset_deta();

        } catch (Throwable $e) {
            session()->flash('error', 'خطا در حذف کاربر: '.$e->getMessage());
            Flux::modal('delete-user')->close();
        }
    }
};

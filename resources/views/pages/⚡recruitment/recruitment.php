<?php

use App\Models\Profile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

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

    // متغیرهای جستجو
    public $search_national_code = '';
    public $search_last_name = '';

    // شناسه نقش انتخابی
    public $role_id = '';

    // شناسه پروفایل و کاربر در حال حذف
    public $profile_id = null;
    public $user_id = null;

    // متغیرهای مرتب‌سازی جدول
    public $sortBy = 'first_name';
    public $sortDirection = 'asc';

    public function updatedSearchNationalCode(): void
    {
        $this->resetPage();
    }

    public function updatedSearchLastName(): void
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

    #[Computed]
    public function profiles()
    {
        $query = Profile::query()->with(['user']);

        // ادمین نباید سوپرادمین‌ها را در جدول ببیند
        if ($this->isAdmin()) {
            $query->where('role_id', '!=', 1);
        }

        // جستجو بر اساس کد ملی
        if (!empty(trim($this->search_national_code))) {
            $query->where('national_code', 'like', '%' . trim($this->search_national_code) . '%');
        }

        // جستجو بر اساس نام خانوادگی
        if (!empty(trim($this->search_last_name))) {
            $query->where('last_name', 'like', '%' . trim($this->search_last_name) . '%');
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
            // سوپرادمین می‌تواند ادمین و بقیه پرسنل را تعریف کند (به‌جز سوپرادمین جدید)
            $query->where('id', '!=', 1);
        } elseif ($this->isAdmin()) {
            // ادمین نه می‌تواند سوپرادمین بسازد و نه ادمین جدید
            $query->whereNotIn('id', [1, 2]);
        } else {
            $query->whereNotIn('id', [1, 2]);
        }

        return $query->get(['id', 'name']);
    }

    public function openAddModal()
    {
        $this->reset_deta();
        \Flux\Flux::modal('add-user')->show();
    }

    public function reset_deta()
    {
        $this->reset([
            'user_name', 'password', 'confirm_password',
            'f_name', 'l_name', 'phone', 'national_code',
            'address', 'role_id', 'profile_id', 'user_id',
        ]);
        $this->resetValidation();
    }

    public function save()
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

        // اعمال محدودیت سطح نقش بر اساس نقش شخص استخدام‌کننده
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
                ]);
            });

            session()->flash('success', 'کاربر جدید با موفقیت استخدام و ثبت شد.');
            \Flux\Flux::modal('add-user')->close();
            $this->reset_deta();

        } catch (\Throwable $e) {
            $this->addError('save_error', 'خطایی در ثبت اطلاعات رخ داد: ' . $e->getMessage());
        }
    }

    public function delete_form(int $profileId)
    {
        $profile = Profile::findOrFail($profileId);

        // کاربر نمی‌تواند خودش را اخراج کند
        if ((int) $profile->user_id === (int) Auth::id()) {
            session()->flash('error', 'شما نمی‌توانید حساب کاربری خودتان را اخراج/حذف کنید.');
            return;
        }

        // ادمین نمی‌تواند سوپرادمین یا ادمین دیگر را اخراج کند
        if ($this->isAdmin() && in_array((int) $profile->role_id, [1, 2])) {
            session()->flash('error', 'شما اجازه اخراج پرسنل در این سطح دسترسی را ندارید.');
            return;
        }

        $this->profile_id = $profile->id;
        $this->user_id = $profile->user_id;
        $this->f_name = $profile->first_name;
        $this->l_name = $profile->last_name;

        \Flux\Flux::modal('delete-user')->show();
    }

    public function delete()
    {
        try {
            $profile = Profile::findOrFail($this->profile_id);

            if ((int) $profile->user_id === (int) Auth::id()) {
                session()->flash('error', 'امکان حذف حساب کاربری خودتان وجود ندارد.');
                \Flux\Flux::modal('delete-user')->close();
                return;
            }

            if ($this->isAdmin() && in_array((int) $profile->role_id, [1, 2])) {
                session()->flash('error', 'شما اجازه اخراج این کاربر را ندارید.');
                \Flux\Flux::modal('delete-user')->close();
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

            session()->flash('success', 'کاربر با موفقیت از سیستم حذف و اخراج گردید.');
            \Flux\Flux::modal('delete-user')->close();
            $this->reset_deta();

        } catch (\Throwable $e) {
            session()->flash('error', 'خطا در اخراج کاربر: ' . $e->getMessage());
            \Flux\Flux::modal('delete-user')->close();
        }
    }
};

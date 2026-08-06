<?php

use App\Models\Profile;
use App\Models\Role;
use App\Models\User;
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

    // شناسه نقش انتخابی
    public $role_id = '';

    // شناسه پروفایل و کاربر در حال ویرایش یا حذف
    public $profile_id = null;
    public $user_id = null;

    // متغیرهای مرتب‌سازی جدول
    public $sortBy = 'first_name';
    public $sortDirection = 'asc';

    public function sort($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    #[Computed]
    public function profiles()
    {
        // حذف roles از with برای جلوگیری از اجرای کوئری جدول واسط غیرموجود profile_role
        return Profile::query()
            ->with(['user'])
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);
    }

    #[Computed]
    public function roles()
    {
        return Role::query()->orderBy('name')->get(['id', 'name']);
    }

    // متد باز کردن مودال ثبت کاربر جدید با فرم کاملاً پاک‌سازی شده
    public function openAddModal()
    {
        $this->reset_deta();
        Flux::modal('add-user')->show();
    }

    // متد پاک‌سازی اطلاعات فرم و خطاها
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
        $this->validate([
            'user_name' => ['required', 'string', 'min:4', 'unique:users,user_name'],
            'password' => ['required', 'string', 'min:6'],
            'confirm_password' => ['required', 'same:password'],
            'f_name' => ['required', 'string', 'max:100'],
            'l_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'regex:/^09[0-9]{9}$/'],
            'national_code' => ['required', 'digits:10', 'unique:profiles,national_code'],
            'address' => ['required', 'string', 'max:500'],
            'role_id' => ['required', 'exists:roles,id'],
        ], [
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
        ]);

        try {
            DB::transaction(function () {
                // ایجاد کاربر جدید بدون فیلد نقش در جدول users
                $user = User::create([
                    'user_name' => $this->user_name,
                    'password' => Hash::make($this->password),
                ]);

                // ایجاد پروفایل مرتبط و قرار دادن مقدار کلید خارجی role_id در جدول profiles
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
            Flux::modal('add-user')->close();
            $this->reset_deta();

        } catch (\Throwable $e) {
            $this->addError('save_error', 'خطایی در ثبت اطلاعات رخ داد: ' . $e->getMessage());
        }
    }

    public function edit(int $profileId)
    {
        $this->resetValidation();

        $profile = Profile::with(['user'])->findOrFail($profileId);

        $this->profile_id = $profile->id;
        $this->user_id = $profile->user_id;

        $this->f_name = $profile->first_name;
        $this->l_name = $profile->last_name;
        $this->phone = $profile->phone;
        $this->national_code = $profile->national_code;
        $this->address = $profile->address;

        // دریافت مستقیم نقش ذخیره شده در ستون role_id پروفایل
        $this->role_id = $profile->role_id;

        $user = $profile->user;
        if ($user) {
            $this->user_name = $user->user_name;
        }

        $this->password = '';
        $this->confirm_password = '';

        Flux::modal('edit-user')->show();
    }

    public function update()
    {
        $this->validate([
            'user_name' => ['required', 'string', 'min:4', 'unique:users,user_name,'.$this->user_id],
            'password' => ['nullable', 'string', 'min:6'],
            'confirm_password' => ['nullable', 'required_with:password', 'same:password'],
            'f_name' => ['required', 'string', 'max:100'],
            'l_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'regex:/^09[0-9]{9}$/'],
            'national_code' => ['required', 'digits:10', 'unique:profiles,national_code,'.$this->profile_id],
            'address' => ['required', 'string', 'max:500'],
            'role_id' => ['required', 'exists:roles,id'],
        ], [
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
        ]);

        try {
            DB::transaction(function () {
                $user = User::findOrFail($this->user_id);
                $profile = Profile::findOrFail($this->profile_id);

                $userData = [
                    'user_name' => $this->user_name,
                ];

                if (! empty($this->password)) {
                    $userData['password'] = Hash::make($this->password);
                }

                $user->update($userData);

                $profile->update([
                    'first_name' => $this->f_name,
                    'last_name' => $this->l_name,
                    'phone' => $this->phone,
                    'national_code' => $this->national_code,
                    'address' => $this->address,
                    'role_id' => $this->role_id, // بروزرسانی فیلد نقش در پروفایل
                ]);
            });

            session()->flash('success', 'اطلاعات کاربر با موفقیت ویرایش شد.');
            Flux::modal('edit-user')->close();
            $this->reset_deta();

        } catch (\Throwable $e) {
            $this->addError('update_error', 'خطایی در ویرایش اطلاعات رخ داد: '.$e->getMessage());
        }
    }

    public function delete_form(int $profileId)
    {
        $profile = Profile::findOrFail($profileId);

        $this->profile_id = $profile->id;
        $this->user_id = $profile->user_id;
        $this->f_name = $profile->first_name;
        $this->l_name = $profile->last_name;

        Flux::modal('delete-user')->show();
    }

    public function delete()
    {
        try {
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

        } catch (\Throwable $e) {
            session()->flash('error', 'خطا در حذف کاربر: '.$e->getMessage());
            Flux::modal('delete-user')->close();
        }
    }
};

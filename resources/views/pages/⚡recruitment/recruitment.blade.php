<div class="space-y-6">
    {{-- پیام‌های موفقیت و خطا --}}
    @if (session()->has('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    {{-- نوار ابزار بالا: دکمه استخدام و فیلترهای متنی جستجو --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:button variant="primary" color="green" wire:click="openAddModal">استخدام پرسنل جدید</flux:button>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="w-40 sm:w-64 md:w-80">
                <flux:input
                    wire:model.live.debounce.300ms="search_national_code"
                    placeholder="جستجو با کد ملی..."
                    icon="identification"
                    clearable
                />
            </div>

            <div class="w-40 sm:w-64 md:w-80">
                <flux:input
                    wire:model.live.debounce.300ms="search_last_name"
                    placeholder="جستجو با نام خانوادگی..."
                    icon="user"
                    clearable
                />
            </div>
        </div>
    </div>

    {{-- باکس فیلتر آبشاری تاریخ شمسی (سال -> ماه -> روز) --}}
    <div class="rounded-xl border border-gray-200 bg-gray-50/80 p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800/80">
        <div class="mb-3 flex items-center justify-between">
            <span class="text-xs font-bold text-gray-700 dark:text-gray-200">فیلتر تقویم شمسی:</span>
            @if(!empty($selected_year))
                <button
                    type="button"
                    wire:click="clearDateFilters"
                    class="rounded bg-red-100 px-2 py-1 text-xs font-medium text-red-600 transition hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400"
                >
                    حذف فیلترهای تاریخ
                </button>
            @endif
        </div>

        <div class="space-y-3">
            {{-- ۱. انتخاب سال --}}
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-semibold text-gray-500 w-12">سال:</span>
                <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-700 shadow-sm transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-300">
                    <input
                        type="radio"
                        name="year_filter"
                        value=""
                        wire:model.live="selected_year"
                        class="text-blue-600 focus:ring-blue-500"
                    />
                    <span>همه سال‌ها</span>
                </label>

                @foreach($this->availableYears as $year)
                    <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-700 shadow-sm transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-300" wire:key="year-{{ $year }}">
                        <input
                            type="radio"
                            name="year_filter"
                            value="{{ $year }}"
                            wire:model.live="selected_year"
                            class="text-blue-600 focus:ring-blue-500"
                        />
                        <span class="font-bold">{{ $year }}</span>
                    </label>
                @endforeach
            </div>

            {{-- ۲. انتخاب ماه (تنها در صورت انتخاب سال فعال می‌شود) --}}
            @if(!empty($selected_year) && count($this->availableMonths) > 0)
                <div class="flex flex-wrap items-center gap-2 border-t border-gray-200 pt-3 dark:border-gray-700">
                    <span class="text-xs font-semibold text-gray-500 w-12">ماه:</span>
                    <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-700 shadow-sm transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-300">
                        <input
                            type="radio"
                            name="month_filter"
                            value=""
                            wire:model.live="selected_month"
                            class="text-blue-600 focus:ring-blue-500"
                        />
                        <span>تمام ماه‌های {{ $selected_year }}</span>
                    </label>

                    @foreach($this->availableMonths as $month)
                        <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-700 shadow-sm transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-300" wire:key="month-{{ $month }}">
                            <input
                                type="radio"
                                name="month_filter"
                                value="{{ $month }}"
                                wire:model.live="selected_month"
                                class="text-blue-600 focus:ring-blue-500"
                            />
                            <span>{{ $persianMonths[$month] ?? $month }}</span>
                        </label>
                    @endforeach
                </div>
            @endif

            {{-- ۳. انتخاب روز (تنها در صورت انتخاب ماه فعال می‌شود) --}}
            @if(!empty($selected_year) && !empty($selected_month) && count($this->availableDays) > 0)
                <div class="flex flex-wrap items-center gap-2 border-t border-gray-200 pt-3 dark:border-gray-700">
                    <span class="text-xs font-semibold text-gray-500 w-12">روز:</span>
                    <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-700 shadow-sm transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-300">
                        <input
                            type="radio"
                            name="day_filter"
                            value=""
                            wire:model.live="selected_day"
                            class="text-blue-600 focus:ring-blue-500"
                        />
                        <span>تمام روزهای {{ $persianMonths[(int)$selected_month] }}</span>
                    </label>

                    @foreach($this->availableDays as $day)
                        <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs text-gray-700 shadow-sm transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-300" wire:key="day-{{ $day }}">
                            <input
                                type="radio"
                                name="day_filter"
                                value="{{ $day }}"
                                wire:model.live="selected_day"
                                class="text-blue-600 focus:ring-blue-500"
                            />
                            <span>{{ $day }}</span>
                        </label>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- جدول اطلاعات پرسنل --}}
    <flux:table :paginate="$this->profiles">
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'first_name'" :direction="$sortDirection" wire:click="sort('first_name')">
                نام
            </flux:table.column>

            <flux:table.column sortable :sorted="$sortBy === 'last_name'" :direction="$sortDirection" wire:click="sort('last_name')">
                نام خانوادگی
            </flux:table.column>

            <flux:table.column>
                نام کاربری
            </flux:table.column>

            <flux:table.column sortable :sorted="$sortBy === 'phone'" :direction="$sortDirection" wire:click="sort('phone')">
                شماره تماس
            </flux:table.column>

            <flux:table.column sortable :sorted="$sortBy === 'national_code'" :direction="$sortDirection" wire:click="sort('national_code')">
                کد ملی
            </flux:table.column>

            <flux:table.column sortable :sorted="$sortBy === 'address'" :direction="$sortDirection" wire:click="sort('address')">
                آدرس
            </flux:table.column>

            <flux:table.column>
                پست / نقش
            </flux:table.column>

            <flux:table.column>
                عملیات
            </flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->profiles as $profile)
                <flux:table.row :key="$profile->id">
                    <flux:table.cell class="whitespace-nowrap">{{ $profile->first_name }}</flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">{{ $profile->last_name }}</flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">{{ $profile->user->user_name ?? '-' }}</flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">{{ $profile->phone }}</flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">{{ $profile->national_code }}</flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">{{ $profile->address }}</flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap">
                        @if((int) $profile->role_id === 1)
                            <span class="inline-flex items-center rounded-md bg-purple-50 px-2 py-1 text-xs font-medium text-purple-700 ring-1 ring-inset ring-purple-700/10">
                                سوپر ادمین
                            </span>
                        @elseif((int) $profile->role_id === 2)
                            <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-700/10">
                                مدیر (ادمین)
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                {{ $this->roles->firstWhere('id', $profile->role_id)->name ?? 'پرسنل' }}
                            </span>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap">
                        <div class="flex items-center gap-2">
                            <flux:button size="sm" variant="outline" wire:click="edit({{ $profile->id }})">
                                ویرایش
                            </flux:button>

                            @if($this->isSuperAdmin() && (int) $profile->user_id !== (int) auth()->id())
                                <flux:button size="sm" variant="primary" color="red" wire:click="delete_form({{ $profile->id }})">
                                    اخراج
                                </flux:button>
                            @endif
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="8">
                        <div class="py-6 text-center text-sm text-gray-500">
                            هیچ رکوردی یافت نشد.
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- مودال افزودن پرسنل --}}
    <flux:modal name="add-user" class="md:w-96" @close="reset_deta">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">استخدام پرسنل</flux:heading>
                <flux:text class="mt-2">برای استخدام پرسنل جدید فرم زیر را تکمیل نمایید</flux:text>
            </div>

            @error('save_error')
            <div class="rounded bg-red-50 p-2 text-xs font-bold text-red-600">{{ $message }}</div>
            @enderror

            <div>
                <flux:input wire:model="user_name" label="نام کاربری" placeholder="نام کاربری" autocomplete="off" />
                @error('user_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:input type="password" wire:model="password" label="رمز عبور" placeholder="رمز عبور" autocomplete="new-password" />
                @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:input type="password" wire:model="confirm_password" label="تکرار رمز عبور" placeholder="تکرار رمز عبور" autocomplete="new-password" />
                @error('confirm_password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:input wire:model="f_name" label="نام" placeholder="نام" />
                @error('f_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:input wire:model="l_name" label="نام خانوادگی" placeholder="نام خانوادگی" />
                @error('l_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:input wire:model="phone" label="شماره تماس" placeholder="شماره تماس" />
                @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:input wire:model="national_code" label="کد ملی" placeholder="کد ملی" />
                @error('national_code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:input wire:model="address" label="آدرس" placeholder="آدرس" />
                @error('address') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:select wire:model="role_id" label="نقش / سمت استخدامی">
                    <option value="">انتخاب نقش یا سمت</option>
                    @foreach ($this->roles as $role)
                        <flux:select.option value="{{ $role->id }}" wire:key="add-role-{{ $role->id }}">
                            {{ $role->name }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
                @error('role_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end space-x-2 space-x-reverse">
                <flux:modal.close>
                    <flux:button type="button" wire:click="reset_deta" variant="ghost">لغو</flux:button>
                </flux:modal.close>

                <flux:button wire:click="save" type="button" variant="primary">
                    برای استخدام نهایی کلیک کنید
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- مودال ویرایش پرسنل --}}
    <flux:modal name="edit-user" class="md:w-96" @close="reset_deta">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">ویرایش اطلاعات پرسنل</flux:heading>
                <flux:text class="mt-2">ویرایش مشخصات {{ $f_name . ' ' . $l_name }}</flux:text>
            </div>

            @error('update_error')
            <div class="rounded bg-red-50 p-2 text-xs font-bold text-red-600">{{ $message }}</div>
            @enderror

            <div>
                <flux:input wire:model="user_name" label="نام کاربری" placeholder="نام کاربری" autocomplete="off" />
                @error('user_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:input type="password" wire:model="password" label="رمز عبور جدید (اختیاری)" placeholder="در صورت عدم تغییر خالی بگذارید" autocomplete="new-password" />
                @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:input type="password" wire:model="confirm_password" label="تکرار رمز عبور جدید" placeholder="تکرار رمز عبور جدید" autocomplete="new-password" />
                @error('confirm_password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:input wire:model="f_name" label="نام" placeholder="نام" />
                @error('f_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:input wire:model="l_name" label="نام خانوادگی" placeholder="نام خانوادگی" />
                @error('l_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:input wire:model="phone" label="شماره تماس" placeholder="شماره تماس" />
                @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:input wire:model="national_code" label="کد ملی" placeholder="کد ملی" />
                @error('national_code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:input wire:model="address" label="آدرس" placeholder="آدرس" />
                @error('address') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            @if(!$isEditingSelf)
                <div>
                    <flux:select wire:model="role_id" label="نقش / سمت استخدامی">
                        <option value="">انتخاب نقش یا سمت</option>
                        @foreach ($this->roles as $role)
                            <flux:select.option value="{{ $role->id }}" wire:key="edit-role-{{ $role->id }}">
                                {{ $role->name }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    @error('role_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            @endif

            <div class="flex justify-end space-x-2 space-x-reverse">
                <flux:modal.close>
                    <flux:button type="button" wire:click="reset_deta" variant="ghost">لغو</flux:button>
                </flux:modal.close>

                <flux:button wire:click="update" type="button" variant="primary">
                    ذخیره تغییرات
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- مودال حذف / اخراج کاربر --}}
    <flux:modal name="delete-user" class="md:w-96" @close="reset_deta">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">اخراج پرسنل</flux:heading>
                <flux:text class="mt-2">آیا از اخراج {{ $f_name . ' ' . $l_name }} مطمئن هستید؟</flux:text>
            </div>

            <div class="flex justify-end space-x-2 space-x-reverse">
                <flux:modal.close>
                    <flux:button type="button" wire:click="reset_deta" variant="ghost">لغو</flux:button>
                </flux:modal.close>

                <flux:button wire:click="delete" type="button" color="red" variant="primary">
                    اخراج
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- دکمه بازگشت --}}
    <a
        href="{{ URL::signedRoute('PersonnelManagement_s_a') }}"
        class="inline-block rounded-lg !bg-blue-600 px-4 py-2 !text-white no-underline transition hover:!bg-blue-700"
        style="background-color: #2563eb !important; color: #ffffff !important;"
    >
        برگشت
    </a>
</div>

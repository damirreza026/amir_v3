<div class="space-y-6">
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

    <div class="flex flex-wrap items-center justify-end gap-3">
        <div class="w-48">
            <flux:input
                wire:model.live.debounce.300ms="search_national_code"
                placeholder="جستجو با کد ملی..."
                icon="identification"
                clearable
            />
        </div>

        <div class="w-48">
            <flux:input
                wire:model.live.debounce.300ms="search_last_name"
                placeholder="جستجو با نام خانوادگی..."
                icon="user"
                clearable
            />
        </div>
    </div>

    <flux:table :paginate="$this->profiles">
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'first_name'" :direction="$sortDirection" wire:click="sort('first_name')">
                نام
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'last_name'" :direction="$sortDirection" wire:click="sort('last_name')">
                نام خانوادگی
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
            <flux:table.column>عملیات</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->profiles as $profile)
                <flux:table.row :key="$profile->id">
                    <flux:table.cell class="whitespace-nowrap">{{ $profile->first_name }}</flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">{{ $profile->last_name }}</flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">{{ $profile->phone }}</flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">{{ $profile->national_code }}</flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">{{ $profile->address }}</flux:table.cell>

                    <!-- نمایش نام نقش بر اساس role_id -->
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
                        <div class="flex space-x-2 space-x-reverse">
                            <flux:button variant="primary" color="yellow" wire:click="edit({{ $profile->id }})">ویرایش</flux:button>

                            {{-- اگر کاربر خودش باشد دکمه حذف نشان داده نمی‌شود --}}
                            @if((int)$profile->user_id !== (int)auth()->id())
                                <flux:button variant="primary" color="red" wire:click="delete_form({{ $profile->id }})">حذف</flux:button>
                            @endif
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="7">
                        <div class="py-6 text-center text-sm text-gray-500">
                            هیچ رکوردی یافت نشد.
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- مودال ویرایش کاربر --}}
    <flux:modal name="edit-user" class="md:w-96" @close="reset_deta">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">ویرایش پرسنل</flux:heading>
                <flux:text class="mt-2">اطلاعات پرسنل را ویرایش نمایید</flux:text>
            </div>

            @error('update_error')
            <div class="text-xs text-red-600 font-bold bg-red-50 p-2 rounded">{{ $message }}</div>
            @enderror

            <div>
                <flux:input wire:model="user_name" label="نام کاربری" placeholder="نام کاربری" autocomplete="off" />
                @error('user_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:input type="password" wire:model="password" label="رمز عبور (اختیاری)" placeholder="در صورت عدم تمایل به تغییر، خالی بگذارید" autocomplete="new-password" />
                @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:input type="password" wire:model="confirm_password" label="تکرار رمز عبور" placeholder="تکرار رمز عبور" autocomplete="new-password" />
                @error('confirm_password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:input wire:model="f_name" label="نام" placeholder="نام"/>
                @error('f_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:input wire:model="l_name" label="نام خانوادگی" placeholder="نام خانوادگی" />
                @error('l_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:input wire:model="phone" label="شماره تماس" placeholder="شماره تماس" />
                @error('phone') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:input wire:model="national_code" label="کد ملی" placeholder="کد ملی"/>
                @error('national_code') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:input wire:model="address" label="آدرس" placeholder="آدرس" />
                @error('address') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- اگر کاربر در حال ویرایش خودش باشد فیلد نقش مخفی و قفل است --}}
            @if(! $isEditingSelf)
                <div>
                    <flux:select wire:model="role_id" label="نقش / سمت">
                        <option value="">انتخاب نقش یا سمت</option>
                        @foreach ($this->roles as $role)
                            <flux:select.option value="{{ $role->id }}" wire:key="edit-role-{{ $role->id }}">
                                {{ $role->name }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    @error('role_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            @else
                <div class="rounded-lg bg-gray-50 p-3 text-xs text-gray-500 border border-gray-200">
                    🔒 نقش و سمت حساب کاربری شما توسط خودتان قابل تغییر نیست.
                </div>
            @endif

            <div class="flex">
                <flux:spacer/>
                <flux:button wire:click="update()" type="submit" variant="primary">تغییر نهایی</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- مودال حذف کاربر --}}
    <flux:modal name="delete-user" class="md:w-96" @close="reset_deta">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">حذف پرسنل</flux:heading>
                <flux:text class="mt-2">آیا از حذف {{ $f_name.' '.$l_name }} مطمئن هستید؟</flux:text>
            </div>

            <div class="flex space-x-2 space-x-reverse justify-end">
                <flux:button wire:click="reset_deta" variant="ghost">لغو</flux:button>
                <flux:button wire:click="delete()" type="submit" color="red" variant="primary">حذف</flux:button>
            </div>
        </div>
    </flux:modal>

    <a
        href="{{ URL::signedRoute('PersonnelManagement_s_a') }}"
        class="inline-block rounded-lg !bg-blue-600 px-4 py-2 !text-white no-underline transition hover:!bg-blue-700"
        style="background-color: #2563eb !important; color: #ffffff !important;"
    >
        برگشت
    </a>
</div>

<div dir="rtl" class="space-y-6">

    {{-- هدر بخش مشتریان --}}
    <div class="flex flex-col justify-between gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm md:flex-row md:items-center">
        <div>
            <flux:heading size="xl" level="1">
                مدیریت مشتریان
            </flux:heading>

            <flux:subheading class="mt-1">
                لیست مشتریان ثبت‌شده در سیستم و امکان مدیریت آن‌ها
            </flux:subheading>
            <p class="mt-1 text-xs font-semibold text-gray-600">
                کاربر فعال: {{ auth()->user()?->profile?->first_name }} {{ auth()->user()?->profile?->last_name }}
            </p>
        </div>

        <flux:button
            type="button"
            wire:click="openSaveModal"
            variant="primary"
            color="green"
        >
            افزودن مشتری جدید
        </flux:button>
    </div>

    <hr class="border-zinc-200 dark:border-zinc-700">

    {{-- پیام‌های موفقیت و خطا --}}
    @if (session()->has('success'))
        <div
            class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700
                   dark:border-green-800 dark:bg-green-950/40 dark:text-green-300"
        >
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div
            class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700
                   dark:border-red-800 dark:bg-red-950/40 dark:text-red-300"
        >
            {{ session('error') }}
        </div>
    @endif

    {{-- فیلدهای جست‌وجوی زنده --}}
    <div class="flex flex-wrap items-center gap-4">
        <div class="w-full sm:w-72">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="جست‌وجوی نام مشتری..."
                autocomplete="off"
                clearable
            />
        </div>

        <div class="w-full sm:w-72">
            <flux:input
                wire:model.live.debounce.300ms="search_employee"
                placeholder="جست‌وجوی ثبت‌کننده..."
                autocomplete="off"
                clearable
            />
        </div>
    </div>

    {{-- باکس فیلتر آبشاری تقویم شمسی (سال -> ماه -> روز) --}}
    <div class="rounded-xl border border-gray-200 bg-gray-50/80 p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800/80">
        <div class="mb-3 flex items-center justify-between">
            <span class="text-xs font-bold text-gray-700 dark:text-gray-200">فیلتر تقویم شمسی:</span>
            @if(!empty($selected_year))
                <button
                    type="button"
                    wire:click="clearDateFilters"
                    class="rounded bg-red-100 px-2 py-1 text-xs font-medium text-red-600 transition hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400 !cursor-pointer"
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
                        name="customer_year_filter"
                        value=""
                        wire:model.live="selected_year"
                        class="text-blue-600 focus:ring-blue-500"
                    />
                    <span>همه سال‌ها</span>
                </label>

                @foreach($this->availableYears as $year)
                    <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-700 shadow-sm transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-300" wire:key="cust-year-{{ $year }}">
                        <input
                            type="radio"
                            name="customer_year_filter"
                            value="{{ $year }}"
                            wire:model.live="selected_year"
                            class="text-blue-600 focus:ring-blue-500"
                        />
                        <span class="font-bold">{{ $year }}</span>
                    </label>
                @endforeach
            </div>

            {{-- ۲. انتخاب ماه --}}
            @if(!empty($selected_year) && count($this->availableMonths) > 0)
                <div class="flex flex-wrap items-center gap-2 border-t border-gray-200 pt-3 dark:border-gray-700">
                    <span class="text-xs font-semibold text-gray-500 w-12">ماه:</span>
                    <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-700 shadow-sm transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-300">
                        <input
                            type="radio"
                            name="customer_month_filter"
                            value=""
                            wire:model.live="selected_month"
                            class="text-blue-600 focus:ring-blue-500"
                        />
                        <span>تمام ماه‌های {{ $selected_year }}</span>
                    </label>

                    @foreach($this->availableMonths as $month)
                        <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-700 shadow-sm transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-300" wire:key="cust-month-{{ $month }}">
                            <input
                                type="radio"
                                name="customer_month_filter"
                                value="{{ $month }}"
                                wire:model.live="selected_month"
                                class="text-blue-600 focus:ring-blue-500"
                            />
                            <span>{{ $persianMonths[$month] ?? $month }}</span>
                        </label>
                    @endforeach
                </div>
            @endif

            {{-- ۳. انتخاب روز --}}
            @if(!empty($selected_year) && !empty($selected_month) && count($this->availableDays) > 0)
                <div class="flex flex-wrap items-center gap-2 border-t border-gray-200 pt-3 dark:border-gray-700">
                    <span class="text-xs font-semibold text-gray-500 w-12">روز:</span>
                    <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-700 shadow-sm transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-300">
                        <input
                            type="radio"
                            name="customer_day_filter"
                            value=""
                            wire:model.live="selected_day"
                            class="text-blue-600 focus:ring-blue-500"
                        />
                        <span>تمام روزهای {{ $persianMonths[(int)$selected_month] ?? $selected_month }}</span>
                    </label>

                    @foreach($this->availableDays as $day)
                        <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs text-gray-700 shadow-sm transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-300" wire:key="cust-day-{{ $day }}">
                            <input
                                type="radio"
                                name="customer_day_filter"
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

    {{-- جدول مشتریان --}}
    <flux:table :paginate="$this->customers">

        <flux:table.columns>
            <flux:table.column
                sortable
                :sorted="$sortBy === 'shop_name'"
                :direction="$sortDirection"
                wire:click="sort('shop_name')"
            >
                نام مشتری / فروشگاه
            </flux:table.column>

            <flux:table.column
                sortable
                :sorted="$sortBy === 'phone'"
                :direction="$sortDirection"
                wire:click="sort('phone')"
            >
                شماره تماس
            </flux:table.column>

            {{-- ستون ثبت شده توسط --}}
            <flux:table.column>
                ثبت شده توسط
            </flux:table.column>

            {{-- ستون تاریخ و زمان ثبت --}}
            <flux:table.column
                sortable
                :sorted="$sortBy === 'created_at'"
                :direction="$sortDirection"
                wire:click="sort('created_at')"
            >
                تاریخ و زمان ثبت
            </flux:table.column>

            <flux:table.column>
                آدرس
            </flux:table.column>

            <flux:table.column>
                عملیات
            </flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->customers as $customer)
                <flux:table.row wire:key="customer-{{ $customer->id }}">

                    {{-- نام مشتری --}}
                    <flux:table.cell class="whitespace-nowrap font-medium">
                        {{ $customer->shop_name }}
                    </flux:table.cell>

                    {{-- شماره تماس --}}
                    <flux:table.cell class="whitespace-nowrap font-mono text-zinc-600 dark:text-zinc-300">
                        {{ $customer->phone ?: '-' }}
                    </flux:table.cell>

                    {{-- ستون ثبت شده توسط --}}
                    <flux:table.cell class="whitespace-nowrap text-xs font-bold text-gray-700">
                        @php
                            $employeeName = trim(
                                ($customer->user?->profile?->first_name ?? '') . ' ' .
                                ($customer->user?->profile?->last_name ?? '')
                            );
                        @endphp

                        {{ $employeeName !== '' ? $employeeName : '-' }}
                    </flux:table.cell>

                    {{-- ستون تاریخ و زمان ثبت به شمسی --}}
                    <flux:table.cell class="whitespace-nowrap text-xs font-bold text-gray-600">
                        @if($customer->created_at)
                            @php
                                try {
                                    $carbonTehran = \Carbon\Carbon::parse($customer->created_at)->setTimezone('Asia/Tehran');
                                    $jalaliDate = \Morilog\Jalali\Jalalian::fromCarbon($carbonTehran);
                                } catch (\Throwable $e) {
                                    $jalaliDate = null;
                                }
                            @endphp

                            @if($jalaliDate)
                                <div class="flex items-center gap-2">
                                    <span dir="ltr">{{ $jalaliDate->format('Y/m/d') }}</span>
                                    <span dir="ltr" class="rounded bg-gray-100 px-1.5 py-0.5 text-[11px] font-medium text-gray-500">
                                        {{ $jalaliDate->format('H:i') }}
                                    </span>
                                </div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </flux:table.cell>

                    {{-- آدرس --}}
                    <flux:table.cell class="max-w-xs truncate" title="{{ $customer->address }}">
                        {{ $customer->address ?: '-' }}
                    </flux:table.cell>

                    {{-- عملیات --}}
                    <flux:table.cell class="whitespace-nowrap">
                        <div class="flex items-center gap-2">

                            <flux:button
                                type="button"
                                wire:click="edit({{ $customer->id }})"
                                wire:loading.attr="disabled"
                                wire:target="edit({{ $customer->id }})"
                                variant="primary"
                                color="yellow"
                                size="sm"
                            >
                                ویرایش
                            </flux:button>

                            <flux:button
                                type="button"
                                wire:click="del_form({{ $customer->id }})"
                                wire:loading.attr="disabled"
                                wire:target="del_form({{ $customer->id }})"
                                variant="primary"
                                color="red"
                                size="sm"
                            >
                                حذف
                            </flux:button>

                        </div>
                    </flux:table.cell>

                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell
                        colspan="6"
                        class="py-8 text-center text-zinc-500 dark:text-zinc-400"
                    >
                        مشتری‌ای یافت نشد.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>

    </flux:table>


    {{-- ====================================================== --}}
    {{-- مودال افزودن مشتری --}}
    {{-- ====================================================== --}}

    <flux:modal
        name="save"
        class="md:w-[30rem]"
    >
        <div class="space-y-6">

            <div>
                <flux:heading size="lg">
                    افزودن مشتری
                </flux:heading>

                <flux:text class="mt-2">
                    برای افزودن مشتری جدید اطلاعات زیر را کامل کنید.
                </flux:text>
            </div>

            @error('save_error')
            <div
                class="rounded-md border border-red-200 bg-red-50 p-3 text-sm font-medium text-red-600
                       dark:border-red-800 dark:bg-red-950/40 dark:text-red-300"
            >
                {{ $message }}
            </div>
            @enderror

            <div class="space-y-4">
                <div>
                    <flux:input
                        wire:model="shop_name"
                        label="نام مشتری / فروشگاه"
                        placeholder="مثال: فروشگاه رضایی"
                        autocomplete="off"
                    />
                    @error('shop_name')
                    <p class="mt-1 text-xs font-medium text-red-600">
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                <div>
                    <flux:input
                        wire:model="phone"
                        label="شماره تماس"
                        placeholder="مثال: 09123456789"
                        autocomplete="off"
                    />
                    @error('phone')
                    <p class="mt-1 text-xs font-medium text-red-600">
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                <div>
                    <flux:input
                        wire:model="address"
                        label="آدرس"
                        placeholder="آدرس کامل مشتری..."
                        autocomplete="off"
                    />
                    @error('address')
                    <p class="mt-1 text-xs font-medium text-red-600">
                        {{ $message }}
                    </p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button
                        type="button"
                        wire:click="resetForm"
                        variant="ghost"
                    >
                        انصراف
                    </flux:button>
                </flux:modal.close>

                <flux:button
                    type="button"
                    wire:click="save"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    variant="primary"
                    color="green"
                >
                    <span wire:loading.remove wire:target="save">
                        ثبت مشتری
                    </span>

                    <span wire:loading wire:target="save">
                        در حال ثبت...
                    </span>
                </flux:button>
            </div>

        </div>
    </flux:modal>


    {{-- ====================================================== --}}
    {{-- مودال ویرایش مشتری --}}
    {{-- ====================================================== --}}

    <flux:modal
        name="edit"
        class="md:w-[30rem]"
    >
        <div class="space-y-6">

            <div>
                <flux:heading size="lg">
                    ویرایش اطلاعات مشتری
                </flux:heading>

                <flux:text class="mt-2">
                    اطلاعات مورد نظر را ویرایش کنید.
                </flux:text>
            </div>

            @error('update_error')
            <div
                class="rounded-md border border-red-200 bg-red-50 p-3 text-sm font-medium text-red-600
                       dark:border-red-800 dark:bg-red-950/40 dark:text-red-300"
            >
                {{ $message }}
            </div>
            @enderror

            <div class="space-y-4">
                <div>
                    <flux:input
                        wire:model="shop_name"
                        label="نام مشتری / فروشگاه"
                        placeholder="نام مشتری"
                        autocomplete="off"
                    />
                    @error('shop_name')
                    <p class="mt-1 text-xs font-medium text-red-600">
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                <div>
                    <flux:input
                        wire:model="phone"
                        label="شماره تماس"
                        placeholder="شماره تماس"
                        autocomplete="off"
                    />
                    @error('phone')
                    <p class="mt-1 text-xs font-medium text-red-600">
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                <div>
                    <flux:input
                        wire:model="address"
                        label="آدرس"
                        placeholder="آدرس"
                        autocomplete="off"
                    />
                    @error('address')
                    <p class="mt-1 text-xs font-medium text-red-600">
                        {{ $message }}
                    </p>
                    @enderror
                </div>
            </div>

            @error('cust_id')
            <p class="text-xs font-medium text-red-600">
                {{ $message }}
            </p>
            @enderror

            <div class="flex items-center gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button
                        type="button"
                        wire:click="resetForm"
                        variant="ghost"
                    >
                        انصراف
                    </flux:button>
                </flux:modal.close>

                <flux:button
                    type="button"
                    wire:click="update"
                    wire:loading.attr="disabled"
                    wire:target="update"
                    variant="primary"
                >
                    <span wire:loading.remove wire:target="update">
                        ذخیره تغییرات
                    </span>

                    <span wire:loading wire:target="update">
                        در حال ذخیره...
                    </span>
                </flux:button>
            </div>

        </div>
    </flux:modal>


    {{-- ====================================================== --}}
    {{-- مودال حذف مشتری --}}
    {{-- ====================================================== --}}

    <flux:modal
        name="delete"
        class="min-w-[22rem]"
    >
        <div class="space-y-6">

            <div>
                <flux:heading size="lg">
                    حذف مشتری
                </flux:heading>

                <flux:text class="mt-2">
                    آیا از حذف مشتری با نام
                    <strong class="font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ $shop_name }}
                    </strong>
                    اطمینان دارید؟
                </flux:text>
            </div>

            <div
                class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700
                   dark:border-red-800 dark:bg-red-950/40 dark:text-red-300"
            >
                این عملیات غیرقابل بازگشت است.
            </div>

            @error('cust_id')
            <p class="text-xs font-medium text-red-600">
                {{ $message }}
            </p>
            @enderror

            <div class="flex items-center justify-end gap-2">
                <flux:modal.close>
                    <flux:button
                        type="button"
                        wire:click="resetForm"
                        variant="ghost"
                    >
                        انصراف
                    </flux:button>
                </flux:modal.close>

                <flux:button
                    type="button"
                    wire:click="delete"
                    wire:loading.attr="disabled"
                    wire:target="delete"
                    color="red"
                    variant="primary"
                >
                    <span wire:loading.remove wire:target="delete">
                        حذف
                    </span>

                    <span wire:loading wire:target="delete">
                        در حال حذف...
                    </span>
                </flux:button>
            </div>

        </div>
    </flux:modal>


    {{-- دکمه بازگشت --}}
    <div>
        <a
            href="{{ URL::signedRoute('customer_s_a') }}"
            class="inline-block rounded-lg !bg-blue-600 px-4 py-2 !text-white no-underline transition hover:!bg-blue-700"
            style="background-color: #2563eb !important; color: #ffffff !important;"
        >
            برگشت
        </a>
    </div>

</div>

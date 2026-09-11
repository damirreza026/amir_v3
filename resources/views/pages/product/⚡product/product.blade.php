<div dir="rtl" class="space-y-6">
    {{-- هدر صفحه و دکمه افزودن --}}
    <div class="flex flex-col justify-between gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm md:flex-row md:items-center">
        <div>
            <h1 class="text-xl font-bold"> مدل ها و نمونه های {{ $category->name }}</h1>
            <p class="mt-1 text-sm text-gray-500">لیست تمام مدل ها و نمونه های {{ $category->name }}</p>
            <p class="mt-1 text-xs font-semibold text-gray-600">
                کاربر فعال: {{ Auth::user()?->profile?->first_name }} {{ Auth::user()?->profile?->last_name }}
            </p>
        </div>
        <div>
            <flux:button wire:click="openSaveModal" variant="primary" color="green">
                افزودن مدل یا نمونه جدید {{ $category->name }}
            </flux:button>
        </div>
    </div>

    <hr class="border-gray-200">

    {{-- نمایش پیام‌های موفقیت و خطا --}}
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

    <div class="max-w-xs">
        <flux:input
            wire:model.live.debounce.300ms="search"
            placeholder="جست‌وجو بر اساس نام محصول..."
            autocomplete="off"
            clearable
        />
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

            {{-- ۲. انتخاب ماه --}}
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

            {{-- ۳. انتخاب روز --}}
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

    {{-- جدول لیست محصولات --}}
    <flux:table :paginate="$this->products">
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection" wire:click="sort('name')">
                نام محصول
            </flux:table.column>
            <flux:table.column>ثبت شده توسط</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">
                تاریخ و زمان ثبت
            </flux:table.column>
            <flux:table.column>عملیات</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->products as $product)
                <flux:table.row :key="$product->id">
                    <flux:table.cell class="whitespace-nowrap font-medium">{{ $product->name }}</flux:table.cell>

                    {{-- ۱. ثبت شده توسط --}}
                    <flux:table.cell class="whitespace-nowrap text-xs font-bold text-gray-700">
                        @if($product->user)
                            <span>{{ $product->user->profile?->first_name }} {{ $product->user->profile?->last_name }}</span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </flux:table.cell>

                    {{-- ۲. تاریخ و زمان ثبت بر اساس تایم‌زون تهران --}}
                    <flux:table.cell class="whitespace-nowrap text-xs font-bold text-gray-600">
                        @if($product->created_at)
                            @php
                                try {
                                    $carbonTehran = \Carbon\Carbon::parse($product->created_at)->setTimezone('Asia/Tehran');
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

                    <flux:table.cell class="whitespace-nowrap">
                        <div class="flex space-x-2 space-x-reverse">
                            <flux:button variant="primary" color="yellow" size="sm" wire:click="edit({{ $product->id }})">ویرایش</flux:button>
                            <flux:button variant="primary" color="red" size="sm" wire:click="delete_form({{ $product->id }})">حذف</flux:button>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="4" class="text-center py-6 text-sm text-gray-500">
                        موردی یافت نشد.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- مودال افزودن محصول جدید --}}
    <flux:modal name="save" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">نمونه یا مدل جدید</flux:heading>
                <flux:text class="mt-2">افزودن مدل یا نمونه به "{{ $category->name }}".</flux:text>
            </div>

            @error('save_error')
            <div class="text-xs text-red-600 font-bold bg-red-50 p-2 rounded">{{ $message }}</div>
            @enderror

            <div>
                <flux:input wire:model="name" label="نام محصول" placeholder="نام محصول" autocomplete="off" />
                @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost" wire:click="reset_data">لغو</flux:button>
                </flux:modal.close>
                <flux:button wire:click="save()" type="submit" variant="primary">ایجاد نهایی محصول</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- مودال ویرایش محصول --}}
    <flux:modal name="edit-user" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">ویرایش محصول</flux:heading>
                <flux:text class="mt-2">برای ویرایش محصول اطلاعات زیر را کامل کنید</flux:text>
            </div>

            @error('update_error')
            <div class="text-xs text-red-600 font-bold bg-red-50 p-2 rounded">{{ $message }}</div>
            @enderror

            <div>
                <flux:input wire:model="name" label="نام محصول" placeholder="نام محصول" autocomplete="off" />
                @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost" wire:click="reset_data">لغو</flux:button>
                </flux:modal.close>
                <flux:button wire:click="update()" type="submit" variant="primary">اعمال تغییرات</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- مودال حذف محصول --}}
    <flux:modal name="delete-user" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">حذف محصول</flux:heading>
                <flux:text class="mt-2">آیا از حذف محصول "{{ $name }}" مطمئن هستید؟</flux:text>
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost" wire:click="reset_data">لغو</flux:button>
                </flux:modal.close>
                <flux:button wire:click="delete()" type="submit" color="red" variant="primary">حذف نهایی</flux:button>
            </div>
        </div>
    </flux:modal>

    <a
        href="{{ URL::signedRoute('category') }}"
        class="inline-block rounded-lg !bg-blue-600 px-4 py-2 !text-white no-underline transition hover:!bg-blue-700"
        style="background-color: #2563eb !important; color: #ffffff !important;"
    >
        برگشت
    </a>
</div>

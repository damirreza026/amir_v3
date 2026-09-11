<div dir="rtl" class="space-y-4">

    @php
        $currentJalaliYear = (int) \Morilog\Jalali\Jalalian::now()->getYear();
        $startYear = $currentJalaliYear;
        $endYear = 1398;

        $formatJalaliDateTime = static function ($date, $time = null): array {
            if (blank($date)) {
                return ['date' => '---', 'time' => ''];
            }

            try {
                $rawString = (string) $date;
                if ($time) {
                    $rawString .= ' ' . (string) $time;
                }

                $carbonDate = \Carbon\Carbon::parse($rawString)->timezone('Asia/Tehran');
                $jalali = \Morilog\Jalali\Jalalian::fromCarbon($carbonDate);

                return [
                    'date' => $jalali->format('Y/m/d'),
                    'time' => $jalali->format('H:i:s'),
                ];
            } catch (\Throwable $e) {
                return [
                    'date' => (string) $date,
                    'time' => $time ? (string) $time : '',
                ];
            }
        };
    @endphp

    {{-- هدر صفحه --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" level="1">
                مدیریت فاکتورها
            </flux:heading>

            <flux:subheading class="mt-1">
                مشاهده، جست‌وجو، ثبت، ویرایش و حذف فاکتورهای فروش
            </flux:subheading>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <flux:modal.trigger name="save">
                <flux:button
                    type="button"
                    wire:click="openSaveModal"
                    variant="primary"
                    color="green"
                    class="w-full sm:w-auto"
                >
                    + افزودن فاکتور جدید
                </flux:button>
            </flux:modal.trigger>

            <a
                href="{{ URL::signedRoute('invoice_s_a') }}"
                class="inline-flex items-center justify-center rounded-lg !bg-blue-600 px-4 py-2 text-sm font-medium !text-white no-underline transition hover:!bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                style="background-color: #2563eb !important; color: #ffffff !important;"
            >
                برگشت
            </a>
        </div>
    </div>

    {{-- پیام خطا --}}
    @error('general')
    <div class="rounded-lg border border-red-300 bg-red-50 p-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-950/40 dark:text-red-300">
        {{ $message }}
    </div>
    @enderror

    {{-- نوار جست‌وجو و فیلتر تاریخ شمسی --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="space-y-3">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div class="w-full sm:max-w-md">
                    <flux:input
                        wire:model.live.debounce.400ms="search"
                        label="جست‌وجوی نام فروشگاه"
                        placeholder="نام فروشگاه را وارد کنید..."
                        autocomplete="off"
                        clearable
                    />
                </div>

                <div class="text-sm text-zinc-500 dark:text-zinc-400">
                    تعداد فاکتورها:
                    <span class="font-bold text-zinc-800 dark:text-zinc-200">
                        {{ $this->invoices->total() }}
                    </span>
                </div>
            </div>

            {{-- فیلتر بر اساس سال، ماه و روز شمسی پویا --}}
            <div class="grid grid-cols-1 gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800 sm:grid-cols-4">
                <div>
                    <flux:select wire:model.live="filter_year" label="فیلتر سال شمسی">
                        <option value="">همه سال‌ها</option>
                        @for ($y = $startYear; $y >= $endYear; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </flux:select>
                </div>

                <div>
                    <flux:select wire:model.live="filter_month" label="فیلتر ماه شمسی">
                        <option value="">همه ماه‌ها</option>
                        <option value="1">فروردین (۰۱)</option>
                        <option value="2">اردیبهشت (۰۲)</option>
                        <option value="3">خرداد (۰۳)</option>
                        <option value="4">تیر (۰۴)</option>
                        <option value="5">مرداد (۰۵)</option>
                        <option value="6">شهریور (۰۶)</option>
                        <option value="7">مهر (۰۷)</option>
                        <option value="8">آبان (۰۸)</option>
                        <option value="9">آذر (۰۹)</option>
                        <option value="10">دی (۱۰)</option>
                        <option value="11">بهمن (۱۱)</option>
                        <option value="12">اسفند (۱۲)</option>
                    </flux:select>
                </div>

                <div>
                    <flux:select wire:model.live="filter_day" label="فیلتر روز شمسی">
                        <option value="">همه روزها</option>
                        @for ($d = 1; $d <= 31; $d++)
                            <option value="{{ $d }}">{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}</option>
                        @endfor
                    </flux:select>
                </div>

                <div class="flex items-end">
                    @if (filled($filter_year) || filled($filter_month) || filled($filter_day))
                        <flux:button type="button" variant="ghost" color="red" wire:click="resetDateFilters" class="w-full text-xs">
                            حذف فیلتر تاریخ ✕
                        </flux:button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    {{-- جدول فاکتورها --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <flux:table :paginate="$this->invoices">
                <flux:table.columns>
                    <flux:table.column>
                        نام فروشگاه
                    </flux:table.column>

                    <flux:table.column>
                        فروشنده
                    </flux:table.column>

                    <flux:table.column
                        sortable
                        :sorted="$sortBy === 'total_price'"
                        :direction="$sortDirection"
                        wire:click="sort('total_price')"
                    >
                        مبلغ کل
                    </flux:table.column>

                    <flux:table.column
                        sortable
                        :sorted="$sortBy === 'invoice_date'"
                        :direction="$sortDirection"
                        wire:click="sort('invoice_date')"
                    >
                        تاریخ و ساعت فاکتور
                    </flux:table.column>

                    <flux:table.column>
                        جزئیات
                    </flux:table.column>

                    <flux:table.column>
                        عملیات
                    </flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->invoices as $invoice)
                        <flux:table.row wire:key="invoice-row-{{ $invoice->id }}">
                            <flux:table.cell class="whitespace-nowrap">
                                <span class="font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $invoice->customer->shop_name ?? '---' }}
                                </span>
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                {{ trim(($invoice->profile->first_name ?? '') . ' ' . ($invoice->profile->last_name ?? '')) ?: '---' }}
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap font-bold text-blue-600 dark:text-blue-400">
                                {{ number_format((float) $invoice->total_price) }}
                                <span class="text-xs font-normal">تومان</span>
                            </flux:table.cell>

                            {{-- تاریخ و زمان تقویم ایرانی --}}
                            <flux:table.cell class="whitespace-nowrap font-mono text-xs">
                                @php
                                    $timeSource = $invoice->created_at ? $invoice->created_at->format('H:i:s') : null;
                                    $dt = $formatJalaliDateTime($invoice->invoice_date, $timeSource);
                                @endphp
                                <div class="font-semibold text-zinc-800 dark:text-zinc-200">
                                    {{ $dt['date'] }}
                                </div>
                                @if (!empty($dt['time']))
                                    <div class="text-[11px] text-zinc-400">
                                        ساعت: {{ $dt['time'] }}
                                    </div>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                <a
                                    href="{{ \Illuminate\Support\Facades\URL::signedRoute('invoiceitems', ['invoice' => $invoice]) }}"
                                    class="inline-flex items-center justify-center rounded-lg !bg-blue-600 px-3 py-1.5 text-sm font-medium !text-white no-underline transition hover:!bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    style="background-color: #2563eb !important; color: #ffffff !important;"
                                >
                                    جزئیات
                                </a>
                            </flux:table.cell>

                            <flux:table.cell class="min-w-[170px]">
                                <div class="flex flex-wrap gap-2">
                                    <flux:button
                                        type="button"
                                        variant="primary"
                                        color="yellow"
                                        size="sm"
                                        wire:click="edit({{ $invoice->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="edit({{ $invoice->id }})"
                                    >
                                        ویرایش
                                    </flux:button>

                                    <flux:button
                                        type="button"
                                        variant="primary"
                                        color="red"
                                        size="sm"
                                        wire:click="del_form({{ $invoice->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="del_form({{ $invoice->id }})"
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
                                class="py-12 text-center text-sm text-zinc-500 dark:text-zinc-400"
                            >
                                @if (filled($search) || filled($filter_year) || filled($filter_month) || filled($filter_day))
                                    هیچ فاکتوری با فیلترهای انتخابی پیدا نشد.
                                @else
                                    هنوز فاکتوری ثبت نشده است.
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </div>

    {{-- مودال ثبت فاکتور --}}
    <flux:modal name="save" class="w-full max-w-7xl">
        <div class="max-h-[90vh] space-y-6 overflow-y-auto p-1">
            <div>
                <flux:heading size="lg">
                    افزودن فاکتور
                </flux:heading>

                <flux:text class="mt-2">
                    برای افزودن فاکتور جدید اطلاعات زیر را کامل کنید.
                </flux:text>
            </div>

            @error('general')
            <div class="rounded-lg border border-red-300 bg-red-50 p-3 text-sm text-red-700">
                {{ $message }}
            </div>
            @enderror

            <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
                <div class="md:col-span-4">
                    <flux:select
                        wire:model.live="customer_id"
                        label="مشتری"
                    >
                        <option value="">انتخاب مشتری...</option>

                        @foreach ($this->customers as $customer)
                            <option value="{{ $customer->id }}">
                                {{ $customer->shop_name }}
                            </option>
                        @endforeach
                    </flux:select>

                    @error('customer_id')
                    <div class="mt-1 text-sm text-red-500">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                {{-- انتخاب تاریخ شمسی در مودال ثبت --}}
                <div class="md:col-span-5">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        تاریخ فاکتور (شمسی)
                    </label>
                    <div class="grid grid-cols-3 gap-2">
                        <flux:select wire:model.live="invoice_day">
                            <option value="">روز</option>
                            @for ($d = 1; $d <= 31; $d++)
                                <option value="{{ $d }}">{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}</option>
                            @endfor
                        </flux:select>

                        <flux:select wire:model.live="invoice_month">
                            <option value="">ماه</option>
                            <option value="1">فروردین</option>
                            <option value="2">اردیبهشت</option>
                            <option value="3">خرداد</option>
                            <option value="4">تیر</option>
                            <option value="5">مرداد</option>
                            <option value="6">شهریور</option>
                            <option value="7">مهر</option>
                            <option value="8">آبان</option>
                            <option value="9">آذر</option>
                            <option value="10">دی</option>
                            <option value="11">بهمن</option>
                            <option value="12">اسفند</option>
                        </flux:select>

                        <flux:select wire:model.live="invoice_year">
                            <option value="">سال</option>
                            @for ($y = $startYear; $y >= $endYear; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </flux:select>
                    </div>

                    @error('invoice_date')
                    <div class="mt-1 text-sm text-red-500">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="md:col-span-3">
                    <flux:input
                        wire:model.live="total_p"
                        label="مبلغ کل"
                        readonly
                    />

                    <div class="mt-1 text-xs font-bold text-green-600">
                        جمع آنلاین:
                        {{ number_format((float) $total_p) }}
                        تومان
                    </div>
                </div>
            </div>

            <div class="space-y-3 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <flux:heading size="sm">
                        آیتم‌های فاکتور
                    </flux:heading>

                    <flux:button
                        type="button"
                        variant="primary"
                        color="green"
                        wire:click="addItem"
                        class="w-full sm:w-auto"
                    >
                        + افزودن ردیف
                    </flux:button>
                </div>

                @foreach ($items as $index => $item)
                    <div
                        class="grid grid-cols-1 gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700 md:grid-cols-12"
                        wire:key="save-item-{{ $index }}"
                    >
                        <div class="md:col-span-5">
                            <flux:select
                                wire:model.live="items.{{ $index }}.product_batch_id"
                                label="محصول"
                            >
                                <option value="">انتخاب محصول...</option>

                                @foreach ($this->productBatches as $batch)
                                    <option value="{{ $batch->id }}">
                                        {{ $batch->product->name ?? '---' }}
                                        | موجودی: {{ $batch->quantity ?? 0 }}
                                        | قیمت: {{ number_format((float) $batch->sale_price) }}
                                    </option>
                                @endforeach
                            </flux:select>

                            @error("items.$index.product_batch_id")
                            <div class="mt-1 text-sm text-red-500">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <flux:input
                                type="number"
                                min="1"
                                wire:model.live="items.{{ $index }}.quantity"
                                label="تعداد"
                            />

                            @error("items.$index.quantity")
                            <div class="mt-1 text-sm text-red-500">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <flux:input
                                wire:model.live="items.{{ $index }}.price"
                                label="قیمت واحد به تومان"
                                readonly
                            />
                        </div>

                        <div class="md:col-span-2">
                            <flux:input
                                wire:model.live="items.{{ $index }}.subtotal"
                                label="جمع ردیف"
                                readonly
                            />
                        </div>

                        <div class="flex items-end md:col-span-1">
                            <flux:button
                                type="button"
                                variant="primary"
                                color="red"
                                wire:click="removeItem({{ $index }})"
                                class="w-full md:w-auto"
                            >
                                حذف
                            </flux:button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex flex-col-reverse gap-2 pt-4 sm:flex-row sm:items-center sm:justify-end">
                <flux:modal.close>
                    <flux:button type="button" variant="ghost">
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
                        ثبت نهایی
                    </span>

                    <span wire:loading wire:target="save">
                        در حال ثبت...
                    </span>
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- مودال ویرایش فاکتور --}}
    <flux:modal name="edit" class="w-full max-w-7xl">
        <div class="max-h-[90vh] space-y-6 overflow-y-auto p-1">
            <div>
                <flux:heading size="lg">
                    ویرایش فاکتور
                </flux:heading>

                <flux:text class="mt-2">
                    برای ویرایش فاکتور قسمت‌های مورد نیاز را تغییر دهید.
                </flux:text>
            </div>

            @error('general')
            <div class="rounded-lg border border-red-300 bg-red-50 p-3 text-sm text-red-700">
                {{ $message }}
            </div>
            @enderror

            <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
                <div class="md:col-span-4">
                    <flux:select
                        wire:model.live="customer_id"
                        label="مشتری"
                    >
                        <option value="">انتخاب مشتری...</option>

                        @foreach ($this->customers as $customer)
                            <option value="{{ $customer->id }}">
                                {{ $customer->shop_name }}
                            </option>
                        @endforeach
                    </flux:select>

                    @error('customer_id')
                    <div class="mt-1 text-sm text-red-500">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                {{-- انتخاب تاریخ شمسی در مودال ویرایش --}}
                <div class="md:col-span-5">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        تاریخ فاکتور (شمسی)
                    </label>
                    <div class="grid grid-cols-3 gap-2">
                        <flux:select wire:model.live="invoice_day">
                            <option value="">روز</option>
                            @for ($d = 1; $d <= 31; $d++)
                                <option value="{{ $d }}">{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}</option>
                            @endfor
                        </flux:select>

                        <flux:select wire:model.live="invoice_month">
                            <option value="">ماه</option>
                            <option value="1">فروردین</option>
                            <option value="2">اردیبهشت</option>
                            <option value="3">خرداد</option>
                            <option value="4">تیر</option>
                            <option value="5">مرداد</option>
                            <option value="6">شهریور</option>
                            <option value="7">مهر</option>
                            <option value="8">آبان</option>
                            <option value="9">آذر</option>
                            <option value="10">دی</option>
                            <option value="11">بهمن</option>
                            <option value="12">اسفند</option>
                        </flux:select>

                        <flux:select wire:model.live="invoice_year">
                            <option value="">سال</option>
                            @for ($y = $startYear; $y >= $endYear; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </flux:select>
                    </div>

                    @error('invoice_date')
                    <div class="mt-1 text-sm text-red-500">
                        {{ $message }}
                    </div>
                    @enderror
                    @error('invoice_year')
                    <div class="mt-1 text-sm text-red-500">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="md:col-span-3">
                    <flux:input
                        wire:model.live="total_p"
                        label="مبلغ کل"
                        readonly
                    />

                    <div class="mt-1 text-xs font-bold text-green-600">
                        جمع آنلاین:
                        {{ number_format((float) $total_p) }}
                        تومان
                    </div>
                </div>
            </div>

            <div class="space-y-3 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <flux:heading size="sm">
                        آیتم‌های فاکتور
                    </flux:heading>

                    <flux:button
                        type="button"
                        variant="primary"
                        color="green"
                        wire:click="addItem"
                        class="w-full sm:w-auto"
                    >
                        + افزودن ردیف
                    </flux:button>
                </div>

                @foreach ($items as $index => $item)
                    <div
                        class="grid grid-cols-1 gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700 md:grid-cols-12"
                        wire:key="edit-item-{{ $index }}"
                    >
                        <div class="md:col-span-5">
                            <flux:select
                                wire:model.live="items.{{ $index }}.product_batch_id"
                                label="محصول"
                            >
                                <option value="">انتخاب محصول...</option>

                                @foreach ($this->productBatches as $batch)
                                    <option value="{{ $batch->id }}">
                                        {{ $batch->product->name ?? '---' }}
                                        | موجودی: {{ $batch->quantity ?? 0 }}
                                        | قیمت: {{ number_format((float) $batch->sale_price) }}
                                    </option>
                                @endforeach
                            </flux:select>

                            @error("items.$index.product_batch_id")
                            <div class="mt-1 text-sm text-red-500">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <flux:input
                                type="number"
                                min="1"
                                wire:model.live="items.{{ $index }}.quantity"
                                label="تعداد"
                            />

                            @error("items.$index.quantity")
                            <div class="mt-1 text-sm text-red-500">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <flux:input
                                wire:model.live="items.{{ $index }}.price"
                                label="قیمت واحد به تومان"
                                readonly
                            />
                        </div>

                        <div class="md:col-span-2">
                            <flux:input
                                wire:model.live="items.{{ $index }}.subtotal"
                                label="جمع ردیف"
                                readonly
                            />
                        </div>

                        <div class="flex items-end md:col-span-1">
                            <flux:button
                                type="button"
                                variant="primary"
                                color="red"
                                wire:click="removeItem({{ $index }})"
                                class="w-full md:w-auto"
                            >
                                حذف
                            </flux:button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex flex-col-reverse gap-2 pt-4 sm:flex-row sm:items-center sm:justify-end">
                <flux:modal.close>
                    <flux:button type="button" variant="ghost">
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
                        ثبت تغییرات
                    </span>

                    <span wire:loading wire:target="update">
                        در حال ذخیره...
                    </span>
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- مودال حذف فاکتور --}}
    <flux:modal name="delete" class="w-full max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    حذف فاکتور
                </flux:heading>

                <flux:text class="mt-2">
                    آیا از حذف فاکتور مطمئن هستید؟ این فاکتور قابل برگشت نخواهد بود.
                </flux:text>
            </div>

            @error('general')
            <div class="rounded-lg border border-red-300 bg-red-50 p-3 text-sm text-red-700">
                {{ $message }}
            </div>
            @enderror

            <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <flux:modal.close>
                    <flux:button type="button" variant="ghost">
                        انصراف
                    </flux:button>
                </flux:modal.close>

                <flux:button
                    type="button"
                    wire:click="delete"
                    wire:loading.attr="disabled"
                    wire:target="delete"
                    variant="primary"
                    color="red"
                >
                    <span wire:loading.remove wire:target="delete">
                        حذف فاکتور
                    </span>

                    <span wire:loading wire:target="delete">
                        در حال حذف...
                    </span>
                </flux:button>
            </div>
        </div>
    </flux:modal>

</div>

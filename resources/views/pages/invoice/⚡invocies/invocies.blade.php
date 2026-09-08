<div dir="rtl" class="space-y-4">

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

    {{-- نوار جست‌وجو --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
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
                        تاریخ فاکتور
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

                            <flux:table.cell class="whitespace-nowrap">
                                {{ $invoice->invoice_date }}
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
                                @if (filled($search))
                                    هیچ فاکتوری برای نام فروشگاه
                                    «{{ $search }}»
                                    پیدا نشد.
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

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
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

                <div>
                    <flux:input
                        type="date"
                        wire:model.live="invoice_date"
                        label="تاریخ فاکتور"
                    />

                    @error('invoice_date')
                    <div class="mt-1 text-sm text-red-500">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div>
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

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
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

                <div>
                    <flux:input
                        type="date"
                        wire:model.live="invoice_date"
                        label="تاریخ فاکتور"
                    />

                    @error('invoice_date')
                    <div class="mt-1 text-sm text-red-500">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div>
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

    {{-- مودال حذف --}}
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

<div dir="rtl" class="space-y-4">


    @error('general')
    <div class="rounded-lg border border-red-300 bg-red-50 p-3 text-sm text-red-700">
        {{ $message }}
    </div>
    @enderror

    <flux:table :paginate="$this->invoices">
        <flux:table.columns>
            <flux:table.column>نام فروشگاه</flux:table.column>

            <flux:table.column>فروشنده</flux:table.column>

            <flux:table.column>مبلغ کل</flux:table.column>

            <flux:table.column
                sortable
                :sorted="$sortBy === 'invoice_date'"
                :direction="$sortDirection"
                wire:click="sort('invoice_date')"
            >
                تاریخ فاکتور
            </flux:table.column>

            <flux:table.column>جزئیات</flux:table.column>

            <flux:table.column>عملیات</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->invoices as $invoice)
                <flux:table.row :key="$invoice->id">
                    <flux:table.cell class="whitespace-nowrap">
                        {{ $invoice->customer->shop_name ?? '---' }}
                    </flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap">
                        {{ ($invoice->profile->first_name ?? '') . ' ' . ($invoice->profile->last_name ?? '') }}
                    </flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap font-bold text-blue-600">
                        {{ number_format((float) $invoice->total_price) }}
                    </flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap">
                        {{ $invoice->invoice_date }}
                    </flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap">
                        <a
                            href="{{ \Illuminate\Support\Facades\URL::signedRoute('invoiceitems', ['invoice' => $invoice]) }}"
                            class="inline-block rounded-lg !bg-blue-600 px-3 py-1.5 !text-white no-underline transition hover:!bg-blue-700"
                            style="background-color: #2563eb !important; color: #ffffff !important;"
                        >
                            جزئیات
                        </a>
                    </flux:table.cell>


                    <flux:table.cell class="whitespace-nowrap">
                        <div class="flex gap-2">
                            <flux:button variant="primary" color="yellow" size="sm" wire:click="edit({{ $invoice->id }})">
                                ویرایش
                            </flux:button>

                            <flux:button variant="primary" color="red" size="sm" wire:click="del_form({{ $invoice->id }})">
                                حذف
                            </flux:button>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    {{-- Save Modal --}}
    <flux:modal name="save" class="md:w-7xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">افزودن فاکتور</flux:heading>
                <flux:text class="mt-2">برای افزودن فاکتور جدید اطلاعات زیر را کامل کنید</flux:text>
            </div>

            @error('general')
            <div class="rounded-lg border border-red-300 bg-red-50 p-3 text-sm text-red-700">
                {{ $message }}
            </div>
            @enderror

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <flux:select wire:model.live="customer_id" label="مشتری">
                        <option value="">انتخاب مشتری...</option>

                        @foreach($this->customers as $customer)
                            <option value="{{ $customer->id }}">
                                {{ $customer->shop_name }}
                            </option>
                        @endforeach
                    </flux:select>

                    @error('customer_id')
                    <div class="mt-1 text-sm text-red-500">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <flux:input type="date" wire:model.live="invoice_date" label="تاریخ فاکتور" />

                    @error('invoice_date')
                    <div class="mt-1 text-sm text-red-500">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <flux:input wire:model.live="total_p" label="مبلغ کل" readonly />

                    <div class="mt-1 text-xs font-bold text-green-600">
                        جمع آنلاین:
                        {{ number_format((float) $total_p) }}
                    </div>
                </div>
            </div>

            <div class="space-y-3 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <flux:heading size="sm">آیتم‌های فاکتور</flux:heading>

                    <flux:button type="button" variant="primary" color="green" wire:click="addItem">
                        + افزودن ردیف
                    </flux:button>
                </div>

                @foreach($items as $index => $item)
                    <div class="grid grid-cols-1 gap-3 rounded-lg border p-3 md:grid-cols-12" wire:key="save-item-{{ $index }}">
                        <div class="md:col-span-5">
                            <flux:select wire:model.live="items.{{ $index }}.product_batch_id" label="محصول">
                                <option value="">انتخاب محصول...</option>

                                @foreach($this->productBatches as $batch)
                                    <option value="{{ $batch->id }}">
                                        {{ $batch->product->name ?? '---' }}
                                        |
                                        موجودی:
                                        {{ $batch->quantity ?? 0 }}
                                        |
                                        قیمت:
                                        {{ number_format((float) $batch->sale_price) }}
                                    </option>
                                @endforeach
                            </flux:select>

                            @error("items.$index.product_batch_id")
                            <div class="mt-1 text-sm text-red-500">{{ $message }}</div>
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
                            <div class="mt-1 text-sm text-red-500">{{ $message }}</div>
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
                            >
                                x
                            </flux:button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex pt-4">
                <flux:spacer />

                <flux:button wire:click="save" type="button" variant="primary">
                   ثبت نهایی
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Edit Modal --}}
    <flux:modal name="edit" class="md:w-7xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">ویرایش فاکتور</flux:heading>
                <flux:text class="mt-2">برای ویرایش فاکتور قسمت های مورد نیاز را تغییر دهید</flux:text>
            </div>

            @error('general')
            <div class="rounded-lg border border-red-300 bg-red-50 p-3 text-sm text-red-700">
                {{ $message }}
            </div>
            @enderror

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <flux:select wire:model.live="customer_id" label="مشتری">
                        <option value="">انتخاب مشتری...</option>

                        @foreach($this->customers as $customer)
                            <option value="{{ $customer->id }}">
                                {{ $customer->shop_name }}
                            </option>
                        @endforeach
                    </flux:select>

                    @error('customer_id')
                    <div class="mt-1 text-sm text-red-500">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <flux:input type="date" wire:model.live="invoice_date" label="تاریخ فاکتور" />

                    @error('invoice_date')
                    <div class="mt-1 text-sm text-red-500">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <flux:input wire:model.live="total_p" label="مبلغ کل" readonly />

                    <div class="mt-1 text-xs font-bold text-green-600">
                        جمع آنلاین:
                        {{ number_format((float) $total_p) }}
                    </div>
                </div>
            </div>

            <div class="space-y-3 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <flux:heading size="sm">آیتم‌های فاکتور</flux:heading>

                    <flux:button type="button" variant="primary" color="green" wire:click="addItem">
                        + افزودن ردیف
                    </flux:button>
                </div>

                @foreach($items as $index => $item)
                    <div class="grid grid-cols-1 gap-3 rounded-lg border p-3 md:grid-cols-12" wire:key="edit-item-{{ $index }}">
                        <div class="md:col-span-5">
                            <flux:select wire:model.live="items.{{ $index }}.product_batch_id" label="محصول">
                                <option value="">انتخاب محصول...</option>

                                @foreach($this->productBatches as $batch)
                                    <option value="{{ $batch->id }}">
                                        {{ $batch->product->name ?? '---' }}
                                        |
                                        موجودی:
                                        {{ $batch->quantity ?? 0 }}
                                        |
                                        قیمت:
                                        {{ number_format((float) $batch->sale_price) }}
                                    </option>
                                @endforeach
                            </flux:select>

                            @error("items.$index.product_batch_id")
                            <div class="mt-1 text-sm text-red-500">{{ $message }}</div>
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
                            <div class="mt-1 text-sm text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <flux:input
                                wire:model.live="items.{{ $index }}.price"
                                label="قیمت واحد"
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
                            >
                                x
                            </flux:button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex pt-4">
                <flux:spacer />

                <flux:button wire:click="update" type="button" variant="primary">
                    ثبت تغییرات
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Delete Modal --}}
    <flux:modal name="delete" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">حذف فاکتور</flux:heading>
                <flux:text class="mt-2">آیا از حذف فاکتور مطمئن هستید؟!(فاکتور غیر قابل برگشت خواهد بود)</flux:text>
            </div>

            @error('general')
            <div class="rounded-lg border border-red-300 bg-red-50 p-3 text-sm text-red-700">
                {{ $message }}
            </div>
            @enderror

            <div class="flex">
                <flux:spacer />

                <flux:button wire:click="delete" type="button" variant="primary" color="red">
                    حذف
                </flux:button>
            </div>
        </div>
    </flux:modal>
    <div class="flex justify-end">
        <flux:modal.trigger name="save">
            <flux:button wire:click="openSaveModal" variant="primary" color="green">
                افزودن فاکتور جدید
            </flux:button>
        </flux:modal.trigger>
        <a
            href="{{ URL::signedRoute('invoice_s_a') }}"
            class="inline-block rounded-lg !bg-blue-600 px-4 py-2 !text-white no-underline transition hover:!bg-blue-700"
            style="background-color: #2563eb !important; color: #ffffff !important;"
        >
            برگشت
        </a>
    </div>

</div>

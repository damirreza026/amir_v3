<div dir="rtl" class="mx-auto max-w-5xl space-y-6 p-4 sm:p-6">
    <script>
        function printInvoice() {
            const oldTitle = document.title;

            document.title = 'نام مغازه شما';

            window.print();

            document.title = oldTitle;
        }
    </script>

    <style>
        .print-only,
        .print-signatures {
            display: none;
        }

        @media print {
            @page {
                size: A4;
                margin: 15mm;
            }

            body * {
                visibility: hidden !important;
            }

            #invoice-print-area,
            #invoice-print-area * {
                visibility: visible !important;
            }

            #invoice-print-area {
                position: absolute !important;
                top: 0 !important;
                right: 0 !important;
                width: 100% !important;
                padding: 0 !important;
                color: #000 !important;
                background: #fff !important;
            }

            .no-print {
                display: none !important;
            }

            .print-only {
                display: table !important;
            }

            .invoice-header {
                border: 1px solid #000 !important;
                padding: 16px !important;
                margin-bottom: 20px !important;
                border-radius: 0 !important;
                background: #fff !important;
                color: #000 !important;
                box-shadow: none !important;
            }

            .invoice-header p,
            .invoice-header div,
            .invoice-header h2,
            .invoice-header span {
                color: #000 !important;
            }

            .print-table {
                width: 100% !important;
                border-collapse: collapse !important;
                font-size: 12px !important;
            }

            .print-table th,
            .print-table td {
                border: 1px solid #000 !important;
                padding: 9px !important;
                text-align: right !important;
                color: #000 !important;
            }

            .print-table th {
                background: #e5e7eb !important;
                font-weight: bold !important;
            }

            .print-table tfoot td {
                font-weight: bold !important;
                background: #f3f4f6 !important;
            }

            .print-signatures {
                display: flex !important;
                justify-content: space-between !important;
                gap: 80px !important;
                margin-top: 55px !important;
                padding: 0 35px !important;
                direction: rtl !important;
            }

            .signature-box {
                width: 220px !important;
                text-align: center !important;
                color: #000 !important;
                font-size: 13px !important;
                font-weight: bold !important;
            }

            .signature-line {
                height: 55px !important;
                margin-top: 12px !important;
                border-bottom: 1px solid #000 !important;
            }
        }
    </style>

    {{-- نوار بالای صفحه (شامل عنوان و دکمه‌ها) --}}
    <div class="no-print flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-zinc-200 pb-4 dark:border-zinc-800">
        <div>
            <flux:heading size="xl" class="font-black text-zinc-900 dark:text-white">
                جزئیات فاکتور شماره {{ $invoice->id }}
            </flux:heading>
            <flux:subheading class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                مشاهده ریز اقلام و چاپ رسمی فاکتور
            </flux:subheading>
        </div>

        <div class="flex items-center gap-3">
            <a
                href="{{ URL::signedRoute('invoice') }}"
                class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-700 shadow-sm transition-all hover:bg-zinc-50 active:scale-95 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700"
            >
                بازگشت
            </a>

            <button
                type="button"
                onclick="printInvoice()"
                class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-emerald-600/20 transition-all hover:bg-emerald-700 hover:shadow-lg active:scale-95"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                </svg>
                چاپ فاکتور
            </button>
        </div>
    </div>

    {{-- محدوده اصلی فاکتور --}}
    <div id="invoice-print-area">
        {{-- باکس اطلاعات سربرگ --}}
        <div class="invoice-header rounded-2xl border border-zinc-200/80 bg-gradient-to-br from-white to-zinc-50 p-6 shadow-sm dark:border-zinc-800 dark:from-zinc-900 dark:to-zinc-900/60">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 lg:items-center">
                <div class="space-y-1">
                    <span class="text-xs font-semibold text-zinc-600 dark:text-zinc-300">مشتری</span>
                    <div class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                        {{ $invoice->customer?->shop_name ?? '---' }}
                    </div>
                </div>

                <div class="space-y-1">
                    <span class="text-xs font-semibold text-zinc-600 dark:text-zinc-300">فروشنده</span>
                    <div class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                        {{
                            trim(
                                ($invoice->profile?->first_name ?? '') . ' ' .
                                ($invoice->profile?->last_name ?? '')
                            ) ?: '---'
                        }}
                    </div>
                </div>

                <div class="space-y-1">
                    <span class="text-xs font-semibold text-zinc-600 dark:text-zinc-300">تاریخ صدور</span>
                    <div class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                        {{ $invoice->invoice_date }}
                    </div>
                </div>

                <div class="rounded-xl bg-blue-50/70 p-3 text-right dark:bg-blue-950/40 sm:text-left">
                    <span class="text-xs font-semibold text-blue-800 dark:text-blue-200">جمع کل فاکتور</span>
                    <div class="text-xl font-black text-blue-600 dark:text-blue-400">
                        {{ number_format((float) $invoice->total_price) }}
                        <span class="text-xs font-bold">تومان</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- جدول عادی صفحه؛ فقط در حالت عادی دیده می‌شود --}}
        <div class="no-print mt-6 overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <flux:table :paginate="$this->invoiceItems">
                <flux:table.columns>
                    <flux:table.column>
                        نام محصول
                    </flux:table.column>

                    <flux:table.column
                        sortable
                        :sorted="$sortBy === 'quantity'"
                        :direction="$sortDirection"
                        wire:click="sort('quantity')"
                    >
                        تعداد
                    </flux:table.column>

                    <flux:table.column>
                        قیمت واحد (تومان)
                    </flux:table.column>

                    <flux:table.column>
                        جمع ردیف (تومان)
                    </flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->invoiceItems as $invoiceItem)
                        <flux:table.row :key="$invoiceItem->id" class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/50 transition">
                            <flux:table.cell class="whitespace-nowrap font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $invoiceItem->productBatch?->product?->name ?? '---' }}
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap font-bold text-zinc-800 dark:text-zinc-200">
                                {{ $invoiceItem->quantity }}
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap text-zinc-600 dark:text-zinc-300">
                                {{ number_format((float) $invoiceItem->price) }}
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap font-bold text-blue-600 dark:text-blue-400">
                                {{ number_format((float) $invoiceItem->subtotal) }}
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="4" class="text-center py-8 text-zinc-400">
                                آیتمی برای این فاکتور ثبت نشده است.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>

        {{-- جدول مخصوص چاپ؛ تمام آیتم‌ها را نمایش می‌دهد --}}
        <table class="print-only print-table">
            <thead>
            <tr>
                <th>ردیف</th>
                <th>نام محصول</th>
                <th>تعداد</th>
                <th>قیمت واحد (تومان)</th>
                <th>جمع ردیف (تومان)</th>
            </tr>
            </thead>

            <tbody>
            @forelse ($this->allInvoiceItems as $index => $invoiceItem)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $invoiceItem->productBatch?->product?->name ?? '---' }}</td>
                    <td>{{ $invoiceItem->quantity }}</td>
                    <td>{{ number_format((float) $invoiceItem->price) }}</td>
                    <td>{{ number_format((float) $invoiceItem->subtotal) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">آیتمی برای این فاکتور ثبت نشده است.</td>
                </tr>
            @endforelse
            </tbody>

            <tfoot>
            <tr>
                <td colspan="4">جمع کل فاکتور</td>
                <td>{{ number_format((float) $invoice->total_price) }} تومان</td>
            </tr>
            </tfoot>
        </table>

        {{-- این بخش فقط هنگام چاپ نمایش داده می‌شود --}}
        <div class="print-signatures">
            <div class="signature-box">
                <p>امضا خریدار</p>
                <div class="signature-line"></div>
            </div>

            <div class="signature-box">
                <p>امضا فروشنده</p>
                <div class="signature-line"></div>
            </div>
        </div>
    </div>
</div>

<div dir="rtl" class="space-y-4 p-4">
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

    <div class="no-print flex justify-end">
        <button
            type="button"
            onclick="printInvoice()"
            class="inline-block rounded-lg !bg-green-600 px-4 py-2 !text-white no-underline transition hover:!bg-green-700"
            style="background-color: #16a34a !important; color: #ffffff !important;"
        >
            چاپ فاکتور
        </button>
    </div>

    <div id="invoice-print-area">
        <div class="invoice-header rounded-xl border bg-zinc-50 p-4 dark:bg-zinc-800">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-lg font-bold">
                        جزئیات فاکتور
                    </h2>

                    <p class="mt-1 text-sm text-zinc-500">
                        مشتری:
                        <span class="font-bold">
                            {{ $invoice->customer?->shop_name ?? '---' }}
                        </span>
                    </p>

                    <p class="mt-1 text-sm text-zinc-500">
                        فروشنده:
                        <span class="font-bold">
                            {{
                                trim(
                                    ($invoice->profile?->first_name ?? '') . ' ' .
                                    ($invoice->profile?->last_name ?? '')
                                ) ?: '---'
                            }}
                        </span>
                    </p>

                    <p class="mt-1 text-sm text-zinc-500">
                        تاریخ:
                        <span class="font-bold">
                            {{ $invoice->invoice_date }}
                        </span>
                    </p>
                </div>

                <div class="text-lg font-black text-blue-600">
                    جمع کل:
                    {{ number_format((float) $invoice->total_price) }}
                    تومان
                </div>
            </div>
        </div>

        {{-- جدول عادی صفحه؛ فقط در حالت عادی دیده می‌شود --}}
        <div class="no-print">
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
                        قیمت واحد
                    </flux:table.column>

                    <flux:table.column>
                        جمع ردیف
                    </flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->invoiceItems as $invoiceItem)
                        <flux:table.row :key="$invoiceItem->id">
                            <flux:table.cell class="whitespace-nowrap">
                                {{ $invoiceItem->productBatch?->product?->name ?? '---' }}
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap font-bold">
                                {{ $invoiceItem->quantity }}
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                {{ number_format((float) $invoiceItem->price) }}
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap font-bold">
                                {{ number_format((float) $invoiceItem->subtotal) }}
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="4">
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
    <a
        href="{{ URL::signedRoute('invoice') }}"
        class="inline-block rounded-lg !bg-blue-600 px-4 py-2 !text-white no-underline transition hover:!bg-blue-700"
        style="background-color: #2563eb !important; color: #ffffff !important;"
    >
        برگشت
    </a>
</div>

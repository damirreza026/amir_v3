<div dir="rtl" class="space-y-4 p-4">
    <div class="rounded-xl border bg-zinc-50 p-4 dark:bg-zinc-800">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-bold">
                    جزئیات فاکتور
                </h2>

                <p class="mt-1 text-sm text-zinc-500">
                    مشتری:
                    <span class="font-bold">
                        {{ $invoice->customer->shop_name ?? '---' }}
                    </span>
                </p>

                <p class="mt-1 text-sm text-zinc-500">
                    فروشنده:
                    <span class="font-bold">
                        {{ ($invoice->profile->first_name ?? '') . ' ' . ($invoice->profile->last_name ?? '') }}
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
            </div>
        </div>
    </div>

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

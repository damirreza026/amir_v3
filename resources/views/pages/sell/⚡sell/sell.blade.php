<div class="space-y-6">
    @if (session()->has('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @error('checkout_error')
    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ $message }}
    </div>
    @enderror

    <div class="grid gap-6 lg:grid-cols-3">

        {{-- جدول کالاها و بچ‌ها --}}
        <div class="space-y-4 lg:col-span-2">
            <h2 class="text-lg font-bold">مدیریت فروش انبار</h2>

            <flux:table :paginate="$this->productbatches">
                <flux:table.columns>
                    <flux:table.column
                        sortable
                        :sorted="$sortBy === 'expiry_date'"
                        :direction="$sortDirection"
                        wire:click="sort('expiry_date')"
                    >
                        Expiry date
                    </flux:table.column>

                    <flux:table.column>Product name</flux:table.column>
                    <flux:table.column>Registrar</flux:table.column>
                    <flux:table.column>Sale price</flux:table.column>
                    <flux:table.column>Production date</flux:table.column>
                    <flux:table.column>Quantity</flux:table.column>
                    <flux:table.column>Action</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->productbatches as $productbatche)
                        <flux:table.row :key="$productbatche->id">
                            <flux:table.cell class="whitespace-nowrap">
                                {{ $productbatche->expiry_date }}
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                {{ $productbatche->product->name }}
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                {{ ($productbatche->profile->first_name ?? '') . ' ' . ($productbatche->profile->last_name ?? '') }}
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                {{ number_format((float) $productbatche->sale_price) }}
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                {{ $productbatche->production_date }}
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap font-bold text-blue-600">
                                {{ $productbatche->quantity }}
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                <flux:button
                                    variant="primary"
                                    color="yellow"
                                    wire:click="edit({{ $productbatche }})"
                                >
                                    Sell
                                </flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>

        {{-- سبد خرید موقت --}}
        <div>
            <div class="space-y-4 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold">سبد خرید</h3>
                        <p class="text-xs text-zinc-500">
                            {{ count($cart) }} آیتم آماده فروش
                        </p>
                    </div>

                    @if (count($cart) > 0)
                        <flux:button
                            size="sm"
                            variant="ghost"
                            wire:click="clearCart"
                        >
                            پاک کردن
                        </flux:button>
                    @endif
                </div>

                {{-- انتخاب مغازه / مشتری --}}
                <div class="space-y-2">
                    <label for="customer_id" class="text-sm font-medium">
                        فروش به مغازه / مشتری
                    </label>

                    <select
                        id="customer_id"
                        wire:model="customer_id"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500"
                    >
                        <option value="">-- انتخاب مغازه --</option>

                        @foreach ($this->customers as $customer)
                            <option value="{{ $customer->id }}">
                                {{ $customer->shop_name }}
                            </option>
                        @endforeach
                    </select>

                    @error('customer_id')
                    <div class="text-xs text-red-600">
                        {{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="max-h-96 space-y-3 overflow-y-auto">
                    @forelse ($cart as $index => $item)
                        <div
                            class="rounded-lg border border-zinc-200 p-3"
                            wire:key="cart-item-{{ $item['batch_id'] }}"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <div class="truncate font-medium">
                                        {{ $item['product_name'] }}
                                    </div>

                                    <div class="mt-1 text-xs text-zinc-500">
                                        تعداد: {{ $item['qty'] }}
                                    </div>

                                    <div class="text-xs text-zinc-500">
                                        قیمت واحد: {{ number_format((float) $item['price']) }}
                                    </div>

                                    <div class="mt-1 text-sm font-bold text-green-600">
                                        {{ number_format((float) $item['total']) }} تومان
                                    </div>
                                </div>

                                <flux:button
                                    size="sm"
                                    variant="danger"
                                    wire:click="removeFromCart({{ $index }})"
                                >
                                    حذف
                                </flux:button>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-lg border border-dashed border-zinc-200 p-6 text-center text-sm text-zinc-400">
                            سبد خرید خالی است.
                        </div>
                    @endforelse
                </div>

                @if (count($cart) > 0)
                    <div class="space-y-3 border-t border-zinc-100 pt-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium">جمع کل:</span>

                            <span class="text-lg font-bold text-green-600">
                                {{ number_format((float) $this->cartTotal) }} تومان
                            </span>
                        </div>

                        <flux:button
                            class="mt-2 w-full"
                            variant="primary"
                            color="green"
                            wire:click="checkout"
                        >
                            ثبت نهایی فروش
                        </flux:button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- مودال انتخاب تعداد و افزودن به سبد --}}
    <flux:modal name="edit-user" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">ثبت در سبد فروش</flux:heading>
                <flux:text class="mt-2">
                    مشخصات محصول انتخابی برای افزودن به سبد خرید
                </flux:text>
            </div>

            <flux:input wire:model="prod_name" label="نام محصول" readonly />
            <flux:input wire:model="sale_p" label="قیمت فروش" readonly />
            <flux:input wire:model="pro_date" label="تاریخ تولید" readonly />
            <flux:input wire:model="ex_date" label="تاریخ انقضا" readonly />

            <flux:input
                type="number"
                min="1"
                wire:model="quan"
                label="تعداد برای فروش"
            />

            @error('quan')
            <div class="mt-1 text-xs text-red-600">
                {{ $message }}
            </div>
            @enderror

            <div class="flex">
                <flux:spacer />

                <flux:button
                    wire:click="update()"
                    type="submit"
                    variant="primary"
                    color="green"
                >
                    افزودن به سبد
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>

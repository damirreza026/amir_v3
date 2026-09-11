<div
    class="space-y-6"
    x-data="{ showFloatingCart: true }"
>
    {{--
        تبدیل ایمن تاریخ میلادی به شمسی.

        اگر مقدار تاریخ خراب، ناقص یا خارج از محدوده Morilog/Jalali باشد،
        همان مقدار خام دیتابیس نمایش داده می‌شود تا کل صفحه متوقف نشود.
    --}}
    @php
        $formatJalaliDate = static function ($date): string {
            if (blank($date)) {
                return '-';
            }

            try {
                $carbonDate = $date instanceof \Carbon\CarbonInterface
                    ? $date
                    : \Carbon\Carbon::parse((string) $date);

                return \Morilog\Jalali\Jalalian::fromCarbon($carbonDate)
                    ->format('Y/m/d');
            } catch (\Throwable $e) {
                return (string) $date;
            }
        };
    @endphp

    {{-- هدر صفحه --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" level="1">
                مدیریت و ثبت فروش
            </flux:heading>

            <flux:subheading class="mt-1">
                انتخاب کالاها از موجودی انبار، افزودن به سبد و صدور فاکتور مشتری
            </flux:subheading>
        </div>

        <div>
            <a
                href="{{ URL::signedRoute('sale_s_a') }}"
                class="inline-flex items-center gap-2 rounded-lg !bg-blue-600 px-4 py-2 text-sm font-medium !text-white no-underline transition hover:!bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                style="background-color: #2563eb !important; color: #ffffff !important;"
            >
                <span>بازگشت به منو</span>
            </a>
        </div>
    </div>

    <hr class="border-zinc-200 dark:border-zinc-700">

    {{-- اعلان‌های موفقیت و خطا --}}
    @if (session()->has('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-950/40 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif

    @error('checkout_error')
    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-950/40 dark:text-red-300">
        {{ $message }}
    </div>
    @enderror

    {{-- چیدمان ریسپانسیو اصلی: دو ستونه در دسکتاپ و تک‌ستونه در موبایل --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">

        {{-- ستون اصلی: جست‌وجو و جدول کالاها --}}
        <div class="space-y-4 lg:col-span-8">

            {{-- فیلد جست‌وجوی زنده بر اساس نام محصول --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="w-full sm:w-80">
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        placeholder="جست‌وجوی نام محصول..."
                        autocomplete="off"
                        clearable
                    />
                </div>

                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                    تعداد ردیف‌های موجود: {{ $this->productbatches->total() }}
                </div>
            </div>

            {{-- جدول کالاهای موجود --}}
            <flux:table :paginate="$this->productbatches">
                <flux:table.columns>
                    <flux:table.column>
                        نام محصول
                    </flux:table.column>

                    <flux:table.column
                        sortable
                        :sorted="$sortBy === 'expiry_date'"
                        :direction="$sortDirection"
                        wire:click="sort('expiry_date')"
                    >
                        تاریخ انقضاء
                    </flux:table.column>

                    <flux:table.column
                        sortable
                        :sorted="$sortBy === 'sale_price'"
                        :direction="$sortDirection"
                        wire:click="sort('sale_price')"
                    >
                        قیمت فروش (تومان)
                    </flux:table.column>

                    <flux:table.column
                        sortable
                        :sorted="$sortBy === 'quantity'"
                        :direction="$sortDirection"
                        wire:click="sort('quantity')"
                    >
                        موجودی
                    </flux:table.column>

                    <flux:table.column class="text-center">
                        عملیات
                    </flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->productbatches as $batch)
                        <flux:table.row wire:key="batch-row-{{ $batch->id }}">

                            {{-- نام محصول و ثبت‌کننده --}}
                            <flux:table.cell>
                                <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                    {{ $batch->product->name ?? '-' }}
                                </div>

                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                    ثبت:
                                    {{ trim(($batch->profile->first_name ?? '') . ' ' . ($batch->profile->last_name ?? '')) ?: '-' }}
                                </div>
                            </flux:table.cell>

                            {{-- تاریخ انقضاء و تولید؛ تبدیل ایمن به شمسی --}}
                            <flux:table.cell class="whitespace-nowrap font-mono text-xs">
                                <div class="text-zinc-800 dark:text-zinc-200">
                                    {{ $formatJalaliDate($batch->expiry_date) }}
                                </div>

                                <div class="text-[11px] text-zinc-400">
                                    تولید: {{ $formatJalaliDate($batch->production_date) }}
                                </div>
                            </flux:table.cell>

                            {{-- قیمت فروش --}}
                            <flux:table.cell class="whitespace-nowrap font-mono font-semibold text-zinc-700 dark:text-zinc-300">
                                {{ number_format((float) $batch->sale_price) }}
                            </flux:table.cell>

                            {{-- موجودی --}}
                            <flux:table.cell class="whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-bold text-blue-700 dark:bg-blue-950/50 dark:text-blue-300">
                                    {{ $batch->quantity }} عدد
                                </span>
                            </flux:table.cell>

                            {{-- دکمه افزودن/فروش --}}
                            <flux:table.cell class="whitespace-nowrap text-center">
                                <flux:button
                                    type="button"
                                    wire:click="edit({{ $batch->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="edit({{ $batch->id }})"
                                    variant="primary"
                                    color="yellow"
                                    size="sm"
                                >
                                    + فروش
                                </flux:button>
                            </flux:table.cell>

                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell
                                colspan="5"
                                class="py-10 text-center text-zinc-500 dark:text-zinc-400"
                            >
                                کالایی با موجودی فعال یافت نشد.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

        </div>

        {{-- ستون کناری: سبد خرید هوشمند و فاکتور --}}
        <div id="cart-section" class="lg:col-span-4">
            <div class="sticky top-6 rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-5">

                {{-- هدر سبد خرید --}}
                <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-green-50 text-green-600 dark:bg-green-950/60 dark:text-green-400 font-bold text-sm">
                            🛒
                        </div>

                        <div>
                            <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                                سبد فروش کالا
                            </h3>

                            <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ count($cart) }} ردیف ({{ $this->cartCount }} عدد)
                            </span>
                        </div>
                    </div>

                    @if (count($cart) > 0)
                        <flux:button
                            type="button"
                            size="sm"
                            variant="ghost"
                            wire:click="clearCart"
                            class="text-xs text-red-600 hover:text-red-700"
                        >
                            تخلیه سبد
                        </flux:button>
                    @endif
                </div>

                {{-- فرم انتخاب مغازه / مشتری --}}
                <div class="mt-4 space-y-1.5">
                    <label
                        for="customer_id"
                        class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300"
                    >
                        مشتری / مغازه طرف حساب
                        <span class="text-red-500">*</span>
                    </label>

                    <select
                        id="customer_id"
                        wire:model="customer_id"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-800 outline-none transition focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                    >
                        <option value="">-- انتخاب مشتری --</option>

                        @foreach ($this->customers as $customer)
                            <option value="{{ $customer->id }}">
                                {{ $customer->shop_name }}
                                {{ $customer->phone ? '(' . $customer->phone . ')' : '' }}
                            </option>
                        @endforeach
                    </select>

                    @error('customer_id')
                    <p class="text-xs font-medium text-red-600">
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                {{-- لیست آیتم‌های سبد خرید --}}
                <div class="mt-4 max-h-80 space-y-2.5 overflow-y-auto pr-1">
                    @forelse ($cart as $index => $item)
                        <div
                            class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-3 transition dark:border-zinc-800 dark:bg-zinc-800/40"
                            wire:key="cart-item-{{ $item['batch_id'] }}"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <h4 class="truncate text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                        {{ $item['product_name'] }}
                                    </h4>

                                    <div class="mt-1 flex flex-wrap items-center gap-x-3 text-xs text-zinc-500 dark:text-zinc-400">
                                        <span>
                                            تعداد:
                                            <strong class="text-zinc-800 dark:text-zinc-200">
                                                {{ $item['qty'] }}
                                            </strong>
                                        </span>

                                        <span>
                                            فی: {{ number_format((float) $item['price']) }}
                                        </span>
                                    </div>

                                    <div class="mt-1.5 text-xs font-bold text-green-600 dark:text-green-400 font-mono">
                                        {{ number_format((float) $item['total']) }} تومان
                                    </div>
                                </div>

                                <flux:button
                                    type="button"
                                    size="sm"
                                    variant="ghost"
                                    color="red"
                                    wire:click="removeFromCart({{ $index }})"
                                    class="!px-2 !py-1 text-xs"
                                >
                                    ✕
                                </flux:button>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-zinc-200 p-6 text-center text-xs text-zinc-400 dark:border-zinc-800 dark:text-zinc-500">
                            سبد فروش در حال حاضر خالی است. برای افزودن، دکمه «+ فروش» در جدول را بزنید.
                        </div>
                    @endforelse
                </div>

                {{-- جمع کل و دکمه ثبت نهایی فاکتور --}}
                @if (count($cart) > 0)
                    <div
                        id="checkout-action-area"
                        x-init="
                            const observer = new IntersectionObserver((entries) => {
                                entries.forEach(entry => {
                                    showFloatingCart = !entry.isIntersecting;
                                });
                            }, { threshold: 0.15 });

                            observer.observe($el);

                            $cleanup(() => {
                                observer.disconnect();
                            });
                        "
                        class="mt-5 space-y-3 border-t border-zinc-100 pt-4 dark:border-zinc-800"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                                مبلغ قابل پرداخت:
                            </span>

                            <span class="font-mono text-lg font-bold text-green-600 dark:text-green-400">
                                {{ number_format((float) $this->cartTotal) }} تومان
                            </span>
                        </div>

                        <flux:button
                            type="button"
                            class="w-full"
                            variant="primary"
                            color="green"
                            wire:click="checkout"
                            wire:loading.attr="disabled"
                            wire:target="checkout"
                        >
                            <span wire:loading.remove wire:target="checkout">
                                ثبت نهایی فاکتور فروش
                            </span>

                            <span wire:loading wire:target="checkout">
                                در حال ثبت فاکتور...
                            </span>
                        </flux:button>
                    </div>
                @endif

            </div>
        </div>

    </div>

    {{-- نوار شناور سبد برای موبایل --}}
    @if (count($cart) > 0)
        <div
            x-show="showFloatingCart"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-6"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-6"
            class="fixed bottom-3 inset-x-3 z-40 rounded-xl border border-zinc-200 bg-white/95 p-3 shadow-xl backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/95 lg:hidden"
        >
            <div class="flex items-center justify-between gap-3">
                <div>
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                        سبد: {{ $this->cartCount }} عدد
                    </div>

                    <div class="font-mono text-sm font-bold text-green-600 dark:text-green-400">
                        {{ number_format((float) $this->cartTotal) }} تومان
                    </div>
                </div>

                <button
                    type="button"
                    @click="
                        const el = document.getElementById('cart-section');

                        if (el) {
                            el.scrollIntoView({
                                behavior: 'smooth',
                                block: 'start'
                            });
                        }
                    "
                    class="cursor-pointer rounded-lg bg-green-600 px-4 py-2 text-xs font-semibold text-white shadow transition hover:bg-green-700 active:scale-95"
                >
                    مشاهده و تسویه
                </button>
            </div>
        </div>
    @endif

    {{-- ====================================================== --}}
    {{-- مودال انتخاب تعداد و افزودن به سبد خرید --}}
    {{-- ====================================================== --}}
    <flux:modal name="edit-user" class="md:w-[28rem]">
        <div class="space-y-5">

            <div>
                <flux:heading size="lg">
                    افزودن به سبد فروش
                </flux:heading>

                <flux:text class="mt-1 text-xs">
                    تعداد مورد نظر از این بچ محصول را برای افزودن به سبد مشخص کنید.
                </flux:text>
            </div>

            <div class="space-y-3">
                <flux:input
                    wire:model="prod_name"
                    label="نام کالا"
                    readonly
                />

                <div class="grid grid-cols-2 gap-3">
                    <flux:input
                        wire:model="sale_p"
                        label="قیمت واحد (تومان)"
                        readonly
                    />

                    <flux:input
                        value="{{ $available_qty }} عدد"
                        label="موجودی کل بچ"
                        readonly
                    />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <flux:input
                        wire:model="pro_date"
                        label="تاریخ تولید"
                        readonly
                    />

                    <flux:input
                        wire:model="ex_date"
                        label="تاریخ انقضا"
                        readonly
                    />
                </div>

                <div>
                    <flux:input
                        type="number"
                        min="1"
                        max="{{ $available_qty }}"
                        wire:model="quan"
                        label="تعداد درخواستی برای فروش"
                        autocomplete="off"
                    />

                    @error('quan')
                    <p class="mt-1 text-xs font-medium text-red-600">
                        {{ $message }}
                    </p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button
                        type="button"
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
                    color="green"
                >
                    <span wire:loading.remove wire:target="update">
                        افزودن به سبد
                    </span>

                    <span wire:loading wire:target="update">
                        در حال افزودن...
                    </span>
                </flux:button>
            </div>

        </div>
    </flux:modal>

</div>

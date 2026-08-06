<div class="space-y-6">

    {{-- هدر صفحه --}}
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">
                {{ $category->name }}
            </h1>

            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                مدیریت موجودی و بچ‌های محصولات
            </p>
        </div>

        <flux:button
            type="button"
            wire:click="openSaveModal"
            variant="primary"
            color="green"
        >
            افزودن موجودی جدید
        </flux:button>
    </div>

    <hr class="border-zinc-200 dark:border-zinc-700">

    {{-- پیام موفقیت --}}
    @if (session()->has('success'))
        <div
            class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700
                   dark:border-green-800 dark:bg-green-950/40 dark:text-green-300"
        >
            {{ session('success') }}
        </div>
    @endif

    {{-- پیام خطا --}}
    @if (session()->has('error'))
        <div
            class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700
                   dark:border-red-800 dark:bg-red-950/40 dark:text-red-300"
        >
            {{ session('error') }}
        </div>
    @endif

    {{-- جدول موجودی‌ها --}}
    <flux:table :paginate="$this->productbatchs">

        <flux:table.columns>
            <flux:table.column>
                دسته‌بندی
            </flux:table.column>

            <flux:table.column>
                محصول
            </flux:table.column>

            <flux:table.column>
                کارمند
            </flux:table.column>

            <flux:table.column>
                قیمت فروش
            </flux:table.column>

            <flux:table.column>
                تاریخ تولید
            </flux:table.column>

            <flux:table.column
                sortable
                :sorted="$sortBy === 'expiry_date'"
                :direction="$sortDirection"
                wire:click="sort('expiry_date')"
            >
                تاریخ انقضا
            </flux:table.column>

            <flux:table.column>
                تعداد
            </flux:table.column>

            <flux:table.column>
                عملیات
            </flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->productbatchs as $productbatch)
                <flux:table.row wire:key="product-batch-{{ $productbatch->id }}">

                    {{-- دسته‌بندی --}}
                    <flux:table.cell class="whitespace-nowrap">
                        {{ $productbatch->product?->category?->name ?? '-' }}
                    </flux:table.cell>

                    {{-- محصول --}}
                    <flux:table.cell class="whitespace-nowrap font-medium">
                        {{ $productbatch->product?->name ?? '-' }}
                    </flux:table.cell>

                    {{-- کارمند --}}
                    <flux:table.cell class="whitespace-nowrap">
                        @php
                            $employeeName = trim(
                                ($productbatch->profile?->first_name ?? '') . ' ' .
                                ($productbatch->profile?->last_name ?? '')
                            );
                        @endphp

                        {{ $employeeName !== '' ? $employeeName : '-' }}
                    </flux:table.cell>

                    {{-- قیمت فروش --}}
                    <flux:table.cell class="whitespace-nowrap font-mono text-green-600">
                        {{ number_format((float) $productbatch->sale_price) }}
                    </flux:table.cell>

                    {{-- تاریخ تولید --}}
                    <flux:table.cell class="whitespace-nowrap font-mono">
                        {{ $productbatch->production_date ?? '-' }}
                    </flux:table.cell>

                    {{-- تاریخ انقضا --}}
                    <flux:table.cell class="whitespace-nowrap font-mono">
                        {{ $productbatch->expiry_date ?? '-' }}
                    </flux:table.cell>

                    {{-- تعداد --}}
                    <flux:table.cell class="whitespace-nowrap font-mono">
                        {{ number_format((int) $productbatch->quantity) }}
                    </flux:table.cell>

                    {{-- عملیات --}}
                    <flux:table.cell class="whitespace-nowrap">
                        <div class="flex items-center gap-2">

                            <flux:button
                                type="button"
                                wire:click="edit({{ $productbatch->id }})"
                                wire:loading.attr="disabled"
                                wire:target="edit({{ $productbatch->id }})"
                                variant="primary"
                                color="yellow"
                                size="sm"
                            >
                                ویرایش
                            </flux:button>

                            <flux:button
                                type="button"
                                wire:click="delete_form({{ $productbatch->id }})"
                                wire:loading.attr="disabled"
                                wire:target="delete_form({{ $productbatch->id }})"
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
                        colspan="8"
                        class="py-8 text-center text-zinc-500 dark:text-zinc-400"
                    >
                        هنوز هیچ موجودی برای این دسته‌بندی ثبت نشده است.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>

    </flux:table>


    {{-- ====================================================== --}}
    {{-- مودال ثبت موجودی جدید --}}
    {{-- ====================================================== --}}

    <flux:modal
        name="save"
        class="md:w-[30rem]"
    >
        <div class="space-y-6">

            {{-- عنوان مودال --}}
            <div>
                <flux:heading size="lg">
                    ثبت موجودی جدید
                </flux:heading>

                <flux:text class="mt-2">
                    اطلاعات موجودی جدید را وارد کنید.
                </flux:text>
            </div>

            {{-- خطای عمومی ثبت --}}
            @error('save_error')
            <div
                class="rounded-md border border-red-200 bg-red-50 p-3 text-sm font-medium text-red-600
                           dark:border-red-800 dark:bg-red-950/40 dark:text-red-300"
            >
                {{ $message }}
            </div>
            @enderror

            {{-- انتخاب محصول --}}
            <div>
                <flux:select
                    wire:model="pro_id"
                    label="محصول"
                    placeholder="یک محصول را انتخاب کنید"
                >
                    @foreach ($this->category->products as $product)
                        <flux:select.option
                            value="{{ $product->id }}"
                            wire:key="save-product-{{ $product->id }}"
                        >
                            {{ $product->name }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                @error('pro_id')
                <p class="mt-1 text-xs font-medium text-red-600">
                    {{ $message }}
                </p>
                @enderror
            </div>

            {{-- قیمت فروش --}}
            <div>
                <flux:input
                    wire:model="sale_price"
                    type="number"
                    min="0"
                    step="1"
                    label="قیمت فروش"
                    placeholder="مثال: 150000"
                    autocomplete="off"
                />

                @error('sale_price')
                <p class="mt-1 text-xs font-medium text-red-600">
                    {{ $message }}
                </p>
                @enderror
            </div>

            {{-- تاریخ تولید --}}
            <div>
                <flux:field>
                    <flux:label>
                        تاریخ تولید
                    </flux:label>

                    <input
                        wire:model="p_date"
                        type="date"
                        class="block h-10 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm
                               text-zinc-900 shadow-sm outline-none transition
                               focus:border-zinc-400 focus:ring-2 focus:ring-zinc-200
                               disabled:cursor-not-allowed disabled:opacity-50
                               dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100
                               dark:focus:border-zinc-500 dark:focus:ring-zinc-700"
                    />

                    <flux:description>
                        تاریخ را از تقویم انتخاب کنید.
                    </flux:description>
                </flux:field>

                @error('p_date')
                <p class="mt-1 text-xs font-medium text-red-600">
                    {{ $message }}
                </p>
                @enderror
            </div>

            {{-- تاریخ انقضا --}}
            <div>
                <flux:field>
                    <flux:label>
                        تاریخ انقضا
                    </flux:label>

                    <input
                        wire:model="ex_date"
                        type="date"
                        min="{{ $p_date ?: '' }}"
                        class="block h-10 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm
                               text-zinc-900 shadow-sm outline-none transition
                               focus:border-zinc-400 focus:ring-2 focus:ring-zinc-200
                               disabled:cursor-not-allowed disabled:opacity-50
                               dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100
                               dark:focus:border-zinc-500 dark:focus:ring-zinc-700"
                    />

                    <flux:description>
                        تاریخ انقضا نباید قبل از تاریخ تولید باشد.
                    </flux:description>
                </flux:field>

                @error('ex_date')
                <p class="mt-1 text-xs font-medium text-red-600">
                    {{ $message }}
                </p>
                @enderror
            </div>

            {{-- تعداد --}}
            <div>
                <flux:input
                    wire:model="quantity"
                    type="number"
                    min="1"
                    step="1"
                    label="تعداد"
                    placeholder="مثال: 20"
                    autocomplete="off"
                />

                @error('quantity')
                <p class="mt-1 text-xs font-medium text-red-600">
                    {{ $message }}
                </p>
                @enderror
            </div>

            {{-- دکمه‌های مودال --}}
            <div class="flex items-center gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button
                        type="button"
                        wire:click="reset_data"
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
                >
                    <span wire:loading.remove wire:target="save">
                        ثبت موجودی
                    </span>

                    <span wire:loading wire:target="save">
                        در حال ثبت...
                    </span>
                </flux:button>
            </div>

        </div>
    </flux:modal>


    {{-- ====================================================== --}}
    {{-- مودال ویرایش موجودی --}}
    {{-- ====================================================== --}}

    <flux:modal
        name="edit-user"
        class="md:w-[30rem]"
    >
        <div class="space-y-6">

            {{-- عنوان مودال --}}
            <div>
                <flux:heading size="lg">
                    ویرایش موجودی
                </flux:heading>

                <flux:text class="mt-2">
                    اطلاعات موجودی را ویرایش کنید.
                </flux:text>
            </div>

            {{-- خطای عمومی ویرایش --}}
            @error('update_error')
            <div
                class="rounded-md border border-red-200 bg-red-50 p-3 text-sm font-medium text-red-600
                           dark:border-red-800 dark:bg-red-950/40 dark:text-red-300"
            >
                {{ $message }}
            </div>
            @enderror

            {{-- انتخاب محصول --}}
            <div>
                <flux:select
                    wire:model="pro_id"
                    label="محصول"
                    placeholder="یک محصول را انتخاب کنید"
                >
                    @foreach ($this->category->products as $product)
                        <flux:select.option
                            value="{{ $product->id }}"
                            wire:key="edit-product-{{ $product->id }}"
                        >
                            {{ $product->name }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                @error('pro_id')
                <p class="mt-1 text-xs font-medium text-red-600">
                    {{ $message }}
                </p>
                @enderror
            </div>

            {{-- قیمت فروش --}}
            <div>
                <flux:input
                    wire:model="sale_price"
                    type="number"
                    min="0"
                    step="1"
                    label="قیمت فروش"
                    placeholder="مثال: 150000"
                    autocomplete="off"
                />

                @error('sale_price')
                <p class="mt-1 text-xs font-medium text-red-600">
                    {{ $message }}
                </p>
                @enderror
            </div>

            {{-- تاریخ تولید در ویرایش --}}
            <div>
                <flux:field>
                    <flux:label>
                        تاریخ تولید
                    </flux:label>

                    <input
                        wire:model="p_date"
                        type="date"
                        class="block h-10 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm
                               text-zinc-900 shadow-sm outline-none transition
                               focus:border-zinc-400 focus:ring-2 focus:ring-zinc-200
                               disabled:cursor-not-allowed disabled:opacity-50
                               dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100
                               dark:focus:border-zinc-500 dark:focus:ring-zinc-700"
                    />

                    <flux:description>
                        تاریخ تولید فعلی را بررسی یا تغییر دهید.
                    </flux:description>
                </flux:field>

                @error('p_date')
                <p class="mt-1 text-xs font-medium text-red-600">
                    {{ $message }}
                </p>
                @enderror
            </div>

            {{-- تاریخ انقضا در ویرایش --}}
            <div>
                <flux:field>
                    <flux:label>
                        تاریخ انقضا
                    </flux:label>

                    <input
                        wire:model="ex_date"
                        type="date"
                        min="{{ $p_date ?: '' }}"
                        class="block h-10 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm
                               text-zinc-900 shadow-sm outline-none transition
                               focus:border-zinc-400 focus:ring-2 focus:ring-zinc-200
                               disabled:cursor-not-allowed disabled:opacity-50
                               dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100
                               dark:focus:border-zinc-500 dark:focus:ring-zinc-700"
                    />

                    <flux:description>
                        تاریخ انقضا نباید قبل از تاریخ تولید باشد.
                    </flux:description>
                </flux:field>

                @error('ex_date')
                <p class="mt-1 text-xs font-medium text-red-600">
                    {{ $message }}
                </p>
                @enderror
            </div>

            {{-- تعداد --}}
            <div>
                <flux:input
                    wire:model="quantity"
                    type="number"
                    min="1"
                    step="1"
                    label="تعداد"
                    placeholder="مثال: 20"
                    autocomplete="off"
                />

                @error('quantity')
                <p class="mt-1 text-xs font-medium text-red-600">
                    {{ $message }}
                </p>
                @enderror
            </div>

            {{-- دکمه‌های مودال --}}
            <div class="flex items-center gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button
                        type="button"
                        wire:click="reset_data"
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
    {{-- مودال حذف موجودی --}}
    {{-- ====================================================== --}}

    <flux:modal
        name="delete-user"
        class="md:w-96"
    >
        <div class="space-y-6">

            <div>
                <flux:heading size="lg">
                    حذف موجودی
                </flux:heading>

                <flux:text class="mt-2">
                    آیا از حذف موجودی محصول

                    <strong class="font-bold text-zinc-900 dark:text-zinc-100">
                        {{ $product_name }}
                    </strong>

                    مطمئن هستید؟
                </flux:text>
            </div>

            <div
                class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700
                       dark:border-red-800 dark:bg-red-950/40 dark:text-red-300"
            >
                این عملیات قابل بازگشت نیست.
            </div>

            <div class="flex items-center justify-end gap-2">
                <flux:modal.close>
                    <flux:button
                        type="button"
                        wire:click="reset_data"
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
    <a href="{{ route('category') }}"
       class="inline-block rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
        برگشت
    </a>

</div>

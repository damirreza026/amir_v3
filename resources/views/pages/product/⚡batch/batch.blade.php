<div class="space-y-6">
    <!-- پیام‌های موفقیت / خطا -->
    @if (session()->has('success'))
        <div class="rounded-lg bg-emerald-500/10 border border-emerald-500/20 p-4 text-emerald-600 dark:text-emerald-400 text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="rounded-lg bg-rose-500/10 border border-rose-500/20 p-4 text-rose-600 dark:text-rose-400 text-sm font-medium">
            {{ session('error') }}
        </div>
    @endif

    <!-- هدر و دکمه افزودن -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">مدیریت موجودی دسته: {{ $category->name }}</flux:heading>
            <flux:subheading size="sm">لیست بچ‌ها و موجودی‌های ثبت‌شده برای این دسته‌بندی</flux:subheading>
        </div>

        <div>
            <flux:button wire:click="openSaveModal" variant="primary" icon="plus">
                افزودن موجودی جدید
            </flux:button>
        </div>
    </div>

    <!-- فیلترها و جست‌وجو -->
    <div class="bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <!-- جست‌وجوی نام محصول -->
            <div class="lg:col-span-1">
                <flux:input
                    wire:model.live.debounce.300ms="search_product"
                    placeholder="جست‌وجوی محصول..."
                    icon="magnifying-glass"
                    size="sm"
                    clearable
                />
            </div>

            <!-- جست‌وجوی کارمند -->
            <div class="lg:col-span-1">
                <flux:input
                    wire:model.live.debounce.300ms="search_employee"
                    placeholder="جست‌وجوی کارمند..."
                    icon="user"
                    size="sm"
                    clearable
                />
            </div>

            <!-- فیلتر سال شمسی -->
            <div>
                <flux:select wire:model.live="selected_year" size="sm" placeholder="همه سال‌ها">
                    <flux:select.option value="">همه سال‌ها</flux:select.option>
                    @foreach ($this->availableYears as $year)
                        <flux:select.option :value="$year">{{ $year }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <!-- فیلتر ماه شمسی -->
            <div>
                <flux:select wire:model.live="selected_month" size="sm" placeholder="همه ماه‌ها" :disabled="empty($selected_year)">
                    <flux:select.option value="">همه ماه‌ها</flux:select.option>
                    @foreach ($this->availableMonths as $month)
                        <flux:select.option :value="$month">{{ $this->persianMonths[$month] ?? $month }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <!-- فیلتر روز شمسی -->
            <div>
                <flux:select wire:model.live="selected_day" size="sm" placeholder="همه روزها" :disabled="empty($selected_month)">
                    <flux:select.option value="">همه روزها</flux:select.option>
                    @foreach ($this->availableDays as $day)
                        <flux:select.option :value="$day">{{ $day }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        @if (filled($search_product) || filled($search_employee) || filled($selected_year))
            <div class="flex items-center justify-end">
                <flux:button wire:click="clearDateFilters" size="xs" variant="ghost" icon="x-mark">
                    پاکسازی همه فیلترها
                </flux:button>
            </div>
        @endif
    </div>

    <!-- جدول موجودی‌ها -->
    <flux:table :paginate="$this->productbatchs">
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'product_id'" :direction="$sortDirection" wire:click="sort('product_id')">نام محصول</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'quantity'" :direction="$sortDirection" wire:click="sort('quantity')">تعداد موجودی</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'sale_price'" :direction="$sortDirection" wire:click="sort('sale_price')">قیمت فروش (تومان)</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'production_date'" :direction="$sortDirection" wire:click="sort('production_date')">تاریخ تولید</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'expiry_date'" :direction="$sortDirection" wire:click="sort('expiry_date')">تاریخ انقضا</flux:table.column>
            <flux:table.column>ثبت‌کننده</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">تاریخ ثبت</flux:table.column>
            <flux:table.column align="end">عملیات</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->productbatchs as $batch)
                <flux:table.row :key="$batch->id">
                    <flux:table.cell class="font-medium text-zinc-900 dark:text-zinc-100">
                        {{ $batch->product?->name ?? '---' }}
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:badge size="sm" :color="$batch->quantity > 5 ? 'zinc' : 'red'">
                            {{ number_format($batch->quantity) }}
                        </flux:badge>
                    </flux:table.cell>

                    <flux:table.cell>
                        {{ number_format($batch->sale_price) }}
                    </flux:table.cell>

                    <flux:table.cell class="text-zinc-600 dark:text-zinc-400">
                        {{ $batch->production_date ? \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($batch->production_date))->format('Y/m/d') : '---' }}
                    </flux:table.cell>

                    <flux:table.cell class="text-zinc-600 dark:text-zinc-400">
                        {{ $batch->expiry_date ? \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($batch->expiry_date))->format('Y/m/d') : '---' }}
                    </flux:table.cell>

                    <flux:table.cell class="text-zinc-600 dark:text-zinc-400">
                        {{ $batch->profile ? $batch->profile->first_name . ' ' . $batch->profile->last_name : '---' }}
                    </flux:table.cell>

                    <flux:table.cell class="text-zinc-600 dark:text-zinc-400">
                        {{ \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($batch->created_at)->setTimezone('Asia/Tehran'))->format('Y/m/d H:i') }}
                    </flux:table.cell>

                    <flux:table.cell align="end">
                        <div class="flex items-center justify-end gap-1">
                            <flux:button wire:click="edit({{ $batch->id }})" size="xs" variant="ghost" icon="pencil-square" title="ویرایش" />
                            <flux:button wire:click="delete_form({{ $batch->id }})" size="xs" variant="ghost" icon="trash" class="text-rose-600 hover:text-rose-700" title="حذف" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="8" class="text-center py-8 text-zinc-500">
                        هیچ رکوردی برای نمایش یافت نشد.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
    <!-- ==================== مودال افزودن ==================== -->
    <flux:modal name="save-modal" class="md:w-[32rem]">
        <form wire:submit="save" class="space-y-5">
            <div>
                <flux:heading size="lg">افزودن موجودی جدید</flux:heading>
                <flux:subheading size="sm">اطلاعات محصول و مشخصات تاریخ و تعداد را وارد کنید.</flux:subheading>
            </div>

            @error('save_error')
            <div class="p-3 bg-rose-500/10 border border-rose-500/20 text-rose-500 rounded-lg text-sm">
                {{ $message }}
            </div>
            @enderror

            <!-- انتخاب محصول -->
            <flux:select wire:model="pro_id" label="محصول" placeholder="انتخاب محصول...">
                @foreach ($this->products as $p)
                    <flux:select.option :value="$p->id">{{ $p->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <div class="grid grid-cols-2 gap-3">
                <!-- قیمت فروش -->
                <flux:input wire:model="sale_price" label="قیمت فروش (تومان)" type="number" min="0" />
                <!-- تعداد -->
                <flux:input wire:model="quantity" label="تعداد موجودی" type="number" min="1" />
            </div>

            <!-- تاریخ تولید -->
            <div class="space-y-1">
                <flux:label>تاریخ تولید (شمسی)</flux:label>
                <div class="grid grid-cols-3 gap-2">
                    <flux:input wire:model="p_day" placeholder="روز (1-31)" type="number" min="1" max="31" />
                    <flux:select wire:model="p_month" placeholder="ماه">
                        @foreach ($this->persianMonths as $num => $name)
                            <flux:select.option :value="$num">{{ $name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:input wire:model="p_year" placeholder="سال (مثلا 1403)" type="number" />
                </div>
                @error('p_year') <flux:error>{{ $message }}</flux:error> @enderror
                @error('p_month') <flux:error>{{ $message }}</flux:error> @enderror
                @error('p_day') <flux:error>{{ $message }}</flux:error> @enderror
            </div>

            <!-- تاریخ انقضا -->
            <div class="space-y-1">
                <flux:label>تاریخ انقضا (شمسی)</flux:label>
                <div class="grid grid-cols-3 gap-2">
                    <flux:input wire:model="ex_day" placeholder="روز (1-31)" type="number" min="1" max="31" />
                    <flux:select wire:model="ex_month" placeholder="ماه">
                        @foreach ($this->persianMonths as $num => $name)
                            <flux:select.option :value="$num">{{ $name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:input wire:model="ex_year" placeholder="سال (مثلا 1404)" type="number" />
                </div>
                @error('ex_year') <flux:error>{{ $message }}</flux:error> @enderror
                @error('ex_month') <flux:error>{{ $message }}</flux:error> @enderror
                @error('ex_day') <flux:error>{{ $message }}</flux:error> @enderror
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <flux:modal.close>
                    <flux:button variant="ghost">انصراف</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">ذخیره موجودی</flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- ==================== مودال ویرایش ==================== -->
    <flux:modal name="edit-modal" class="md:w-[32rem]">
        <form wire:submit="update" class="space-y-5">
            <div>
                <flux:heading size="lg">ویرایش موجودی</flux:heading>
                <flux:subheading size="sm">تغییر مشخصات و اطلاعات بچ موجودی</flux:subheading>
            </div>

            @error('update_error')
            <div class="p-3 bg-rose-500/10 border border-rose-500/20 text-rose-500 rounded-lg text-sm">
                {{ $message }}
            </div>
            @enderror

            <!-- انتخاب محصول -->
            <flux:select wire:model="pro_id" label="محصول" placeholder="انتخاب محصول...">
                @foreach ($this->products as $p)
                    <flux:select.option :value="$p->id">{{ $p->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <div class="grid grid-cols-2 gap-3">
                <flux:input wire:model="sale_price" label="قیمت فروش (تومان)" type="number" min="0" />
                <flux:input wire:model="quantity" label="تعداد موجودی" type="number" min="1" />
            </div>

            <!-- تاریخ تولید -->
            <div class="space-y-1">
                <flux:label>تاریخ تولید (شمسی)</flux:label>
                <div class="grid grid-cols-3 gap-2">
                    <flux:input wire:model="p_day" placeholder="روز (1-31)" type="number" min="1" max="31" />
                    <flux:select wire:model="p_month" placeholder="ماه">
                        @foreach ($this->persianMonths as $num => $name)
                            <flux:select.option :value="$num">{{ $name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:input wire:model="p_year" placeholder="سال" type="number" />
                </div>
                @error('p_year') <flux:error>{{ $message }}</flux:error> @enderror
                @error('p_month') <flux:error>{{ $message }}</flux:error> @enderror
                @error('p_day') <flux:error>{{ $message }}</flux:error> @enderror
            </div>

            <!-- تاریخ انقضا -->
            <div class="space-y-1">
                <flux:label>تاریخ انقضا (شمسی)</flux:label>
                <div class="grid grid-cols-3 gap-2">
                    <flux:input wire:model="ex_day" placeholder="روز (1-31)" type="number" min="1" max="31" />
                    <flux:select wire:model="ex_month" placeholder="ماه">
                        @foreach ($this->persianMonths as $num => $name)
                            <flux:select.option :value="$num">{{ $name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:input wire:model="ex_year" placeholder="سال" type="number" />
                </div>
                @error('ex_year') <flux:error>{{ $message }}</flux:error> @enderror
                @error('ex_month') <flux:error>{{ $message }}</flux:error> @enderror
                @error('ex_day') <flux:error>{{ $message }}</flux:error> @enderror
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <flux:modal.close>
                    <flux:button variant="ghost">انصراف</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">بروزرسانی</flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- ==================== مودال حذف ==================== -->
    <flux:modal name="delete-modal" class="md:w-96">
        <div class="space-y-5">
            <div>
                <flux:heading size="lg">حذف موجودی</flux:heading>
                <flux:subheading size="sm">
                    آیا از حذف این رکورد موجودی برای محصول <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $product_name }}</span> اطمینان دارید؟ این عملیات غیرقابل بازگشت است.
                </flux:subheading>
            </div>

            <div class="flex items-center justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">انصراف</flux:button>
                </flux:modal.close>
                <flux:button wire:click="delete" variant="danger">حذف قطعی</flux:button>
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

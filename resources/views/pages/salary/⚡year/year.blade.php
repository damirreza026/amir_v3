<div class="space-y-6" dir="rtl">
    @if (session()->has('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-bold text-green-700 flex items-center gap-2">
            <flux:icon.check-circle class="size-5" />
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700 flex items-center gap-2">
            <flux:icon.x-circle class="size-5" />
            {{ session('error') }}
        </div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3 bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
        <div>
            <flux:button variant="primary" wire:click="openAddModal" icon="plus" class="font-bold">
                تعریف سال مالی جدید
            </flux:button>
        </div>

        <div class="w-64">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="جستجوی سال مالی..."
                icon="magnifying-glass"
                clearable
            />
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <flux:table :paginate="$this->years">
            <flux:table.columns>
                <flux:table.column sortable :sorted="$sortBy === 'year'" :direction="$sortDirection" wire:click="sort('year')">
                    سال مالی
                </flux:table.column>
                <flux:table.column>وضعیت ساختار</flux:table.column>
                <flux:table.column>مدیریت محتوا</flux:table.column>
                <flux:table.column>عملیات</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->years as $yearItem)
                    <flux:table.row :key="$yearItem->id" class="hover:bg-slate-50 transition-colors">
                        <flux:table.cell class="whitespace-nowrap font-black text-slate-800 text-lg">
                            {{ $yearItem->year }}
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap">
                            <span class="text-xs font-semibold bg-slate-100 text-slate-600 px-2 py-1 rounded-md">
                                ۱۲ ماه + ۵۴ هفته اتوماتیک
                            </span>
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap">
                            <a
                                href="{{ URL::signedRoute('month', ['year' => $yearItem->id]) }}"
                                class="inline-flex items-center gap-2 text-sm font-bold text-indigo-600 hover:text-indigo-800 transition-colors"
                            >
                                <flux:icon.calendar-days class="size-4" />
                                مدیریت ماه‌ها
                                <flux:icon.arrow-left class="size-4" />
                            </a>
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap">
                            <div class="flex space-x-2 space-x-reverse">
                                <flux:button variant="subtle" color="red" size="sm" wire:click="delete_form({{ $yearItem->id }})" icon="trash">
                                    حذف سال
                                </flux:button>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4">
                            <div class="py-12 text-center text-sm text-slate-400 font-medium">
                                <flux:icon.calendar class="mx-auto size-10 mb-3 opacity-20" />
                                هیچ سال مالی یافت نشد.
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    {{-- مودال افزودن سال مالی --}}
    <flux:modal name="add-year" class="md:w-96" @close="reset_data">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg" class="font-black">تعریف سال مالی جدید</flux:heading>
                <flux:text class="mt-2 text-slate-500 leading-relaxed">
                    با ثبت سال مالی، <strong class="text-slate-900">۱۲ ماه</strong> و تمامی <strong class="text-slate-900">هفته‌ها</strong> (۶ ماه اول ۵ هفته‌ای و ۶ ماه دوم ۴ هفته‌ای) به صورت هوشمند ایجاد می‌گردند.
                </flux:text>
            </div>

            @error('save_error')
            <div class="rounded-lg bg-red-50 p-3 text-xs font-bold text-red-600 border border-red-100">{{ $message }}</div>
            @enderror

            <div class="space-y-2">
                <flux:input wire:model="year" label="سال مالی (مثال: ۱۴۰۳)" placeholder="۱۴۰۳" class="font-bold" />
                @error('year') <p class="mt-1 text-xs font-bold text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end gap-2 border-t pt-4">
                <flux:button wire:click="reset_data" variant="ghost" class="font-bold">انصراف</flux:button>
                <flux:button wire:click="save" type="submit" variant="primary" class="font-bold px-6">ایجاد سال و ساختار هوشمند</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- مودال حذف سال مالی --}}
    <flux:modal name="delete-year" class="md:w-96" @close="reset_data">
        <div class="space-y-6 p-2">
            <div class="text-center">
                <div class="w-16 h-16 bg-red-50 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <flux:icon.trash class="size-8" />
                </div>
                <flux:heading size="lg" class="font-black">حذف سال مالی {{ $year }}</flux:heading>
                <flux:text class="mt-3 text-red-600 font-medium leading-relaxed">
                    هشدار: با این کار تمامی ماه‌ها، هفته‌ها و کلیه اطلاعات حقوقی ثبت شده در این سال به صورت دائمی حذف خواهد شد.
                </flux:text>
            </div>

            <div class="flex flex-col gap-2 mt-4">
                <flux:button wire:click="delete" type="submit" color="red" variant="primary" class="w-full font-bold">بله، کاملاً حذف شود</flux:button>
                <flux:button wire:click="reset_data" variant="ghost" class="w-full font-bold">لغو عملیات</flux:button>
            </div>
        </div>
    </flux:modal>
</div>

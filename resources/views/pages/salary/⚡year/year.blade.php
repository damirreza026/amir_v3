<div class="space-y-6">
    @if (session()->has('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <flux:button variant="primary" wire:click="openAddModal" icon="plus">
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

    <flux:table :paginate="$this->years">
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'year'" :direction="$sortDirection" wire:click="sort('year')">
                سال مالی
            </flux:table.column>
            <flux:table.column>
                تعداد ماه‌ها
            </flux:table.column>
            <flux:table.column>
                مدیریت ماه‌ها
            </flux:table.column>
            <flux:table.column>عملیات</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->years as $yearItem)
                <flux:table.row :key="$yearItem->id">
                    <flux:table.cell class="whitespace-nowrap font-bold text-gray-800">
                        {{ $yearItem->year }}
                    </flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap">
                        {{ method_exists($yearItem, 'months') ? $yearItem->months()->count() : 12 }} ماه
                    </flux:table.cell>

                    {{-- لینک امضاشده جهت جلوگیری از خطای ۴۰۳ میدلور signed --}}
                    <flux:table.cell class="whitespace-nowrap">
                        <a
                            href="{{ URL::signedRoute('month', ['year' => $yearItem->id]) }}"
                            class="inline-flex items-center gap-1 text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline"
                        >
                            مشاهده و مدیریت هفته های هر ماه‌
                            <flux:icon.arrow-left class="size-4" />
                        </a>
                    </flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap">
                        <div class="flex space-x-2 space-x-reverse">
                            <flux:button variant="primary" color="red" wire:click="delete_form({{ $yearItem->id }})">
                                حذف
                            </flux:button>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="4">
                        <div class="py-6 text-center text-sm text-gray-500">
                            هیچ سال مالی ثبت نشده است.
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- مودال افزودن سال مالی --}}
    <flux:modal name="add-year" class="md:w-96" @close="reset_data">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">تعریف سال مالی جدید</flux:heading>
                <flux:text class="mt-2">با ثبت سال مالی، ۱۲ ماه به صورت خودکار ایجاد می‌گردند.</flux:text>
            </div>

            @error('save_error')
            <div class="rounded bg-red-50 p-2 text-xs font-bold text-red-600">{{ $message }}</div>
            @enderror

            <div>
                <flux:input wire:model="year" label="سال مالی (مثال: ۱۴۰۳)" placeholder="۱۴۰۳" />
                @error('year') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end space-x-2 space-x-reverse">
                <flux:button wire:click="reset_data" variant="ghost">انصراف</flux:button>
                <flux:button wire:click="save" type="submit" variant="primary">ایجاد سال</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- مودال حذف سال مالی --}}
    <flux:modal name="delete-year" class="md:w-96" @close="reset_data">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">حذف سال مالی</flux:heading>
                <flux:text class="mt-2 text-red-600">
                    آیا از حذف سال مالی {{ $year }} و تمامی ماه‌ها و اطلاعات مربوط به آن مطمئن هستید؟
                </flux:text>
            </div>

            <div class="flex justify-end space-x-2 space-x-reverse">
                <flux:button wire:click="reset_data" variant="ghost">لغو</flux:button>
                <flux:button wire:click="delete" type="submit" color="red" variant="primary">حذف نهایی</flux:button>
            </div>
        </div>
    </flux:modal>
</div>

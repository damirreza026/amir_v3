<div dir="rtl">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold">ماه‌های سال {{ $year->year }}</h1>
            <p class="text-sm text-gray-500">لیست تمام ماه‌های سال {{ $year->year }}</p>
        </div>

        <flux:button wire:click="openSaveModal" variant="primary" color="green">
            افزودن ماه جدید
        </flux:button>
    </div>

    <hr class="my-4 border-gray-200">

    @if (session()->has('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <flux:table :paginate="$this->months">
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'month'" :direction="$sortDirection" wire:click="sort('month')">
                ماه
            </flux:table.column>
            <flux:table.column>
                عملیات
            </flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->months as $item)
                <flux:table.row :key="$item->id">
                    <flux:table.cell class="whitespace-nowrap font-medium">
                        {{ $item->month }} - {{ $item->month_name }}
                    </flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap">
                        <div class="flex space-x-2 space-x-reverse">
                            <flux:button variant="primary" color="yellow" size="sm" wire:click="edit({{ $item->id }})">
                                ویرایش
                            </flux:button>

                            <flux:button variant="primary" color="red" size="sm" wire:click="delete_form({{ $item->id }})">
                                حذف
                            </flux:button>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="2">
                        <div class="py-6 text-center text-sm text-gray-500">
                            هنوز ماهی برای این سال ثبت نشده است.
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal name="save" class="md:w-96" @close="reset_data">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">ماه جدید</flux:heading>
                <flux:text class="mt-2">افزودن ماه جدید به سال «{{ $year->year }}»</flux:text>
            </div>

            @error('save_error')
            <div class="rounded bg-red-50 p-2 text-xs font-bold text-red-600">{{ $message }}</div>
            @enderror

            <div>
                <label for="save_month" class="mb-2 block text-sm font-medium text-gray-700">
                    انتخاب ماه
                </label>

                <select
                    id="save_month"
                    wire:model="month"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                >
                    <option value="">انتخاب ماه</option>

                    @foreach ($monthNames as $number => $name)
                        <option value="{{ $number }}">
                            {{ $number }} - {{ $name }}
                        </option>
                    @endforeach
                </select>

                @error('month')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex">
                <flux:spacer/>
                <flux:button wire:click="save" type="button" variant="primary">
                    ایجاد نهایی ماه
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="edit-month" class="md:w-96" @close="reset_data">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">ویرایش ماه</flux:heading>
                <flux:text class="mt-2">ویرایش اطلاعات ماه در سال «{{ $year->year }}»</flux:text>
            </div>

            @error('update_error')
            <div class="rounded bg-red-50 p-2 text-xs font-bold text-red-600">{{ $message }}</div>
            @enderror

            <div>
                <label for="edit_month" class="mb-2 block text-sm font-medium text-gray-700">
                    انتخاب ماه
                </label>

                <select
                    id="edit_month"
                    wire:model="month"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                >
                    <option value="">انتخاب ماه</option>

                    @foreach ($monthNames as $number => $name)
                        <option value="{{ $number }}">
                            {{ $number }} - {{ $name }}
                        </option>
                    @endforeach
                </select>

                @error('month')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex">
                <flux:spacer/>
                <flux:button wire:click="update" type="button" variant="primary">
                    ثبت تغییرات
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="delete-month" class="md:w-96" @close="reset_data">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">حذف ماه</flux:heading>
                <flux:text class="mt-2">
                    آیا از حذف ماه «{{ $month }} - {{ $month_name }}» مطمئن هستید؟
                </flux:text>
            </div>

            <div class="flex justify-end space-x-2 space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="ghost">لغو</flux:button>
                </flux:modal.close>

                <flux:button wire:click="delete" type="button" color="red" variant="primary">
                    حذف نهایی
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <div class="mt-6">
        <a
            href="{{ route('year') }}"
            class="inline-block rounded-lg !bg-blue-600 px-4 py-2 !text-white no-underline transition hover:!bg-blue-700"
            style="background-color: #2563eb !important; color: #ffffff !important;"
        >
            برگشت
        </a>
    </div>

</div>

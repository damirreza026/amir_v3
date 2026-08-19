<div dir="rtl" class="space-y-6">

    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-end">

        <div>
            <h1 class="text-xl font-bold">
                مدیریت هفته‌های حقوقی
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                سال: {{ $year->year }}
            </p>
        </div>

        <div class="flex items-end gap-3">

            <div class="w-64">
                <flux:select
                    wire:model.live="selected_month_id"
                    label="انتخاب ماه"
                    placeholder="انتخاب ماه"
                >
                    @foreach ($this->months as $month)
                        <flux:select.option value="{{ $month->id }}">
                            {{ $month->month }} - {{ $month->month_name }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <flux:button
                variant="primary"
                :disabled="! $selected_month_id"
                wire:click="openSaveModal"
            >
                افزودن هفته
            </flux:button>

        </div>
    </div>

    @if (session()->has('success'))
        <div class="rounded-lg bg-green-50 p-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="rounded-lg bg-red-50 p-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @if (! $selected_month_id)

        <div class="rounded-lg border border-dashed p-8 text-center text-gray-500">
            ابتدا یک ماه را انتخاب کنید.
        </div>

    @else

        <div class="overflow-x-auto rounded-lg border">
            <table class="w-full text-right text-sm">

                <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3">شماره هفته</th>
                    <th class="px-4 py-3">نام هفته</th>
                    <th class="px-4 py-3">عملیات</th>
                </tr>
                </thead>

                <tbody class="divide-y">

                @forelse ($this->weeks as $item)
                    <tr wire:key="week-{{ $item->id }}">

                        <td class="px-4 py-3">
                            {{ $item->week }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $item->week_name }}
                        </td>

                        <td class="px-4 py-3">
                            <div class="flex gap-2">

                                <flux:button
                                    size="sm"
                                    variant="primary"
                                    color="yellow"
                                    wire:click="edit({{ $item->id }})"
                                >
                                    ویرایش
                                </flux:button>

                                <flux:button
                                    size="sm"
                                    variant="primary"
                                    color="red"
                                    wire:click="deleteForm({{ $item->id }})"
                                >
                                    حذف
                                </flux:button>

                            </div>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-gray-500">
                            هنوز هفته‌ای برای این ماه ثبت نشده است.
                        </td>
                    </tr>
                @endforelse

                </tbody>
            </table>
        </div>

        <div>
            {{ $this->weeks->links() }}
        </div>

    @endif


    <flux:modal name="save-week" class="md:w-96">

        <div class="space-y-5">

            <flux:heading size="lg">
                افزودن هفته
            </flux:heading>

            @error('save_error')
            <div class="rounded bg-red-50 p-2 text-sm text-red-600">
                {{ $message }}
            </div>
            @enderror

            <flux:select
                wire:model.live="week"
                label="شماره هفته"
                placeholder="انتخاب هفته"
            >
                @foreach ($weekNames as $number => $name)
                    <flux:select.option value="{{ $number }}">
                        {{ $number }} - {{ $name }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            @error('week')
            <div class="text-xs text-red-600">
                {{ $message }}
            </div>
            @enderror

            <flux:input
                wire:model="week_name"
                label="نام هفته (قابل ویرایش)"
                placeholder="مثلاً هفته اول مرداد ۴ روز کاری"
            />

            @error('week_name')
            <div class="text-xs text-red-600">
                {{ $message }}
            </div>
            @enderror

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">
                        انصراف
                    </flux:button>
                </flux:modal.close>

                <flux:button
                    wire:click="save"
                    variant="primary"
                >
                    ثبت
                </flux:button>
            </div>

        </div>

    </flux:modal>


    <flux:modal name="edit-week" class="md:w-96">

        <div class="space-y-5">

            <flux:heading size="lg">
                ویرایش هفته
            </flux:heading>

            @error('update_error')
            <div class="rounded bg-red-50 p-2 text-sm text-red-600">
                {{ $message }}
            </div>
            @enderror

            <flux:select
                wire:model.live="week"
                label="شماره هفته"
                placeholder="انتخاب هفته"
            >
                @foreach ($weekNames as $number => $name)
                    <flux:select.option value="{{ $number }}">
                        {{ $number }} - {{ $name }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            @error('week')
            <div class="text-xs text-red-600">
                {{ $message }}
            </div>
            @enderror

            <flux:input
                wire:model="week_name"
                label="نام هفته (قابل ویرایش)"
                placeholder="مثلاً هفته اول مرداد ۴ روز کاری"
            />

            @error('week_name')
            <div class="text-xs text-red-600">
                {{ $message }}
            </div>
            @enderror

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">
                        انصراف
                    </flux:button>
                </flux:modal.close>

                <flux:button
                    wire:click="update"
                    variant="primary"
                >
                    ذخیره تغییرات
                </flux:button>
            </div>

        </div>

    </flux:modal>


    <flux:modal name="delete-week" class="md:w-96">

        <div class="space-y-5">

            <flux:heading size="lg">
                حذف هفته
            </flux:heading>

            <p class="text-sm text-gray-600">
                آیا از حذف
                <strong>{{ $week_name }}</strong>
                مطمئن هستید؟
            </p>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">
                        انصراف
                    </flux:button>
                </flux:modal.close>

                <flux:button
                    wire:click="delete"
                    variant="primary"
                    color="red"
                >
                    حذف نهایی
                </flux:button>
            </div>

        </div>

    </flux:modal>

</div>

<div dir="rtl" class="space-y-6">

    {{-- سربرگ صفحه با زمینه سفید و روشن --}}
    <div class="flex flex-col justify-between gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm md:flex-row md:items-center">
        <div>
            <h1 class="text-xl font-black text-gray-900">
                مدیریت هفته‌های کاری
            </h1>

            <div class="mt-3 flex flex-wrap items-center gap-2 text-sm">
                <span class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-1.5 font-bold text-gray-800 border border-gray-200">
                    سال مالی: {{ $year->year }}
                </span>

                @if ($this->currentMonth)
                    <span class="inline-flex items-center rounded-lg bg-indigo-50 px-3 py-1.5 font-bold text-indigo-700 border border-indigo-200">
                        ماه:
                        {{ $this->currentMonth->month_name }}
                        ({{ $this->currentMonth->month }})
                    </span>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            {{-- دکمه افزودن هفته --}}
            <flux:button
                variant="primary"
                :disabled="! $selected_month_id"
                wire:click="openSaveModal"
                class="!cursor-pointer font-bold"
            >
                افزودن هفته
            </flux:button>

            <a
                href="{{ URL::signedRoute('month', ['year' => $year->id]) }}"
                class="inline-flex items-center gap-2 rounded-lg bg-gray-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-800 !cursor-pointer"
            >
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span>برگشت به ماه‌ها</span>
            </a>
        </div>
    </div>

    {{-- پیام‌های هشدار --}}
    @if (session()->has('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-semibold text-green-800 shadow-sm flex items-center gap-2">
            <svg class="size-5 text-green-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800 shadow-sm flex items-center gap-2">
            <svg class="size-5 text-red-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @error('save_error')
    <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800 shadow-sm">
        {{ $message }}
    </div>
    @enderror

    @error('update_error')
    <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800 shadow-sm">
        {{ $message }}
    </div>
    @enderror

    {{-- جدول هفته‌ها --}}
    @if (! $selected_month_id)
        <div class="rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center text-sm font-medium text-gray-500 shadow-sm">
            ماهی برای نمایش یافت نشد. لطفاً از صفحه ماه‌ها وارد شوید.
        </div>
    @else
        <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="w-full text-right text-sm">
                <thead class="border-b border-gray-200 bg-gray-100/90 text-xs font-bold text-gray-800">
                <tr>
                    <th class="px-5 py-4">شماره هفته</th>
                    <th class="px-5 py-4">نام هفته</th>
                    <th class="px-5 py-4 text-center">عملیات</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 bg-white">
                @forelse ($this->weeks as $item)
                    <tr wire:key="week-{{ $item->id }}" class="transition hover:bg-amber-50/40">
                        <td class="whitespace-nowrap px-5 py-4 font-semibold text-gray-900">
                                <span class="inline-flex size-7 items-center justify-center rounded-full bg-gray-100 text-xs font-bold text-gray-800 border border-gray-200">
                                    {{ $item->week }}
                                </span>
                        </td>

                        <td class="whitespace-nowrap px-5 py-4 text-base font-extrabold text-gray-900">
                            {{ $item->week_name }}
                        </td>

                        <td class="whitespace-nowrap px-5 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <flux:button
                                    size="sm"
                                    variant="primary"
                                    color="yellow"
                                    wire:click="edit({{ $item->id }})"
                                    class="!cursor-pointer font-bold"
                                >
                                    ویرایش
                                </flux:button>

                                <flux:button
                                    size="sm"
                                    variant="primary"
                                    color="red"
                                    wire:click="deleteForm({{ $item->id }})"
                                    class="!cursor-pointer font-bold"
                                >
                                    حذف
                                </flux:button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-5 py-10 text-center text-sm font-medium text-gray-500">
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

    {{-- مودال افزودن هفته --}}
    <flux:modal name="save-week" class="!bg-white !rounded-xl !border !border-gray-200 !shadow-lg md:!w-[28rem] overflow-hidden p-0">
        <div class="space-y-0 text-gray-900">
            {{-- Header --}}
            <div class="border-b border-gray-200 bg-gray-100/90 px-6 py-4">
                <flux:heading size="lg" class="!font-black !text-zinc-950">
                    افزودن هفته جدید
                </flux:heading>
            </div>

            {{-- Body --}}
            <div class="space-y-4 px-6 py-5 bg-white text-gray-900">
                @if ($this->currentMonth)
                    <div class="flex items-center gap-2 rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-2.5 text-xs font-bold text-indigo-950">
                        <span>ثبت هفته برای ماه: <strong class="font-black text-indigo-700">{{ $this->currentMonth->month_name }}</strong></span>
                    </div>
                @endif

                @error('save_error')
                <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-xs font-bold text-red-700">
                    {{ $message }}
                </div>
                @enderror

                <div class="space-y-1 font-bold text-gray-900">
                    <flux:select
                        wire:model.live="week"
                        label="شماره هفته"
                        placeholder="انتخاب شماره هفته"
                        class="!w-full !text-gray-900 !font-bold"
                    >
                        @foreach ($weekNames as $number => $name)
                            <flux:select.option value="{{ $number }}" class="!text-gray-900 !font-semibold">
                                {{ $number }} - {{ $name }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    @error('week')
                    <p class="text-xs font-bold text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1 font-bold text-gray-900">
                    <flux:input
                        wire:model="week_name"
                        label="نام هفته"
                        placeholder="مثلاً: هفته اول مرداد ۴ روز کاری"
                        class="!text-gray-900 !font-bold"
                    />
                    @error('week_name')
                    <p class="text-xs font-bold text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4">
                <flux:modal.close>
                    <button type="button" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-800 shadow-sm transition hover:bg-gray-100 hover:text-black !cursor-pointer">
                        انصراف
                    </button>
                </flux:modal.close>

                <flux:button
                    wire:click="save"
                    variant="primary"
                    class="!cursor-pointer font-bold px-5"
                >
                    ثبت هفته
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- مودال ویرایش هفته --}}
    <flux:modal name="edit-week" class="!bg-white !rounded-xl !border !border-gray-200 !shadow-lg md:!w-[28rem] overflow-hidden p-0">
        <div class="space-y-0 text-gray-900">
            {{-- Header --}}
            <div class="border-b border-gray-200 bg-gray-100/90 px-6 py-4">
                <flux:heading size="lg" class="!font-black !text-zinc-950">
                    ویرایش هفته
                </flux:heading>
            </div>

            {{-- Body --}}
            <div class="space-y-4 px-6 py-5 bg-white text-gray-900">
                @if ($this->currentMonth)
                    <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 text-xs font-bold text-gray-900">
                        <span>ماه: <strong class="font-black text-gray-950">{{ $this->currentMonth->month_name }}</strong></span>
                    </div>
                @endif

                @error('update_error')
                <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-xs font-bold text-red-700">
                    {{ $message }}
                </div>
                @enderror

                <div class="space-y-1 font-bold text-gray-900">
                    <flux:select
                        wire:model.live="week"
                        label="شماره هفته"
                        placeholder="انتخاب شماره هفته"
                        class="!w-full !text-gray-900 !font-bold"
                    >
                        @foreach ($weekNames as $number => $name)
                            <flux:select.option value="{{ $number }}" class="!text-gray-900 !font-semibold">
                                {{ $number }} - {{ $name }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    @error('week')
                    <p class="text-xs font-bold text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1 font-bold text-gray-900">
                    <flux:input
                        wire:model="week_name"
                        label="نام هفته"
                        placeholder="مثلاً: هفته اول مرداد ۴ روز کاری"
                        class="!text-gray-900 !font-bold"
                    />
                    @error('week_name')
                    <p class="text-xs font-bold text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4">
                <flux:modal.close>
                    <button type="button" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-800 shadow-sm transition hover:bg-gray-100 hover:text-black !cursor-pointer">
                        انصراف
                    </button>
                </flux:modal.close>

                <flux:button
                    wire:click="update"
                    variant="primary"
                    color="yellow"
                    class="!cursor-pointer font-bold px-5"
                >
                    ذخیره تغییرات
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- مودال حذف هفته --}}
    <flux:modal name="delete-week" class="!bg-white !rounded-xl !border !border-gray-200 !shadow-lg md:!w-96 overflow-hidden p-0">
        <div class="space-y-0 text-gray-900">
            {{-- Header --}}
            <div class="border-b border-gray-200 bg-gray-100/90 px-6 py-4">
                <flux:heading size="lg" class="!font-black !text-zinc-950">
                    حذف هفته
                </flux:heading>
            </div>

            {{-- Body --}}
            <div class="px-6 py-5 bg-white">
                <p class="text-sm font-bold text-gray-800 leading-relaxed">
                    آیا از حذف هفته
                    <strong class="font-black text-red-600">«{{ $week_name }}»</strong>
                    اطمینان دارید؟
                </p>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4">
                <flux:modal.close>
                    <button type="button" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-800 shadow-sm transition hover:bg-gray-100 hover:text-black !cursor-pointer">
                        انصراف
                    </button>
                </flux:modal.close>

                <flux:button
                    wire:click="delete"
                    variant="primary"
                    color="red"
                    class="!cursor-pointer font-bold px-5"
                >
                    حذف نهایی
                </flux:button>
            </div>
        </div>
    </flux:modal>

</div>

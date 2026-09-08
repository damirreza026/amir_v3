<div dir="rtl" class="space-y-6">

    {{-- هدر صفحه با بک‌گراند کاملاً سفید و روشن --}}
    <div class="flex flex-col justify-between gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm md:flex-row md:items-center">
        <div>
            <h1 class="text-xl font-black text-gray-900">
                مدیریت ماه‌های سال مالی {{ $year->year }}
            </h1>
            <p class="mt-1 text-sm font-medium text-gray-600">
                مشاهده ماه‌های کاری و دسترسی به مدیریت هفته‌ها
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a
                href="{{ route('year') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-gray-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-800"
            >
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span>برگشت به سال‌ها</span>
            </a>
        </div>
    </div>

    {{-- پیام‌های موفقیت و خطا --}}
    @if (session()->has('success'))
        <div class="rounded-lg border border-green-300 bg-green-50 p-4 text-sm font-semibold text-green-800 shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="rounded-lg border border-red-300 bg-red-50 p-4 text-sm font-semibold text-red-800 shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    @error('update_error')
    <div class="rounded-lg border border-red-300 bg-red-50 p-4 text-sm font-semibold text-red-800 shadow-sm">
        {{ $message }}
    </div>
    @enderror

    {{-- جدول ماه‌ها با کانتینر سفید روشن و فونت خوانا --}}
    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="w-full text-right text-sm">
            <thead class="border-b border-gray-200 bg-gray-100/90 text-xs font-bold text-gray-800">
            <tr>
                <th class="px-5 py-4">شماره ماه</th>
                <th class="px-5 py-4">نام ماه</th>
                <th class="px-5 py-4 text-center">هفته‌ها</th>
                <th class="px-5 py-4 text-center">عملیات</th>
            </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 bg-white">
            @forelse ($this->months as $monthItem)
                <tr wire:key="month-{{ $monthItem->id }}" class="transition hover:bg-amber-50/40">
                    <td class="whitespace-nowrap px-5 py-4 font-semibold text-gray-800">
                            <span class="inline-flex size-7 items-center justify-center rounded-full bg-gray-100 text-xs font-bold text-gray-800">
                                {{ $monthItem->month }}
                            </span>
                    </td>

                    <td class="whitespace-nowrap px-5 py-4 text-base font-extrabold text-gray-900">
                        {{ $monthItem->month_name }}
                    </td>

                    <td class="whitespace-nowrap px-5 py-4 text-center">
                        <a
                            href="{{ URL::signedRoute('week', ['year' => $year->id, 'month' => $monthItem->id]) }}"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-bold text-indigo-700 ring-1 ring-inset ring-indigo-300 transition hover:bg-indigo-600 hover:text-white"
                        >
                            <span>مدیریت هفته‌ها</span>
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                    </td>

                    <td class="whitespace-nowrap px-5 py-4 text-center">
                        <flux:button
                            size="sm"
                            variant="primary"
                            color="yellow"
                            wire:click="edit({{ $monthItem->id }})"
                        >
                            ویرایش
                        </flux:button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-5 py-10 text-center text-sm font-medium text-gray-500">
                        هیچ ماهی برای این سال مالی ثبت نشده است.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- مودال ویرایش ماه با پس‌زمینه سفید و استایل روشن --}}
    <flux:modal name="edit-month" class="bg-white md:w-96">
        <div class="space-y-5 p-2">
            <div>
                <flux:heading size="lg" class="font-bold text-gray-900">
                    ویرایش اطلاعات ماه
                </flux:heading>
                <flux:subheading class="text-xs text-gray-500">
                    تغییر عنوان نمایشی این ماه
                </flux:subheading>
            </div>

            @error('update_error')
            <div class="rounded-lg bg-red-50 p-2 text-xs font-semibold text-red-600">
                {{ $message }}
            </div>
            @enderror

            <div>
                <flux:input
                    wire:model="month_name"
                    label="نام ماه"
                    placeholder="مثلاً فروردین"
                />
                @error('month_name')
                <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-2 pt-3">
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

</div>

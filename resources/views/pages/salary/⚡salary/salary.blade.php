<div dir="rtl" class="space-y-6 p-4 sm:p-6">

    @if (session()->has('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-bold text-green-700 shadow-sm flex items-center gap-2">
            <svg class="size-5 text-green-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @error('salary_error')
    <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-bold text-red-700 shadow-sm flex items-center gap-2">
        <svg class="size-5 text-red-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
        <span>{{ $message }}</span>
    </div>
    @enderror

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <h1 class="text-2xl font-black text-slate-900">
                مدیریت و پرداخت حقوق پرسنل
            </h1>
            <p class="text-xs text-slate-500 mt-1">تعیین نرخ ساعتی، ثبت ساعات کاری هفتگی، صدور فیش و پرداخت نهایی</p>
        </div>

        @if ($this->currentYear)
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-100 font-bold text-slate-700 border border-slate-200 text-sm">
                سال مالی: {{ $this->currentYear->year }}
            </div>
        @endif
    </div>

    {{-- فیلترهای سال و ماه --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

            <div>
                <flux:select wire:model.live="selected_year_id" label="انتخاب سال مالی" icon="calendar">
                    <flux:select.option value="">انتخاب سال</flux:select.option>
                    @foreach ($this->years as $year)
                        <flux:select.option :value="$year->id">{{ $year->year }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div>
                <flux:select wire:model.live="selected_month_id" label="انتخاب ماه" icon="calendar-days" :disabled="! $selected_year_id">
                    <flux:select.option value="">انتخاب ماه</flux:select.option>
                    @foreach ($this->months as $month)
                        <flux:select.option :value="$month->id">{{ $month->month_name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div class="flex items-end">
                <div class="w-full rounded-xl bg-indigo-50 p-3.5 text-sm font-bold text-indigo-700 border border-indigo-100 flex items-center justify-between">
                    <span>تعداد هفته‌های این ماه:</span>
                    <span class="text-base font-black">{{ $this->weeks->count() }} هفته</span>
                </div>
            </div>
        </div>
    </div>

    @if ($selected_year_id && $selected_month_id)

        {{-- جست‌وجوی پرسنل --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <flux:input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="جستجو بر اساس نام یا نام خانوادگی پرسنل..."
                icon="magnifying-glass"
                clearable
            />
        </div>

        {{-- جدول اصلی --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3.5 text-right text-xs font-bold text-slate-700">ردیف</th>
                        <th class="px-4 py-3.5 text-right text-xs font-bold text-slate-700">نام پرسنل</th>

                        @foreach ($this->weeks as $week)
                            <th class="whitespace-nowrap px-4 py-3.5 text-center text-xs font-bold text-slate-700">
                                {{ $week->week_name }}
                            </th>
                        @endforeach

                        <th class="whitespace-nowrap px-4 py-3.5 text-center text-xs font-bold text-slate-700">
                            مجموع ساعات
                        </th>

                        <th class="whitespace-nowrap px-4 py-3.5 text-center text-xs font-bold text-slate-700">
                            حقوق کل ماه
                        </th>

                        <th class="px-4 py-3.5 text-center text-xs font-bold text-slate-700">
                            وضعیت
                        </th>

                        <th class="px-4 py-3.5 text-center text-xs font-bold text-slate-700">
                            عملیات
                        </th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse ($this->currentMonthData as $row)
                        @php
                            $profile = $row['profile'];
                            $isRowPaid = $row['is_paid'];
                            $isRowApproved = $row['is_approved'];
                        @endphp

                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-4 py-4 text-xs font-bold text-slate-500">
                                {{ $loop->iteration }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-sm font-black text-slate-900">
                                {{ trim(($profile->first_name ?? '') . ' ' . ($profile->last_name ?? '')) ?: 'بدون نام' }}
                            </td>

                            @foreach ($row['weeks'] as $week)
                                @php
                                    $weeklySalary = $row['salaries']->get($week->id);
                                    $weeklyHours = $weeklySalary ? (float) $weeklySalary->overtime_hours : null;
                                    $weeklyRate = $weeklySalary ? (float) ($weeklySalary->hourly_rate > 0 ? $weeklySalary->hourly_rate : ($profile->hourly_rate ?? 70000)) : ($profile->hourly_rate ?? 70000);
                                    $weeklyAmount = ! is_null($weeklyHours) ? round($weeklyHours * $weeklyRate) : 0;
                                @endphp

                                <td class="px-3 py-3 text-center">
                                    @if ($weeklySalary)
                                        <button
                                            type="button"
                                            wire:click="openSalaryModal({{ $profile->id }}, {{ $week->id }})"
                                            title="نرخ: {{ number_format($weeklyRate) }} ریال | مبلغ: {{ number_format($weeklyAmount) }} ریال"
                                            class="inline-flex flex-col items-center justify-center rounded-xl p-2 text-xs font-bold transition border {{ $isRowPaid ? 'bg-blue-50 text-blue-800 border-blue-200 hover:bg-blue-100' : ($isRowApproved ? 'bg-emerald-50 text-emerald-800 border-emerald-200 hover:bg-emerald-100' : 'bg-amber-50 text-amber-900 border-amber-200 hover:bg-amber-100') }}"
                                        >
                                            <span class="text-xs">{{ number_format((float) $weeklyHours, 1) }} ساعت</span>
                                            <span class="text-[10px] text-slate-500 mt-0.5">{{ number_format($weeklyAmount) }} ریال</span>
                                        </button>
                                    @else
                                        <button
                                            type="button"
                                            wire:click="openSalaryModal({{ $profile->id }}, {{ $week->id }})"
                                            class="rounded-xl px-3 py-2 text-xs font-bold transition border border-dashed border-slate-300 text-slate-600 hover:bg-slate-100 hover:border-slate-400"
                                        >
                                            + ثبت ساعت
                                        </button>
                                    @endif
                                </td>
                            @endforeach

                            <td class="whitespace-nowrap px-4 py-4 text-center text-xs font-extrabold text-slate-800">
                                <span class="px-2.5 py-1 rounded-full bg-slate-100 border border-slate-200">
                                    {{ number_format((float) $row['total_hours'], 1) }} ساعت
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-center text-sm font-black text-emerald-600">
                                @if ($row['is_complete'])
                                    {{ number_format($row['calculated_salary']) }} ریال
                                @else
                                    <span class="text-slate-400 text-xs font-normal">در حال تکمیل...</span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-center">
                                @if ($row['is_paid'])
                                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700 border border-blue-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> پرداخت شده
                                    </span>
                                @elseif ($row['is_approved'])
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> فیش صادر شده
                                    </span>
                                @elseif ($row['is_complete'])
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> آماده صدور فیش
                                    </span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-600">
                                        {{ $row['completed_weeks'] }} از {{ $row['total_weeks'] }} هفته
                                    </span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-center">
                                @if ($row['is_paid'])
                                    <span class="text-xs font-bold text-slate-400">تکمیل و پرداخت شده</span>
                                @elseif ($row['is_approved'])
                                    <flux:button
                                        size="sm"
                                        variant="primary"
                                        wire:click="markAsPaid({{ $profile->id }})"
                                        class="!cursor-pointer font-bold"
                                    >
                                        پرداخت نهایی
                                    </flux:button>
                                @elseif ($row['is_complete'])
                                    <flux:button
                                        size="sm"
                                        variant="primary"
                                        color="green"
                                        wire:click="issueSalary({{ $profile->id }})"
                                        class="!cursor-pointer font-bold"
                                    >
                                        صدور فیش حقوقی
                                    </flux:button>
                                @else
                                    <span class="text-xs text-slate-400">در انتظار ثبت هفته‌ها</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 6 + $this->weeks->count() }}" class="px-4 py-12 text-center text-sm font-medium text-slate-400">
                                پرسنلی برای نمایش یافت نشد.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center text-sm font-medium text-slate-500">
            لطفاً برای مشاهده وضعیت و پرداخت، سال و ماه را انتخاب کنید.
        </div>
    @endif

    {{-- مودال تعیین نرخ ساعتی و ثبت ساعت کارکرد --}}
    <flux:modal name="salary-modal" class="w-full max-w-lg">
        <div class="space-y-6">
            <div class="border-b pb-3">
                <flux:heading size="lg" class="font-black">ثبت و ویرایش ساعت و نرخ هفتگی</flux:heading>
                <flux:text class="mt-1 text-slate-500">
                    پرسنل: <strong class="text-slate-900">{{ $profile_name ?: '---' }}</strong>
                </flux:text>
            </div>

            @if ($is_record_locked)
                <div class="rounded-xl border border-blue-200 bg-blue-50 p-3.5 text-xs font-bold text-blue-800">
                    این فیش صادر یا پرداخت گردیده و اطلاعات آن قفل شده است.
                </div>
            @endif

            @error('salary_error')
            <div class="rounded-xl border border-red-200 bg-red-50 p-3 text-xs font-bold text-red-700">
                {{ $message }}
            </div>
            @enderror

            <div class="space-y-4">
                <div>
                    <flux:input
                        label="نرخ هر ساعت کارکرد در این هفته (ریال)"
                        type="number"
                        min="0"
                        wire:model.live="modal_hourly_rate"
                        :disabled="$is_record_locked"
                        placeholder="مثال: 70000"
                    />
                    @error('modal_hourly_rate') <p class="text-xs text-red-600 font-bold mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <flux:input
                        label="ساعات کارکرد در این هفته"
                        type="number"                        min="0"
                        step="0.01"
                        wire:model.live="modal_total_hours"
                        :disabled="$is_record_locked"
                        placeholder="مثال: 44.5"
                    />
                    @error('modal_total_hours') <p class="text-xs text-red-600 font-bold mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="rounded-2xl bg-indigo-50 border border-indigo-100 p-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-indigo-700">مبلغ کارکرد این هفته:</span>
                    <strong class="text-lg font-black text-indigo-950">
                        {{ number_format((float) $total_salary) }} ریال
                    </strong>
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t pt-4">
                <flux:modal.close>
                    <flux:button type="button" variant="ghost" class="font-bold">انصراف</flux:button>
                </flux:modal.close>

                @if (! $is_record_locked)
                    <flux:button
                        type="button"
                        variant="primary"
                        wire:click="saveSalary"
                        class="font-bold px-6"
                    >
                        ذخیره اطلاعات هفته
                    </flux:button>
                @endif
            </div>
        </div>
    </flux:modal>
</div>


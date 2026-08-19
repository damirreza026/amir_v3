<div dir="rtl" class="space-y-6 p-4 sm:p-6">

    @if (session()->has('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @error('salary_error')
    <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
        {{ $message }}
    </div>
    @enderror

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                مدیریت حقوق و دستمزد
            </h1>

            @if ($this->currentYear)
                <p class="mt-1 text-sm text-zinc-500">
                    سال {{ $this->currentYear->year }}
                </p>
            @endif
        </div>

        <div class="text-sm text-zinc-500">
            نرخ ساعتی:
            <strong class="text-zinc-900 dark:text-white">
                {{ number_format((float) $hourly_rate) }}
            </strong>
            ریال
        </div>
    </div>

    <div class="rounded-xl border bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">

            <div>
                <label class="mb-2 block text-sm font-medium">سال</label>

                <select
                    wire:model.live="selected_year_id"
                    class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800"
                >
                    <option value="">انتخاب سال</option>

                    @foreach ($this->years as $year)
                        <option value="{{ $year->id }}">
                            {{ $year->year }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">ماه</label>

                <select
                    wire:model.live="selected_month_id"
                    @disabled(! $selected_year_id)
                    class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800"
                >
                    <option value="">انتخاب ماه</option>

                    @foreach ($this->months as $month)
                        <option value="{{ $month->id }}">
                            {{ $month->month_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">نرخ هر ساعت</label>

                <input
                    type="number"
                    min="0"
                    wire:model.live="hourly_rate"
                    class="w-full rounded-lg border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800"
                >
            </div>

            <div class="flex items-end">
                <div class="w-full rounded-lg bg-blue-50 p-3 text-sm text-blue-700">
                    هفته‌های ماه:
                    <strong>{{ $this->weeks->count() }}</strong>
                </div>
            </div>
        </div>
    </div>

    @if ($selected_year_id && $selected_month_id)

        <div class="overflow-hidden rounded-xl border bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-3 text-right text-xs">ردیف</th>
                        <th class="px-4 py-3 text-right text-xs">نام پرسنل</th>

                        @foreach ($this->weeks as $week)
                            <th class="whitespace-nowrap px-4 py-3 text-center text-xs">
                                {{ $week->week_name }}
                            </th>
                        @endforeach

                        <th class="whitespace-nowrap px-4 py-3 text-center text-xs">
                            مجموع ساعات ماه
                        </th>

                        <th class="whitespace-nowrap px-4 py-3 text-center text-xs">
                            حقوق نهایی
                        </th>

                        <th class="px-4 py-3 text-center text-xs">
                            وضعیت
                        </th>

                        <th class="px-4 py-3 text-center text-xs">
                            عملیات
                        </th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($this->currentMonthData as $row)
                        @php
                            $profile = $row['profile'];
                            $isRowPaid = $row['is_paid'];
                        @endphp

                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/60">
                            <td class="px-4 py-3 text-sm">
                                {{ $loop->iteration }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-sm font-medium">
                                {{ trim(($profile->first_name ?? '') . ' ' . ($profile->last_name ?? '')) ?: 'بدون نام' }}
                            </td>

                            @foreach ($row['weeks'] as $week)
                                @php
                                    $weeklySalary = $row['salaries']->get($week->id);
                                    $weeklyHours = $weeklySalary?->overtime_hours;
                                @endphp

                                <td class="px-4 py-3 text-center">
                                    @if ($weeklySalary)
                                        <button
                                            type="button"
                                            wire:click="openSalaryModal({{ $profile->id }}, {{ $week->id }})"
                                            @disabled($isRowPaid)
                                            class="rounded-lg px-3 py-1.5 text-xs font-medium transition
                                                {{ $isRowPaid
                                                    ? 'cursor-not-allowed bg-blue-50 text-blue-400'
                                                    : 'bg-green-100 text-green-700 hover:bg-green-200' }}"
                                        >
                                            {{ number_format((float) $weeklyHours, 2) }}
                                            ساعت
                                        </button>
                                    @else
                                        <button
                                            type="button"
                                            wire:click="openSalaryModal({{ $profile->id }}, {{ $week->id }})"
                                            @disabled($isRowPaid)
                                            class="rounded-lg px-3 py-1.5 text-xs font-medium transition
                                                {{ $isRowPaid
                                                    ? 'cursor-not-allowed bg-blue-50 text-blue-400'
                                                    : 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200' }}"
                                        >
                                            ثبت ساعات
                                        </button>
                                    @endif
                                </td>
                            @endforeach

                            <td class="whitespace-nowrap px-4 py-3 text-center text-sm font-semibold">
                                {{ number_format((float) $row['total_hours'], 2) }}
                                ساعت
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-center text-sm font-semibold">
                                @if ($row['is_complete'])
                                    {{ number_format($row['calculated_salary']) }}
                                    ریال
                                @else
                                    ---
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-center">
                                @if ($row['is_paid'])
                                    <span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs text-blue-700">
                                        پرداخت شده
                                    </span>
                                @elseif ($row['is_approved'])
                                    <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs text-green-700">
                                        فیش صادر شده
                                    </span>
                                @elseif ($row['is_complete'])
                                    <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs text-green-700">
                                        فیش ماهانه آماده است
                                    </span>
                                @else
                                    <span class="rounded-full bg-yellow-100 px-2.5 py-1 text-xs text-yellow-700">
                                        {{ $row['completed_weeks'] }}
                                        از
                                        {{ $row['total_weeks'] }}
                                        هفته تکمیل شده
                                    </span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-center">
                                @if ($row['is_paid'])
                                    <span class="text-xs text-gray-400">
                                        انجام شد
                                    </span>
                                @elseif ($row['is_approved'])
                                    <button
                                        type="button"
                                        wire:click="markAsPaid({{ $profile->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="markAsPaid({{ $profile->id }})"
                                        class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                                    >
                                        <span wire:loading.remove wire:target="markAsPaid({{ $profile->id }})">
                                            پرداخت شد
                                        </span>

                                        <span wire:loading wire:target="markAsPaid({{ $profile->id }})">
                                            در حال پرداخت...
                                        </span>
                                    </button>
                                @elseif ($row['is_complete'])
                                    <button
                                        type="button"
                                        wire:click="issueSalary({{ $profile->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="issueSalary({{ $profile->id }})"
                                        class="rounded-lg bg-green-600 px-3 py-2 text-xs font-medium text-white hover:bg-green-700 disabled:opacity-50"
                                    >
                                        <span wire:loading.remove wire:target="issueSalary({{ $profile->id }})">
                                            صدور فیش
                                        </span>

                                        <span wire:loading wire:target="issueSalary({{ $profile->id }})">
                                            در حال صدور...
                                        </span>
                                    </button>
                                @else
                                    <span class="text-xs text-gray-400">
                                        ---
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="{{ 6 + $this->weeks->count() }}"
                                class="px-4 py-10 text-center text-sm text-zinc-500"
                            >
                                هیچ پرسنلی برای نمایش وجود ندارد.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="rounded-xl border border-dashed p-10 text-center text-sm text-zinc-500">
            ابتدا سال و ماه را انتخاب کنید.
        </div>
    @endif

    <flux:modal name="salary-modal" class="w-full max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    ثبت ساعات هفتگی
                </flux:heading>

                <flux:text class="mt-2">
                    پرسنل:
                    <strong>{{ $profile_name ?: '---' }}</strong>
                </flux:text>
            </div>

            @error('salary_error')
            <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                {{ $message }}
            </div>
            @enderror

            <flux:input
                label="ساعات کارکرد این هفته"
                type="number"
                min="0"
                step="0.01"
                wire:model.live="modal_total_hours"
            />

            @error('modal_total_hours')
            <p class="text-xs text-red-600">{{ $message }}</p>
            @enderror

            <flux:input
                label="نرخ هر ساعت"
                type="number"
                min="0"
                wire:model.live="hourly_rate"
            />

            <div class="rounded-xl bg-blue-50 p-4 dark:bg-blue-900/20">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-blue-700">
                        مبلغ این هفته
                    </span>

                    <strong class="text-lg text-blue-800">
                        {{ number_format((float) $total_salary) }}
                        ریال
                    </strong>
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button type="button" variant="ghost">
                        انصراف
                    </flux:button>
                </flux:modal.close>

                <flux:button
                    type="button"
                    variant="primary"
                    wire:click="saveSalary"
                    wire:loading.attr="disabled"
                    wire:target="saveSalary"
                >
                    <span wire:loading.remove wire:target="saveSalary">
                        ذخیره ساعات
                    </span>

                    <span wire:loading wire:target="saveSalary">
                        در حال ذخیره...
                    </span>
                </flux:button>
            </div>
        </div>
    </flux:modal>
        <a
            href="{{ URL::signedRoute('salary_s_a') }}"
            class="inline-block rounded-lg !bg-blue-600 px-4 py-2 !text-white no-underline transition hover:!bg-blue-700"
            style="background-color: #2563eb !important; color: #ffffff !important;"
        >
            برگشت
        </a>
</div>

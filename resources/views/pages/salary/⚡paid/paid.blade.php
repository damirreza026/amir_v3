<div>
    <div class="mb-6">
        <h1 class="text-xl font-bold">
            فیش‌های حقوقی من
        </h1>

        @if ($this->profile)
            <p class="mt-1 text-sm text-gray-500">
                {{ $this->profile->first_name }}
                {{ $this->profile->last_name }}
            </p>
        @endif
    </div>

    @if (! $this->profile)
        <div class="rounded-xl border border-red-200 bg-red-50 p-5 text-sm text-red-700">
            برای حساب کاربری شما پروفایل پرسنلی ثبت نشده است.
        </div>
    @else
        {{-- فیلتر سال و ماه --}}
        <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-2">
            <flux:select
                wire:model.live="selected_year_id"
                label="سال"
            >
                <flux:select.option value="">
                    همه سال‌ها
                </flux:select.option>

                @foreach ($this->years as $year)
                    <flux:select.option :value="$year->id">
                        سال {{ $year->year }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            <flux:select
                wire:model.live="selected_month_id"
                label="ماه"
                :disabled="! $selected_year_id"
            >
                <flux:select.option value="">
                    همه ماه‌ها
                </flux:select.option>

                @foreach ($this->months as $month)
                    <flux:select.option :value="$month->id">
                        {{ $month->month_name }}
                    </flux:select.option>
                @endforeach
            </flux:select>
        </div>

        {{-- جدول فیش‌ها --}}
        <flux:table :paginate="$this->salaries">
            <flux:table.columns>
                <flux:table.column>سال</flux:table.column>
                <flux:table.column>ماه</flux:table.column>
                <flux:table.column>مجموع ساعات</flux:table.column>
                <flux:table.column>مبلغ فیش</flux:table.column>
                <flux:table.column>تاریخ صدور</flux:table.column>
                <flux:table.column>وضعیت</flux:table.column>
                <flux:table.column>عملیات</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->salaries as $salary)
                    <flux:table.row :key="$salary->id">
                        <flux:table.cell class="whitespace-nowrap">
                            سال {{ $salary->year }}
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap">
                            {{ $salary->month_name }}
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap">
                            <span class="font-bold text-blue-600">
                                {{ number_format((float) $salary->total_hours, 1) }}
                            </span>
                            <span class="text-xs text-gray-500">
                                ساعت
                            </span>
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap">
                            @if ($salary->is_paid || $salary->is_issued)
                                <span class="font-bold text-green-700">
                                    {{ number_format((float) $salary->net_salary) }}
                                </span>
                                <span class="text-xs text-gray-500">
                                    ریال
                                </span>
                            @else
                                <span class="text-sm text-gray-400">---</span>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap text-sm text-gray-500">
                            @if ($salary->issued_at)
                                {{ \Illuminate\Support\Carbon::parse($salary->issued_at)->format('Y/m/d') }}
                            @else
                                ---
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap">
                            @if ($salary->is_paid)
                                <span class="inline-block rounded-md bg-blue-100 px-2 py-1 text-xs text-blue-700">
                                    پرداخت شده
                                </span>
                            @elseif ($salary->is_issued)
                                <span class="inline-block rounded-md bg-green-100 px-2 py-1 text-xs text-green-700">
                                    فیش صادر شده
                                </span>
                            @else
                                <span class="inline-block rounded-md bg-amber-100 px-2 py-1 text-xs text-amber-700">
                                    در انتظار صدور
                                </span>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap text-center">
                            @if ($salary->is_paid || $salary->is_issued)
                                <flux:button
                                    type="button"
                                    variant="primary"
                                    size="sm"
                                    wire:click="openPrintModal({{ $salary->month_id }})"
                                >
                                    <svg class="inline-block size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                                    </svg>
                                    <span class="ms-1">پرینت فیش</span>
                                </flux:button>
                            @else
                                <span class="text-xs text-gray-400">
                                    ---
                                </span>
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell
                            colspan="7"
                            class="py-8 text-center text-gray-500"
                        >
                            هیچ فیش حقوقی برای نمایش وجود ندارد.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        {{-- مودال مشاهده و پرینت فیش --}}
        <flux:modal name="print-salary-modal" class="w-full max-w-3xl">
            <div class="space-y-6">
                @if ($this->printableSalary)
                    <div class="space-y-6 rounded-xl border border-zinc-200 bg-white p-6">
                        {{-- سربرگ فیش --}}
                        <div class="flex flex-col items-center gap-2 border-b border-zinc-200 pb-4 text-center">
                            <h2 class="text-xl font-bold text-zinc-900">
                                فیش حقوقی
                            </h2>

                            <p class="text-sm text-zinc-600">
                                {{ $this->printableSalary->month_name }}
                                سال {{ $this->printableSalary->year }}
                            </p>

                            @if ($this->printableSalary->is_paid)
                                <span class="inline-block rounded-md bg-blue-100 px-2 py-1 text-xs text-blue-700">
                                    پرداخت شده
                                </span>
                            @else
                                <span class="inline-block rounded-md bg-amber-100 px-2 py-1 text-xs text-amber-700">
                                    هنوز پرداخت نشده است
                                </span>
                            @endif
                        </div>

                        {{-- مشخصات پرسنل --}}
                        <div class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                            <div>
                                <span class="text-zinc-500">نام و نام خانوادگی:</span>
                                <strong class="text-zinc-900">
                                    {{ trim(($this->printableSalary->profile->first_name ?? '') . ' ' . ($this->printableSalary->profile->last_name ?? '')) }}
                                </strong>
                            </div>

                            <div>
                                <span class="text-zinc-500">کد پرسنلی:</span>
                                <strong class="text-zinc-900">
                                    {{ $this->printableSalary->profile->id }}
                                </strong>
                            </div>

                            <div>
                                <span class="text-zinc-500">مجموع ساعات کارکرد:</span>
                                <strong class="text-blue-700">
                                    {{ number_format((float) $this->printableSalary->total_hours, 2) }}
                                    ساعت
                                </strong>
                            </div>

                            <div>
                                <span class="text-zinc-500">مبلغ خالص حقوق:</span>
                                <strong class="text-green-700">
                                    {{ number_format((float) $this->printableSalary->net_salary) }}
                                    ریال
                                </strong>
                            </div>
                        </div>

                        {{-- امضای شرکت --}}
                        <div class="mt-6 flex items-end justify-between border-t border-zinc-200 pt-4">
                            <div class="text-center">
                                <p class="text-sm text-zinc-500">مهر و امضای شرکت</p>
                                <div class="mt-10 h-12 w-40 border-b border-zinc-400"></div>
                            </div>

                            <div class="text-center text-xs text-zinc-500">
                                تاریخ صدور:
                                @if ($this->printableSalary->issued_at)
                                    {{ \Illuminate\Support\Carbon::parse($this->printableSalary->issued_at)->format('Y/m/d') }}
                                @else
                                    ---
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <p class="text-center text-sm text-zinc-500">
                        فیشی برای نمایش وجود ندارد.
                    </p>
                @endif

                {{-- دکمه‌های پایین مودال --}}
                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <flux:modal.close>
                        <flux:button type="button" variant="ghost">
                            بستن
                        </flux:button>
                    </flux:modal.close>

                    <flux:button
                        type="button"
                        variant="primary"
                        onclick="window.print()"
                    >
                        <svg class="inline-block size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                        </svg>
                        <span class="ms-1">پرینت فیش</span>
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif
</div>

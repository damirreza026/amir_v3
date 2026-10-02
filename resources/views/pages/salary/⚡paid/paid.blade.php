<div class="max-w-7xl mx-auto p-4 md:p-8 space-y-8" dir="rtl">

    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 print:hidden">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">
                مدیریت حقوق و دستمزد
            </h1>
            <p class="text-slate-500 mt-1 text-sm">
                مشاهده گزارش کارکرد، وضعیت فیش‌ها و دریافت نسخه چاپی
            </p>
        </div>

        @if ($this->profile)
            <div class="flex items-center gap-3 bg-white p-2 pr-4 pl-4 rounded-2xl shadow-sm border border-slate-200">
                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-sm">
                    {{ mb_substr($this->profile->first_name ?? '', 0, 1) }}
                    {{ mb_substr($this->profile->last_name ?? '', 0, 1) }}
                </div>
                <div>
                    <p class="text-xs text-slate-400 leading-none">
                        پرسنل
                    </p>
                    <p class="text-sm font-black text-slate-800 mt-0.5">
                        {{ $this->profile->first_name }} {{ $this->profile->last_name }}
                    </p>
                </div>
            </div>
        @endif
    </div>

    {{-- Filters Section --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-white p-6 rounded-3xl border border-slate-200 shadow-sm print:hidden">
        <flux:select
            wire:model.live="selected_year_id"
            label="انتخاب سال مالی"
            icon="calendar"
        >
            <flux:select.option value="">
                همه سال‌ها
            </flux:select.option>
            @foreach ($this->years as $year)
                <flux:select.option :value="$year->id">
                    {{ $year->year }}
                </flux:select.option>
            @endforeach
        </flux:select>

        <flux:select
            wire:model.live="selected_month_id"
            label="انتخاب ماه"
            icon="calendar-days"
            :disabled="! $selected_year_id"
        >
            <flux:select.option value="">
                همه ماه‌ها
            </flux:select.option>
            @foreach ($this->months as $m)
                <flux:select.option :value="$m->id">
                    {{ $m->month_name }}
                </flux:select.option>
            @endforeach
        </flux:select>
    </div>

    {{-- Table Section --}}
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden print:hidden">
        <flux:table :paginate="$this->salaries">
            <flux:table.columns>
                <flux:table.column class="text-right font-bold">
                    بازه زمانی
                </flux:table.column>
                <flux:table.column class="text-center font-bold">
                    مجموع ساعت
                </flux:table.column>
                <flux:table.column class="text-center font-bold">
                    مبلغ کارکرد
                </flux:table.column>
                <flux:table.column class="text-center font-bold">
                    وضعیت فیش
                </flux:table.column>
                <flux:table.column class="text-left font-bold">
                    عملیات
                </flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->salaries as $item)
                    <flux:table.row :key="$item->month_id" class="hover:bg-slate-50 transition-colors">

                        {{-- Month & Year --}}
                        <flux:table.cell>
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-800">
                                    {{ $item->month_name }}
                                </span>
                                <span class="text-xs text-slate-400">
                                    سال مالی {{ $item->year }}
                                </span>
                            </div>
                        </flux:table.cell>

                        {{-- Total Hours --}}
                        <flux:table.cell class="text-center">
                            <span class="px-3 py-1 bg-indigo-50 text-indigo-700 border border-indigo-100 rounded-full text-xs font-bold">
                                {{ number_format($item->total_hours, 1) }} ساعت
                            </span>
                        </flux:table.cell>

                        {{-- Net Salary --}}
                        <flux:table.cell class="text-center font-bold text-emerald-600">
                            {{ number_format($item->net_salary) }}
                            <span class="text-xs text-slate-400 font-normal">ریال</span>
                        </flux:table.cell>

                        {{-- Status --}}
                        <flux:table.cell class="text-center">
                            @if ($item->status === 'paid')
                                <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    پرداخت شده
                                </span>
                            @elseif ($item->status === 'issued')
                                <span class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-700 bg-blue-50 border border-blue-200 px-3 py-1 rounded-full">
                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                    فیش صادر شده
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-700 bg-amber-50 border border-amber-200 px-3 py-1 rounded-full">
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                    در جریان ثبت
                                </span>
                            @endif
                        </flux:table.cell>

                        {{-- Actions --}}
                        <flux:table.cell class="text-left">
                            <div class="flex justify-end gap-2">
                                <flux:button
                                    size="sm"
                                    variant="subtle"
                                    icon="chart-bar"
                                    title="ریز کارکرد هفتگی"
                                    wire:click="openPerformanceModal({{ $item->month_id }})"
                                />

                                @if ($item->status === 'paid' || $item->status === 'issued')
                                    <flux:button
                                        size="sm"
                                        variant="subtle"
                                        icon="printer"
                                        title="چاپ فیش حقوقی"
                                        wire:click="openPrintModal({{ $item->month_id }})"
                                    />
                                @endif
                            </div>
                        </flux:table.cell>

                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center py-12 text-slate-400 text-sm font-medium">
                            هیچ اطلاعات حقوق و دستمزدی برای این بازه یافت نشد.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    {{-- Performance Detail Modal --}}
    <flux:modal name="performance-detail-modal" class="w-full max-w-2xl">
        @if ($this->weeklyDetails)
            <div class="space-y-6">
                <div class="flex justify-between items-center border-b pb-4">
                    <div>
                        <flux:heading size="lg" class="font-black">
                            ریز کارکرد ماه {{ $this->weeklyDetails->month_name }}
                        </flux:heading>
                        <flux:text class="text-xs text-slate-500 mt-0.5">
                            تفکیک ساعات، نرخ ساعتی و کارکرد هر هفته
                        </flux:text>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-indigo-50 border border-indigo-100 p-4 rounded-2xl">
                        <p class="text-xs font-bold text-indigo-600 mb-1">
                            مجموع ساعات کارکرد
                        </p>
                        <p class="text-2xl font-black text-indigo-950">
                            {{ number_format($this->weeklyDetails->total_hours, 1) }}
                            <span class="text-sm font-normal">ساعت</span>
                        </p>
                    </div>

                    <div class="bg-emerald-50 border border-emerald-100 p-4 rounded-2xl">
                        <p class="text-xs font-bold text-emerald-600 mb-1">
                            مجموع کارکرد ماه
                        </p>
                        <p class="text-2xl font-black text-emerald-950">
                            {{ number_format($this->weeklyDetails->total_amount) }}
                            <span class="text-sm font-normal">ریال</span>
                        </p>
                    </div>
                </div>

                <div class="space-y-3">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        عملکرد به تفکیک هفته‌ها
                    </h3>

                    @forelse ($this->weeklyDetails->weeks as $w)
                        <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-800 text-sm">
                                    {{ $w->week_name }}
                                </span>
                                <span class="text-xs text-slate-500 mt-1">
                                    نرخ هر ساعت: {{ number_format($w->rate) }} ریال
                                </span>
                            </div>

                            <div class="text-left">
                                <div class="text-sm font-black text-slate-800">
                                    {{ number_format($w->hours, 1) }} ساعت
                                </div>
                                <div class="text-xs text-emerald-600 font-bold mt-1">
                                    {{ number_format($w->amount) }} ریال
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-xs text-slate-400 py-4">
                            هفته‌ای ثبت نشده است.
                        </p>
                    @endforelse
                </div>

                <div class="flex justify-end pt-2 border-t">
                    <flux:modal.close>
                        <flux:button variant="ghost" class="font-bold">
                            بستن
                        </flux:button>
                    </flux:modal.close>
                </div>
            </div>
        @endif
    </flux:modal>

    {{-- Print Salary Modal --}}
    <flux:modal name="print-salary-modal" class="w-full max-w-2xl">
        @if ($this->printableSalary)

            {{-- Print Styles --}}


            <div class="space-y-6">

                {{-- Printable Area --}}
                <div
                    id="printable-salary-area"
                    class="bg-white rounded-2xl border border-slate-300 overflow-hidden text-slate-900"
                    dir="rtl"
                >

                    {{-- Letterhead --}}
                    <div class="relative border-b-2 border-slate-800 px-8 py-5 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-slate-900 text-white flex items-center justify-center text-lg font-black">
                                ف
                            </div>
                            <div>
                                <p class="text-lg font-black tracking-tight">
                                    فیش حقوق و دستمزد
                                </p>
                                <p class="text-[11px] text-slate-500 mt-0.5">
                                    سامانه مدیریت منابع انسانی و حقوق و دستمزد
                                </p>
                            </div>
                        </div>

                        <div class="text-left">
                            <p class="text-[10px] text-slate-400">شماره سند</p>
                            <p class="text-sm font-black font-mono">
                                {{ str_pad($this->print_month_id ?? 0, 5, '0', STR_PAD_LEFT) }}
                            </p>
                        </div>
                    </div>


                    {{-- Employee Info Grid --}}
                    <div class="px-8 py-5">
                        <div class="grid grid-cols-2 gap-x-8 gap-y-4 text-sm">

                            <div class="flex justify-between border-b border-dashed border-slate-200 pb-2">
                                <span class="text-slate-500">نام و نام خانوادگی:</span>
                                <strong class="text-slate-900">
                                    {{ $this->printableSalary->profile->first_name ?? '' }}
                                    {{ $this->printableSalary->profile->last_name ?? '' }}
                                </strong>
                            </div>

                            <div class="flex justify-between border-b border-dashed border-slate-200 pb-2">
                                <span class="text-slate-500">کد پرسنلی:</span>
                                <strong class="text-slate-900 font-mono">
                                    {{ auth()->id() }}
                                </strong>
                            </div>

                            <div class="flex justify-between border-b border-dashed border-slate-200 pb-2">
                                <span class="text-slate-500">کد ملی:</span>
                                <strong class="text-slate-900 font-mono tracking-widest">
                                    {{ $this->printableSalary->profile->national_code ?? '---' }}
                                </strong>
                            </div>

                            <div class="flex justify-between border-b border-dashed border-slate-200 pb-2">
                                <span class="text-slate-500">مجموع کارکرد:</span>
                                <strong class="text-slate-900">
                                    {{ number_format($this->printableSalary->total_hours, 1) }}
                                    ساعت
                                </strong>
                            </div>

                            <div class="flex justify-between border-b border-dashed border-slate-200 pb-2">
                                <span class="text-slate-500">وضعیت پرداخت:</span>
                                <span class="font-bold text-slate-900">
                                    {{ $this->printableSalary->status }}
                                </span>
                            </div>

                            <div class="flex justify-between border-b border-dashed border-slate-200 pb-2">
                                <span class="text-slate-500">واحد پرداخت:</span>
                                <span class="font-bold text-slate-900">ریال</span>
                            </div>

                        </div>


                        {{-- Net Salary Highlight --}}
                        <div class="mt-6 flex items-center justify-between bg-slate-900 text-white px-5 py-4 rounded-xl">
                            <span class="text-sm font-bold tracking-wide">
                                خالص پرداختی
                            </span>
                            <span class="text-2xl font-black font-mono">
                                {{ number_format($this->printableSalary->net_salary) }}
                                <span class="text-xs font-normal opacity-70">ریال</span>
                            </span>
                        </div>


                        {{-- Amount In Words Note --}}
                        <p class="mt-3 text-[11px] text-slate-400 text-left" dir="ltr">
                            This payslip is generated electronically by the payroll system.
                        </p>
                    </div>


                    {{-- Signatures --}}
                    <div class="border-t-2 border-slate-800 px-8 py-6 grid grid-cols-3 gap-6 text-center text-sm">

                        <div>
                            <p class="font-bold text-slate-700 mb-8">امضای کارمند</p>
                            <div class="border-t border-slate-400 mx-4 pt-1">
                                <span class="text-[10px] text-slate-400">نام و امضا</span>
                            </div>
                        </div>

                        <div>
                            <p class="font-bold text-slate-700 mb-8">امضای مدیر واحد</p>
                            <div class="border-t border-slate-400 mx-4 pt-1">
                                <span class="text-[10px] text-slate-400">تأیید کارکرد</span>
                            </div>
                        </div>

                        <div>
                            <p class="font-bold text-slate-700 mb-8">مهر و امضای شرکت</p>
                            <div class="border-t border-slate-400 mx-4 pt-1">
                                <span class="text-[10px] text-slate-400">مالی / منابع انسانی</span>
                            </div>
                        </div>

                    </div>


                    {{-- Footer --}}
                    <div class="bg-slate-50 border-t border-slate-200 px-8 py-2.5 flex justify-between text-[10px] text-slate-400">
                        <span>این فیش به‌صورت سیستمی صادر شده است</span>
                        <span>نسخه قابل چاپ</span>
                    </div>

                </div>


                {{-- Modal Actions --}}
                <div class="flex gap-2 justify-end border-t pt-4 print:hidden">
                    <flux:modal.close>
                        <flux:button variant="ghost" class="font-bold">
                            انصراف
                        </flux:button>
                    </flux:modal.close>

                    <flux:button
                        variant="primary"
                        icon="printer"
                        class="font-bold px-6"
                        onclick="window.print()"
                    >
                        چاپ فیش حقوقی
                    </flux:button>
                </div>

            </div>

        @endif
    </flux:modal>

</div>

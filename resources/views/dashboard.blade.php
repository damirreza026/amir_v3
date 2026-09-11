<x-layouts::app :title="__('Dashboard')">

    @php
        $user = auth()->user();
        $profile = $user?->profile;
        $roleId = (int) ($profile?->role_id ?? 0);

        // ترکیب نام و نام خانوادگی از پروفایل، یا نام پیش‌فرض کاربر
        $firstName = $profile?->first_name ?? '';
        $lastName = $profile?->last_name ?? '';
        $fullName = trim("{$firstName} {$lastName}") ?: ($user?->name ?? 'کاربر محترم');

        $roles = [
            1 => 'سوپر ادمین',
            2 => 'ادمین',
            3 => 'انباردار',
            4 => 'فروشنده',
            5 => 'حسابدار',
        ];

        $roleName = $roles[$roleId] ?? 'کاربر';

        $roleBadge = match ($roleId) {
            1 => 'bg-purple-500/10 text-purple-400 ring-purple-500/30',
            2 => 'bg-sky-500/10 text-sky-400 ring-sky-500/30',
            3 => 'bg-amber-500/10 text-amber-400 ring-amber-500/30',
            4 => 'bg-emerald-500/10 text-emerald-400 ring-emerald-500/30',
            5 => 'bg-rose-500/10 text-rose-400 ring-rose-500/30',
            default => 'bg-zinc-500/10 text-zinc-400 ring-zinc-500/30',
        };

        // دسترسی سریع بر اساس نقش
        $quickLinks = [
            ['label' => 'فیش‌های حقوقی من', 'icon' => 'document-text', 'route' => 'my_salary', 'roles' => [1, 2, 3, 4, 5], 'color' => 'from-indigo-500 to-blue-600'],
            ['label' => 'مدیریت کارمندان', 'icon' => 'user-group', 'route' => 'PersonnelManagement_s_a', 'roles' => [1, 2], 'color' => 'from-emerald-500 to-green-600'],
            ['label' => 'مدیریت محصولات', 'icon' => 'inbox', 'route' => 'ProductHandel', 'roles' => [1, 2, 3], 'color' => 'from-amber-500 to-orange-600'],
            ['label' => 'مدیریت مشتریان', 'icon' => 'cube', 'route' => 'customer_s_a', 'roles' => [1, 2, 3, 4, 5], 'color' => 'from-violet-500 to-purple-600'],
            ['label' => 'فروش / صدور فاکتور', 'icon' => 'shopping-cart', 'route' => 'sale_s_a', 'roles' => [1, 2, 3, 4, 5], 'color' => 'from-rose-500 to-pink-600'],
            ['label' => 'گزارشات و فاکتورها', 'icon' => 'chart-bar', 'route' => 'invoice_s_a', 'roles' => [1, 2, 5], 'color' => 'from-cyan-500 to-teal-600'],
            ['label' => 'پرداخت حقوق', 'icon' => 'currency-dollar', 'route' => 'salary_s_a', 'roles' => [1, 2, 5], 'color' => 'from-lime-500 to-green-600'],
            ['label' => 'سال و ماه و هفته کاری', 'icon' => 'calendar', 'route' => 'year_s_a', 'roles' => [1, 2, 5], 'color' => 'from-orange-500 to-amber-600'],
        ];

        $myLinks = collect($quickLinks)->filter(fn ($link) => in_array($roleId, $link['roles'], true));

        // تبدیل تاریخ‌ها به شمسی با تایم‌زون تهران (سازگار با Carbon)
        $todayJalali = \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::now('Asia/Tehran'))->format('%A %d %B %Y');

        $createdAtJalali = $user?->created_at
            ? \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($user->created_at)->setTimezone('Asia/Tehran'))->format('%d %B %Y')
            : '—';

        $lastUpdated = $profile?->last_modified_at ?? $profile?->updated_at ?? $user?->updated_at;
        $updatedAtJalali = $lastUpdated
            ? \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($lastUpdated)->setTimezone('Asia/Tehran'))->format('%d %B %Y')
            : '—';
    @endphp

    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">

        {{-- ─── بنر خوش‌آمد ─── --}}
        <div class="relative overflow-hidden rounded-2xl bg-linear-to-l from-indigo-600 via-violet-600 to-fuchsia-600 p-8 text-white shadow-xl shadow-indigo-500/20">
            <div class="pointer-events-none absolute -right-16 -top-16 size-52 rounded-full bg-white/10 blur-2xl"></div>
            <div class="pointer-events-none absolute -bottom-24 left-1/4 size-72 rounded-full bg-fuchsia-300/20 blur-3xl"></div>

            <div class="relative z-10 flex flex-col gap-6 md:flex-row md:items-center md:justify-between">

                <div>
                    <p class="text-sm font-medium text-indigo-100">سلام 👋</p>
                    <h1 class="mt-2 text-2xl font-extrabold leading-snug md:text-3xl">
                        {{ $fullName }} عزیز، خوش آمدید به داشبورد خود
                    </h1>
                    <p class="mt-3 max-w-xl text-sm leading-relaxed text-indigo-100/90">
                        امروز
                        <span class="font-bold text-white">{{ $todayJalali }}</span>
                        است. امیدواریم روزی پر از موفقیت داشته باشید.
                    </p>

                    <span class="mt-5 inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-1.5 text-xs font-bold ring-1 ring-white/25 backdrop-blur">
                        <flux:icon name="shield-check" variant="solid" class="size-4" />
                        {{ $roleName }}
                    </span>
                </div>

                {{-- آواتار و مشخصات خلاصه --}}
                <div class="flex items-center gap-4 rounded-2xl bg-white/10 p-4 ring-1 ring-white/20 backdrop-blur">
                    <div class="flex size-16 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-2xl font-black text-white ring-2 ring-white/30">
                        {{ method_exists($user, 'initials') ? $user->initials() : mb_substr($fullName, 0, 2) }}
                    </div>
                    <div class="grid">
                        <span class="text-base font-bold">{{ $fullName }}</span>
                        <span class="mt-1 flex items-center gap-1.5 text-xs text-indigo-100">
                            <flux:icon name="envelope" class="size-3.5" />
                            {{ $user?->email }}
                        </span>
                        @if (! empty($profile?->phone))
                            <span class="mt-1 flex items-center gap-1.5 text-xs text-indigo-100" dir="ltr">
                                <flux:icon name="phone" class="size-3.5" />
                                {{ $profile->phone }}
                            </span>
                        @endif
                    </div>
                </div>

            </div>
        </div>

        {{-- ─── اطلاعات کامل کاربر ─── --}}
        <div class="grid gap-4 lg:grid-cols-3">

            {{-- کارت اصلی اطلاعات --}}
            <div class="rounded-2xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900 lg:col-span-2">
                <div class="flex items-center gap-3">
                    <span class="flex size-10 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-500 dark:text-indigo-400">
                        <flux:icon name="user-circle" variant="solid" class="size-5" />
                    </span>
                    <h2 class="text-base font-bold text-neutral-900 dark:text-white">اطلاعات حساب کاربری</h2>
                </div>

                <dl class="mt-6 grid gap-4 sm:grid-cols-2">

                    <div class="rounded-xl bg-neutral-50 p-4 dark:bg-zinc-800/60">
                        <dt class="text-xs text-neutral-500 dark:text-zinc-400">نام و نام خانوادگی</dt>
                        <dd class="mt-1.5 text-sm font-bold text-neutral-900 dark:text-white">{{ $fullName }}</dd>
                    </div>

                    <div class="rounded-xl bg-neutral-50 p-4 dark:bg-zinc-800/60">
                        <dt class="text-xs text-neutral-500 dark:text-zinc-400">ایمیل</dt>
                        <dd class="mt-1.5 break-all text-sm font-bold text-neutral-900 dark:text-white">{{ $user?->email }}</dd>
                    </div>

                    <div class="rounded-xl bg-neutral-50 p-4 dark:bg-zinc-800/60">
                        <dt class="text-xs text-neutral-500 dark:text-zinc-400">نقش کاربری</dt>
                        <dd class="mt-1.5">
                            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $roleBadge }}">
                                <flux:icon name="shield-check" variant="solid" class="size-3.5" />
                                {{ $roleName }}
                            </span>
                        </dd>
                    </div>

                    @if (! empty($profile?->phone))
                        <div class="rounded-xl bg-neutral-50 p-4 dark:bg-zinc-800/60">
                            <dt class="text-xs text-neutral-500 dark:text-zinc-400">تلفن / موبایل</dt>
                            <dd class="mt-1.5 text-sm font-bold text-neutral-900 dark:text-white" dir="ltr">{{ $profile->phone }}</dd>
                        </div>
                    @endif

                    @if (! empty($profile?->national_code))
                        <div class="rounded-xl bg-neutral-50 p-4 dark:bg-zinc-800/60">
                            <dt class="text-xs text-neutral-500 dark:text-zinc-400">کد ملی</dt>
                            <dd class="mt-1.5 text-sm font-bold text-neutral-900 dark:text-white" dir="ltr">{{ $profile->national_code }}</dd>
                        </div>
                    @endif

                    @if (! empty($profile?->address))
                        <div class="rounded-xl bg-neutral-50 p-4 dark:bg-zinc-800/60 sm:col-span-2">
                            <dt class="text-xs text-neutral-500 dark:text-zinc-400">آدرس</dt>
                            <dd class="mt-1.5 text-sm font-bold leading-relaxed text-neutral-900 dark:text-white">{{ $profile->address }}</dd>
                        </div>
                    @endif

                </dl>
            </div>

            {{-- کارت تاریخچه و وضعیت --}}
            <div class="flex flex-col gap-4">

                <div class="rounded-2xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900">
                    <div class="flex items-center gap-3">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-500 dark:text-emerald-400">
                            <flux:icon name="calendar-days" variant="solid" class="size-5" />
                        </span>
                        <h2 class="text-base font-bold text-neutral-900 dark:text-white">وضعیت حساب</h2>
                    </div>

                    <ul class="mt-6 space-y-3 text-sm">
                        <li class="flex items-center justify-between rounded-xl bg-neutral-50 px-4 py-3 dark:bg-zinc-800/60">
                            <span class="text-neutral-500 dark:text-zinc-400">وضعیت عضویت</span>
                            <span class="inline-flex items-center gap-1.5 font-bold text-emerald-500">
                                <span class="size-2 rounded-full bg-emerald-500"></span>
                                فعال
                            </span>
                        </li>
                        <li class="flex items-center justify-between rounded-xl bg-neutral-50 px-4 py-3 dark:bg-zinc-800/60">
                            <span class="text-neutral-500 dark:text-zinc-400">تاریخ عضویت</span>
                            <span class="font-bold text-neutral-900 dark:text-white">
                                {{ $createdAtJalali }}
                            </span>
                        </li>
                        <li class="flex items-center justify-between rounded-xl bg-neutral-50 px-4 py-3 dark:bg-zinc-800/60">
                            <span class="text-neutral-500 dark:text-zinc-400">آخرین به‌روزرسانی</span>
                            <span class="font-bold text-neutral-900 dark:text-white">
                                {{ $updatedAtJalali }}
                            </span>
                        </li>
                    </ul>
                </div>

            </div>
        </div>

        {{-- ─── دسترسی سریع بر اساس نقش ─── --}}
        @if ($myLinks->isNotEmpty())
            <div class="rounded-2xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-zinc-900">
                <div class="flex items-center gap-3">
                    <span class="flex size-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-500 dark:text-amber-400">
                        <flux:icon name="bolt" variant="solid" class="size-5" />
                    </span>
                    <h2 class="text-base font-bold text-neutral-900 dark:text-white">دسترسی سریع</h2>
                </div>

                <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($myLinks as $link)
                        <a href="{{ route($link['route']) }}"
                           class="group flex items-center gap-3 rounded-xl border border-neutral-200 bg-neutral-50 p-4 transition-all hover:-translate-y-0.5 hover:shadow-lg dark:border-neutral-700 dark:bg-zinc-800/60">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br {{ $link['color'] }} text-white shadow-md">
                                <flux:icon :name="$link['icon']" variant="solid" class="size-5" />
                            </span>
                            <span class="text-sm font-bold text-neutral-800 transition-colors group-hover:text-indigo-600 dark:text-zinc-100 dark:group-hover:text-indigo-400">
                                {{ $link['label'] }}
                            </span>
                            <flux:icon name="chevron-left" class="mr-auto size-4 text-neutral-400 transition-transform group-hover:-translate-x-1" />
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</x-layouts::app>

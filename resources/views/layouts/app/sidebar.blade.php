<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark" dir="rtl">
<head>
    @include('partials.head')

    <style>
        /* ─── پس‌زمینه ملایم و استاندارد دارک مد ─── */
        .sidebar-soft {
            background: linear-gradient(180deg, #18181b 0%, #09090b 100%) !important;
        }

        /* ─── اسکرول‌بار نرم و باریک ─── */
        .sidebar-soft nav {
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, .1) transparent;
        }
        .sidebar-soft nav::-webkit-scrollbar { width: 4px; }
        .sidebar-soft nav::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, .1);
            border-radius: 999px;
        }

        /* ─── آیتم‌های منو ─── */
        .sidebar-soft .side-item {
            border-radius: 10px;
            color: #a1a1aa !important; /* خاکستری ملایم خوانا */
            transition: all 0.2s ease;
        }
        .sidebar-soft .side-item:hover {
            background: rgba(255, 255, 255, 0.05);
            color: #f4f4f5 !important;
            transform: translateX(-2px);
        }

        /* ─── آیتم فعال (با رنگ‌های ملایم و پاستلی) ─── */
        .sidebar-soft .side-item[aria-current="page"],
        .sidebar-soft .side-item[data-current="true"] {
            background-color: var(--active-bg);
            color: var(--active-text) !important;
            font-weight: 500;
        }
    </style>
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800">
<flux:sidebar sticky collapsible="mobile" class="sidebar-soft border-e border-zinc-800">
    <flux:sidebar.header>
        <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
        <flux:sidebar.collapse class="lg:hidden" />
    </flux:sidebar.header>

    @auth
        @php
            $user = auth()->user();
            $profile = $user->profile;
            $roleId = $profile?->role_id ?? 0;
        @endphp

        <flux:sidebar.nav class="sidebar-soft">
            <flux:sidebar.group heading="منوی اصلی" class="grid gap-1">

                {{-- داشبورد — آبی ملایم --}}
                <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate
                                   class="side-item" style="--active-bg: rgba(56, 189, 248, 0.12); --active-text: #38bdf8;">
                    داشبورد
                </flux:sidebar.item>

                {{-- مدیریت کارمندان — سبز ملایم --}}
                @if (in_array($roleId, [1, 2]))
                    <flux:sidebar.item icon="user-group" :href="route('PersonnelManagement_s_a')" :current="request()->routeIs('PersonnelManagement_s_a')"
                                       class="side-item" style="--active-bg: rgba(52, 211, 153, 0.12); --active-text: #34d399;">
                        مدیریت کارمندان
                    </flux:sidebar.item>
                @endif

                {{-- مدیریت محصولات — کهربایی ملایم --}}
                @if (in_array($roleId, [1, 2, 3]))
                    <flux:sidebar.item icon="inbox" :href="route('ProductHandel')" :current="request()->routeIs('ProductHandel')"
                                       class="side-item" style="--active-bg: rgba(251, 191, 36, 0.12); --active-text: #fbbf24;">
                        مدیریت محصولات
                    </flux:sidebar.item>
                @endif

                {{-- مدیریت مشتریان — بنفش ملایم --}}
                @if (in_array($roleId, [1, 2, 3, 4, 5]))
                    <flux:sidebar.item icon="cube" :href="route('customer_s_a')" :current="request()->routeIs('customer_s_a')"
                                       class="side-item" style="--active-bg: rgba(167, 139, 250, 0.12); --active-text: #a78bfa;">
                        مدیریت مشتریان
                    </flux:sidebar.item>
                @endif

                {{-- فروش / صدور فاکتور — صورتی/رز ملایم --}}
                @if (in_array($roleId, [1, 2, 3, 4, 5]))
                    <flux:sidebar.item icon="shopping-cart" :href="route('sale_s_a')" :current="request()->routeIs('sale_s_a')"
                                       class="side-item" style="--active-bg: rgba(244, 63, 94, 0.12); --active-text: #fb7185;">
                        فروش محصولات / صدور فاکتور
                    </flux:sidebar.item>
                @endif

                {{-- گزارشات و فاکتورها — فیروزه‌ای ملایم --}}
                @if (in_array($roleId, [1, 2, 5]))
                    <flux:sidebar.item icon="chart-bar" :href="route('invoice_s_a')" :current="request()->routeIs('invoice_s_a')"
                                       class="side-item" style="--active-bg: rgba(45, 212, 191, 0.12); --active-text: #2dd4bf;">
                        گزارشات و فاکتورها
                    </flux:sidebar.item>
                @endif

                {{-- پرداخت حقوق — سبز تیره ملایم --}}
                @if (in_array($roleId, [1, 2, 5]))
                    <flux:sidebar.item icon="currency-dollar" :href="route('salary_s_a')" :current="request()->routeIs('salary_s_a')"
                                       class="side-item" style="--active-bg: rgba(74, 222, 128, 0.12); --active-text: #4ade80;">
                        پرداخت حقوق
                    </flux:sidebar.item>
                @endif

                {{-- افزودن سال و ماه و هفته — نارنجی ملایم --}}
                @if (in_array($roleId, [1, 2, 5]))
                    <flux:sidebar.item icon="calendar" :href="route('year_s_a')" :current="request()->routeIs('year_s_a')"
                                       class="side-item" style="--active-bg: rgba(251, 146, 60, 0.12); --active-text: #fb923c;">
                        افزودن سال و ماه و هفته کاری
                    </flux:sidebar.item>
                @endif

                {{-- مدیریت حقوق و دستمزد — سرخابی ملایم --}}
                @if ($roleId === 5)
                    <flux:sidebar.item icon="banknotes" :href="route('salary_s_a')" :current="request()->routeIs('salary_s_a')"
                                       class="side-item" style="--active-bg: rgba(232, 121, 249, 0.12); --active-text: #e879f9;">
                        مدیریت حقوق و دستمزد
                    </flux:sidebar.item>
                @endif

                {{-- فیش‌های حقوقی من — نیلی ملایم --}}
                @if (true)
                    <flux:sidebar.item icon="document-text" :href="route('my_salary')" :current="request()->routeIs('my_salary')"
                                       class="side-item" style="--active-bg: rgba(129, 140, 248, 0.12); --active-text: #818cf8;">
                        فیش‌های حقوقی من
                    </flux:sidebar.item>
                @endif

            </flux:sidebar.group>
        </flux:sidebar.nav>

        @if ($roleId === 0)
            <div class="mx-3 mt-2 flex items-center gap-2 rounded-xl border border-red-500/20 bg-red-500/10 px-3 py-2 text-sm text-red-400">
                <flux:icon name="exclamation-triangle" variant="solid" class="size-4 shrink-0" />
                <span>نقشی برای این کاربر یافت نشد.</span>
            </div>
        @endif
    @endauth

    <flux:spacer />

    {{-- فوتر تمیز --}}
    <div class="mx-3 mb-2 rounded-lg bg-zinc-900/60 px-3 py-2 text-center text-[11px] text-zinc-500">
        سیستم مدیریت جامع
    </div>

    <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
</flux:sidebar>

<!-- Mobile User Menu -->
<flux:header class="lg:hidden">
    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

    <flux:spacer />

    <flux:dropdown position="top" align="end">
        <flux:profile
            :initials="auth()->user()->initials()"
            icon-trailing="chevron-down"
        />

        <flux:menu>
            <flux:menu.radio.group>
                <div class="p-0 text-sm font-normal">
                    <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                        <flux:avatar
                            :name="auth()->user()->name"
                            :initials="auth()->user()->initials()"
                        />

                        <div class="grid flex-1 text-start text-sm leading-tight">
                            <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                            <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                        </div>
                    </div>
                </div>
            </flux:menu.radio.group>

            <flux:menu.separator />

            <flux:menu.radio.group>
                <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                    تنظیمات
                </flux:menu.item>
            </flux:menu.radio.group>

            <flux:menu.separator />

            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:menu.item
                    as="button"
                    type="submit"
                    icon="arrow-right-start-on-rectangle"
                    class="w-full cursor-pointer"
                    data-test="logout-button"
                >
                    خروج
                </flux:menu.item>
            </form>
        </flux:menu>
    </flux:dropdown>
</flux:header>

{{ $slot }}

@persist('toast')
<flux:toast.group>
    <flux:toast />
</flux:toast.group>
@endpersist

@fluxScripts
</body>
</html>

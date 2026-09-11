<x-layouts::auth :title="__('ورود به سیستم - شرکت راهکار نوین')">

    {{-- استایل‌های اختصاصی رنگارنگ، افکت شیشه‌ای و انیمیشن‌ها --}}
    <style>
        /* انیمیشن ورود (Fade + Slide) */
        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* انیمیشن ورود از راست */
        @keyframes fadeSlideRight {
            from { opacity: 0; transform: translateX(-30px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        /* گرادیانت متحرک زنده */
        @keyframes gradientShift {
            0%   { background-position: 0% 50%; }
            50%  { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* شناور شدن حباب‌های رنگی */
        @keyframes bubbleFloat {
            0%, 100% { transform: translateY(0) scale(1) rotate(0deg); }
            50%      { transform: translateY(-30px) scale(1.12) rotate(10deg); }
        }

        /* درخشش دور دکمه اصلی ورود */
        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 18px rgba(139, 92, 246, 0.5), 0 0 35px rgba(236, 72, 153, 0.3); }
            50%      { box-shadow: 0 0 28px rgba(236, 72, 153, 0.7), 0 0 45px rgba(59, 130, 246, 0.5); }
        }

        /* متن گرادیانتی متحرک شاد */
        .ard-gradient-text {
            background: linear-gradient(270deg, #06b6d4, #3b82f6, #8b5cf6, #ec4899, #10b981);
            background-size: 300% 300%;
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: gradientShift 5s ease infinite;
        }

        /* کارت شیشه‌ای اصلی با پس‌زمینه رنگارنگ */
        .ard-login-card {
            animation: fadeSlideUp 0.8s ease-out both;
            position: relative;
            overflow: hidden;
            border-radius: 1.5rem;
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }

        .dark .ard-login-card {
            background: rgba(18, 24, 38, 0.75);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
        }

        .ard-login-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(59, 130, 246, 0.12), rgba(139, 92, 246, 0.12), rgba(236, 72, 153, 0.12));
            background-size: 250% 250%;
            animation: gradientShift 7s ease infinite;
            pointer-events: none;
            z-index: 0;
        }

        .ard-login-card > * {
            position: relative;
            z-index: 1;
        }

        /* انیمیشن مرحله‌ای المان‌های داخل فرم */
        .ard-login-card form { animation: fadeSlideUp 0.8s ease-out 0.15s both; }
        .ard-login-card form > *:nth-child(1) { animation: fadeSlideRight 0.6s ease-out 0.25s both; }
        .ard-login-card form > *:nth-child(2) { animation: fadeSlideRight 0.6s ease-out 0.40s both; }
        .ard-login-card form > *:nth-child(3) { animation: fadeSlideRight 0.6s ease-out 0.55s both; }
        .ard-login-card form > *:nth-child(4) { animation: fadeSlideRight 0.6s ease-out 0.70s both; }

        /* حباب‌های پس‌زمینه */
        .ard-bubble {
            position: absolute;
            border-radius: 9999px;
            filter: blur(45px);
            opacity: 0.5;
            pointer-events: none;
            z-index: 0;
        }
        .ard-bubble-1 {
            width: 200px; height: 200px;
            background: #3b82f6;
            top: -70px; right: -50px;
            animation: bubbleFloat 6s ease-in-out infinite;
        }
        .ard-bubble-2 {
            width: 170px; height: 170px;
            background: #ec4899;
            bottom: -60px; left: -40px;
            animation: bubbleFloat 8s ease-in-out infinite reverse;
        }
        .ard-bubble-3 {
            width: 130px; height: 130px;
            background: #8b5cf6;
            top: 35%; left: 65%;
            animation: bubbleFloat 7s ease-in-out 1s infinite;
        }
        .ard-bubble-4 {
            width: 120px; height: 120px;
            background: #10b981;
            bottom: 20%; right: 70%;
            animation: bubbleFloat 9s ease-in-out 0.5s infinite;
        }

        /* دکمه ورود درخشان */
        .ard-login-card button[type="submit"],
        .ard-login-card [data-test="login-button"] {
            background: linear-gradient(90deg, #6366f1, #8b5cf6, #ec4899) !important;
            border: none !important;
            color: #ffffff !important;
            animation: pulseGlow 3s ease-in-out infinite;
            transition: transform 0.2s ease, filter 0.2s ease;
        }
        .ard-login-card button[type="submit"]:hover,
        .ard-login-card [data-test="login-button"]:hover {
            transform: translateY(-2px) scale(1.02);
            filter: brightness(1.1);
        }

        /* افکت فوکوس روی فیلدهای ورودی */
        .ard-login-card input:focus {
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.3) !important;
            border-color: #8b5cf6 !important;
            transition: box-shadow 0.3s ease, border-color 0.3s ease;
        }
    </style>

    <div class="flex flex-col gap-6 ard-login-card">
        {{-- حباب‌های شیشه‌ای رنگارنگ --}}
        <div class="ard-bubble ard-bubble-1"></div>
        <div class="ard-bubble ard-bubble-2"></div>
        <div class="ard-bubble ard-bubble-3"></div>
        <div class="ard-bubble ard-bubble-4"></div>

        {{-- هدر صفحه ورود --}}
        <x-auth-header :title="__('ورود به سیستم')" :description="__('لطفاً نام کاربری و رمز عبور خود را وارد کنید')" />

        <!-- وضعیت نشست (Session Status) -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        {{-- فرم ورود فقط با نام کاربری و رمز عبور --}}
        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- نام کاربری -->
            <flux:input
                name="user_name"
                :label="__('نام کاربری')"
                :value="old('user_name')"
                required
                autofocus
                autocomplete="username"
                placeholder="نام کاربری خود را وارد کنید"
            />

            <!-- رمز عبور -->
            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('رمز عبور')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('رمز عبور خود را وارد کنید')"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-sm end-0" :href="route('password.request')" wire:navigate>
                        {{ __('فراموشی رمز عبور؟') }}
                    </flux:link>
                @endif
            </div>

            <!-- مرا به خاطر بسپار -->
            <flux:checkbox name="remember" :label="__('مرا به خاطر بسپار')" :checked="old('remember')" />

            <!-- دکمه ارسال -->
            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full font-bold py-2.5" data-test="login-button">
                    {{ __('ورود به حساب') }}
                </flux:button>
            </div>
        </form>

        {{-- فوتر و نام شرکت پیش‌فرض --}}
        <div class="pt-2 text-sm text-center border-t border-zinc-200/50 dark:border-zinc-700/50">
            <span class="ard-gradient-text font-bold text-base">
                {{ config('app.name', 'شرکت راهکار نوین') }}
            </span>
        </div>
    </div>
</x-layouts::auth>

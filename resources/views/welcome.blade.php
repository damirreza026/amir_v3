<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Vazirmatn', 'Figtree', 'sans-serif'],
                    },
                    animation: {
                        'glow': 'glow 3s ease-in-out infinite',
                    },
                    keyframes: {
                        glow: {
                            '0%, 100%': { opacity: 0.5, transform: 'scale(1)' },
                            '50%': { opacity: 1, transform: 'scale(1.05)' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }

        /* Custom Gradient Text */
        .text-gradient {
            background: linear-gradient(to right, #3b82f6, #8b5cf6, #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* ARD Signature Animation */
        @keyframes textReveal {
            from { opacity: 0; letter-spacing: -0.5em; filter: blur(10px); }
            to { opacity: 1; letter-spacing: 0.1em; filter: blur(0); }
        }
        .ard-signature {
            animation: textReveal 2s ease-out forwards;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* For the floating animation */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
    </style>
</head>
<body class="font-sans antialiased bg-[#05070a] text-slate-200 min-h-screen flex flex-col">

<!-- Background Accents (Corporate Style) -->
<div class="fixed inset-0 -z-10">
    <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-blue-900/20 blur-[120px]"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] rounded-full bg-purple-900/20 blur-[120px]"></div>
    <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-10"></div>
</div>

<!-- Header -->
<header class="w-full px-6 py-6 lg:px-12 flex justify-between items-center">
    <div class="flex items-center gap-3">
        <div class="h-10 w-10 rounded-lg bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center shadow-lg shadow-blue-500/20">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <span class="text-xl font-bold tracking-tight text-white">{{ config('app.name', 'Enterprise') }}</span>
    </div>

    <nav class="flex gap-4">
        @auth
            <a href="{{ url('/dashboard') }}" class="px-5 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-500 rounded-full transition shadow-lg shadow-blue-600/20">داشبورد</a>
        @else
            @if (Route::has('login'))
                <a href="{{ route('login') }}" class="px-5 py-2 text-sm font-medium text-slate-300 hover:text-white transition">ورود</a>
            @endif
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="px-5 py-2 text-sm font-medium text-white bg-white/10 hover:bg-white/20 rounded-full border border-white/10 transition">ثبت‌نام</a>
            @endif
        @endauth
    </nav>
</header>

<!-- Hero Section -->
<main class="flex-grow flex items-center px-6 lg:px-12 py-12">
    <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-16 items-center w-full">

        <!-- Content -->
        <div class="text-right order-2 lg:order-1">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-xs font-bold mb-6">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                    </span>
                سیستم مدیریت هوشمند
            </div>

            <h1 class="text-5xl lg:text-7xl font-extrabold text-white leading-tight mb-8">
                آینده‌ی <br>
                <span class="text-gradient">مدیریت کسب‌وکار</span>
            </h1>

            <p class="text-lg text-slate-400 leading-relaxed mb-10 max-w-xl">
                با استفاده از تکنولوژی‌های پیشرفته، فرآیندهای پیچیده را به تجربه‌ای ساده و لذت‌بخش تبدیل می‌کنیم. امنیت، سرعت و دقت، همه‌ی آن‌هایی هستند که شما نیاز دارید.
            </p>

            <div class="flex flex-wrap gap-4 justify-start">
                <a href="{{ route('login') }}" class="px-8 py-4 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-2xl shadow-xl shadow-blue-600/30 transition-all hover:-translate-y-1">
                    شروع کار با سیستم
                </a>
                <a href="#features" class="px-8 py-4 bg-white/5 hover:bg-white/10 text-white font-semibold rounded-2xl border border-white/10 transition-all">
                    مشاهده ویژگی‌ها
                </a>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-3 gap-6 mt-16">
                <div>
                    <div class="text-2xl font-bold text-white">99%</div>
                    <div class="text-sm text-slate-500">پایداری سیستم</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-white">24/7</div>
                    <div class="text-sm text-slate-500">پشتیبانی فنی</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-white">10x</div>
                    <div class="text-sm text-slate-500">افزایش سرعت</div>
                </div>
            </div>
        </div>

        <!-- Visual Element (Floating Card) -->
        <div class="relative order-1 lg:order-2 flex justify-center">
            <div class="relative w-full max-w-[450px] aspect-square">
                <!-- Decorative Rings -->
                <div class="absolute inset-0 rounded-full border border-blue-500/20 animate-[spin_20s_linear_infinite]"></div>
                <div class="absolute inset-10 rounded-full border border-purple-500/10 animate-[spin_15s_linear_infinite_reverse]"></div>

                <!-- Main Floating Card -->
                <div class="absolute inset-20 glass-card rounded-3xl shadow-2xl flex flex-col items-center justify-center p-8 text-center animate-float">
                    <div class="w-16 h-16 bg-blue-600/20 rounded-2xl flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.826.822 9.479 6.822 12.02 0 11.25.944 11.955 0 0111.955-3.04 11.955 11.955 0 018.618-3.04z" /></svg>
                    </div>

                    <h3 class="text-xl font-bold text-white mb-2">A.R.D</h3>
                    <p class="text-sm text-slate-400">طراحی شده توسط</p>
                    <p class="text-xs font-semibold text-blue-400 mt-1">امیررضا درویشی</p>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Footer -->
<footer class="py-8 text-center border-t border-white/5">
    <p class="text-slate-600 text-sm">
        تمامی حقوق محفوظ است © {{ date('Y') }} - طراحی و توسعه با
        <span class="ard-signature inline-block text-white font-bold ml-1">A.R.D</span>
    </p>
    <p class="text-xs text-slate-700 mt-1">امیررضا درویشی</p>
</footer>

</body>
</html>

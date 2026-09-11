<x-layouts::app :title="__('پرداخت حقوق')">

    @php
        $user = auth()->user();
        $profile = $user?->profile;
        $roleId = (int) ($profile?->role_id ?? 0);

        /*
        |--------------------------------------------------------------------------
        | دسترسی‌های این صفحه
        |--------------------------------------------------------------------------
        | 1: سوپر ادمین
        | 2: ادمین
        | 5: حسابدار
        */
        $allowedRoles = [1, 2, 5];
        $hasAccess = in_array($roleId, $allowedRoles, true);
    @endphp

    @if ($hasAccess)

        <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

            <div class="grid auto-rows-min gap-4 md:grid-cols-3">

                <div
                    class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700"
                    style="height: 280px !important; min-height: 480px !important;"
                >
                    <x-placeholder-pattern
                        class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20"
                    />

                    <x-salary.salary />
                </div>

            </div>

            {{--
            <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern
                    class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20"
                />
            </div>
            --}}

        </div>

    @else

        <div class="flex min-h-[60vh] items-center justify-center px-4">
            <div class="w-full max-w-lg rounded-2xl border border-red-200 bg-red-50 p-8 text-center shadow-sm dark:border-red-500/20 dark:bg-red-500/10">

                <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-500/15 dark:text-red-400">
                    <flux:icon
                        name="shield-exclamation"
                        variant="solid"
                        class="size-8"
                    />
                </div>

                <h2 class="mt-5 text-xl font-bold text-red-700 dark:text-red-400">
                    دسترسی غیرمجاز
                </h2>

                <p class="mt-3 text-sm leading-7 text-red-600/80 dark:text-red-300/80">
                    شما اجازه دسترسی به صفحه پرداخت حقوق را ندارید.
                </p>

                <a
                    href="{{ route('dashboard') }}"
                    wire:navigate
                    class="mt-6 inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-900"
                >
                    <flux:icon name="arrow-right" class="size-4" />

                    بازگشت به داشبورد
                </a>

            </div>
        </div>

    @endif

</x-layouts::app>

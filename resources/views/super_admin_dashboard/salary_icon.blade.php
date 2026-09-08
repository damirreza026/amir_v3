<x-layouts::app :title="__('Dashboard')">
    @auth
        @php
            $user = auth()->user();
            $profile = $user->profile;
            $roleId = $profile?->role_id ?? 0;
        @endphp
    @endauth

    @if($roleId == 1)
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
                {{--        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">--}}
                {{--            <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />--}}
                {{--        </div>--}}
            </div>
        @else

        {{'دسترسی غیر مجاز'}}

    @endif


</x-layouts::app>

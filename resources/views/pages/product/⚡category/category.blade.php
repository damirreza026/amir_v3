<div dir="rtl" class="space-y-6">

    {{-- هدر صفحه --}}
    <div class="flex flex-col justify-between gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm md:flex-row md:items-center">
        <div>
            <h1 class="text-xl font-black text-zinc-950">
                مدیریت دسته‌بندی محصولات
            </h1>
            <p class="mt-1 text-sm font-semibold text-gray-600">
                کاربر: {{ Auth::user()?->profile?->first_name }} {{ Auth::user()?->profile?->last_name }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button
                type="button"
                wire:click="openSaveModal"
                class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 !cursor-pointer"
            >
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>افزودن دسته‌بندی جدید</span>
            </button>

            <a
                href="{{ URL::signedRoute('ProductHandel') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-gray-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-800 !cursor-pointer"
            >
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span>برگشت</span>
            </a>
        </div>
    </div>

    {{-- پیام‌های نشست --}}
    @if (session()->has('success'))
        <div class="flex items-center gap-2 rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-bold text-green-800 shadow-sm">
            <svg class="size-5 shrink-0 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-bold text-red-800 shadow-sm">
            <svg class="size-5 shrink-0 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- نوار جست‌وجو --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="relative max-w-md">
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                <svg class="size-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="جست‌وجوی سریع نام دسته‌بندی..."
                class="w-full rounded-lg border border-gray-300 bg-gray-50 py-2.5 pr-10 pl-10 text-sm font-bold text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-200"
            />

            @if (filled($search))
                <button
                    type="button"
                    wire:click="$set('search', '')"
                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 transition hover:text-gray-600 !cursor-pointer"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            @endif
        </div>
    </div>

    {{-- جدول دسته‌بندی‌ها --}}
    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="w-full text-right text-sm">
            <thead class="border-b border-gray-200 bg-gray-100/90 text-xs font-bold text-gray-800">
            <tr>
                <th
                    wire:click="sort('name')"
                    class="cursor-pointer select-none px-5 py-4 transition hover:bg-gray-200/70"
                >
                    <div class="flex items-center gap-2">
                        <span>نام دسته‌بندی</span>
                        @if ($sortBy === 'name')
                            <span class="text-xs font-extrabold text-indigo-600">
                                    {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                </span>
                        @endif
                    </div>
                </th>

                <th class="px-5 py-4">مدل‌ها</th>
                <th class="px-5 py-4">تعداد</th>
                <th class="px-5 py-4 text-center">عملیات</th>
            </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 bg-white">
            @forelse ($this->categories as $category)
                <tr wire:key="category-{{ $category->id }}" class="transition hover:bg-amber-50/40">
                    <td class="whitespace-nowrap px-5 py-4 text-base font-black text-zinc-950">
                        {{ $category->name }}
                    </td>

                    <td class="whitespace-nowrap px-5 py-4">
                        <a
                            href="{{ URL::signedRoute('product', ['category' => $category]) }}"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700 transition hover:bg-blue-600 hover:text-white !cursor-pointer"
                        >
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>مدل‌ها و نمونه‌ها</span>
                        </a>
                    </td>

                    <td class="whitespace-nowrap px-5 py-4">
                        <a
                            href="{{ URL::signedRoute('batch', ['category' => $category]) }}"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-bold text-indigo-700 transition hover:bg-indigo-600 hover:text-white !cursor-pointer"
                        >
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <span>تعداد هر مدل</span>
                        </a>
                    </td>

                    <td class="whitespace-nowrap px-5 py-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <button
                                type="button"
                                wire:click="edit({{ $category->id }})"
                                class="inline-flex items-center rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition hover:bg-amber-600 !cursor-pointer"
                            >
                                ویرایش
                            </button>
                            <button
                                type="button"
                                wire:click="delete_form({{ $category->id }})"
                                class="inline-flex items-center rounded-lg bg-red-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition hover:bg-red-700 !cursor-pointer"
                            >
                                حذف
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-5 py-10 text-center text-sm font-bold text-gray-500">
                        @if (filled($search))
                            هیچ دسته‌بندی مطابق با عبارت «{{ $search }}» پیدا نشد.
                        @else
                            هیچ دسته‌بندی یافت نشد.
                        @endif
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- صفحه‌بندی --}}
    <div>
        {{ $this->categories->links() }}
    </div>

    {{-- مودال افزودن دسته‌بندی --}}
    <flux:modal name="save-category" class="!w-full md:!w-[28rem] !overflow-hidden !rounded-2xl !border !border-gray-200 !bg-white !p-0 !shadow-xl" @close="reset_data">
        <div class="text-gray-900">
            <div class="border-b border-gray-200 bg-gray-50/80 px-6 py-4">
                <h3 class="text-base font-black text-zinc-950">
                    افزودن دسته‌بندی جدید
                </h3>
            </div>

            <div class="space-y-4 bg-white px-6 py-5">
                @error('save_error')
                <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-xs font-bold text-red-700">
                    {{ $message }}
                </div>
                @enderror

                <div>
                    <label class="block mb-1.5 text-xs font-bold text-zinc-800">نام دسته‌بندی</label>
                    <input
                        type="text"
                        wire:model="name"
                        placeholder="مثلاً: لبنیات سنتی"
                        class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 text-sm font-bold text-zinc-900 placeholder-gray-400 shadow-sm transition focus:border-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-100"
                    />
                    @error('name')
                    <p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4">
                <flux:modal.close>
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-700 shadow-sm transition hover:bg-gray-100 !cursor-pointer"
                    >
                        انصراف
                    </button>
                </flux:modal.close>

                <button
                    wire:click="save"
                    type="button"
                    class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 !cursor-pointer"
                >
                    ثبت دسته‌بندی
                </button>
            </div>
        </div>
    </flux:modal>

    {{-- مودال ویرایش دسته‌بندی --}}
    <flux:modal name="edit-category" class="!w-full md:!w-[28rem] !overflow-hidden !rounded-2xl !border !border-gray-200 !bg-white !p-0 !shadow-xl" @close="reset_data">
        <div class="text-gray-900">
            <div class="border-b border-gray-200 bg-gray-50/80 px-6 py-4">
                <h3 class="text-base font-black text-zinc-950">
                    ویرایش دسته‌بندی
                </h3>
            </div>

            <div class="space-y-4 bg-white px-6 py-5">
                @error('update_error')
                <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-xs font-bold text-red-700">
                    {{ $message }}
                </div>
                @enderror

                <div>
                    <label class="block mb-1.5 text-xs font-bold text-zinc-800">نام دسته‌بندی</label>
                    <input
                        type="text"
                        wire:model="name"
                        placeholder="نام جدید دسته‌بندی"
                        class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 text-sm font-bold text-zinc-900 placeholder-gray-400 shadow-sm transition focus:border-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-100"
                    />
                    @error('name')
                    <p class="mt-1.5 text-xs font-bold text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4">
                <flux:modal.close>
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-700 shadow-sm transition hover:bg-gray-100 !cursor-pointer"
                    >
                        انصراف
                    </button>
                </flux:modal.close>

                <button
                    wire:click="update"
                    type="button"
                    class="inline-flex items-center justify-center rounded-xl bg-amber-500 px-5 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-amber-600 !cursor-pointer"
                >
                    ذخیره تغییرات
                </button>
            </div>
        </div>
    </flux:modal>

    {{-- مودال حذف دسته‌بندی --}}
    <flux:modal name="delete-category" class="!w-full md:!w-96 !overflow-hidden !rounded-2xl !border !border-gray-200 !bg-white !p-0 !shadow-xl" @close="reset_data">
        <div class="text-gray-900">
            <div class="border-b border-gray-200 bg-gray-50/80 px-6 py-4">
                <h3 class="text-base font-black text-zinc-950">
                    حذف دسته‌بندی
                </h3>
            </div>

            <div class="bg-white px-6 py-5">
                <p class="text-sm font-bold leading-relaxed text-gray-700">
                    آیا از حذف دسته‌بندی
                    <strong class="font-black text-red-600">«{{ $name }}»</strong>
                    اطمینان دارید؟
                </p>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4">
                <flux:modal.close>
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-700 shadow-sm transition hover:bg-gray-100 !cursor-pointer"
                    >
                        انصراف
                    </button>
                </flux:modal.close>

                <button
                    wire:click="delete"
                    type="button"
                    class="inline-flex items-center justify-center rounded-xl bg-red-600 px-5 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-red-700 !cursor-pointer"
                >
                    حذف نهایی
                </button>
            </div>
        </div>
    </flux:modal>

</div>

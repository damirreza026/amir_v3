<div class="space-y-6">
    {{-- هدر صفحه و خوش‌آمدگویی --}}
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-xl font-bold">{{ Auth::user()->profile->first_name . ' ' . Auth::user()->profile->last_name }}</h1>
            <p class="text-sm text-gray-500">دسته بندی محصولات</p>
        </div>
        <div>
            <flux:button wire:click="openSaveModal" variant="primary" color="green">
                افزودن دسته بندی جدید
            </flux:button>
        </div>
    </div>

    <hr class="border-gray-200">

    {{-- نمایش پیام‌های موفقیت و خطا --}}
    @if (session()->has('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    {{-- جدول دسته‌بندی‌ها --}}
    <flux:table :paginate="$this->categories">
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection" wire:click="sort('name')">
                نام دسته
            </flux:table.column>
            <flux:table.column>مدل(نمونه دسته)</flux:table.column>
            <flux:table.column>تعداد</flux:table.column>
            <flux:table.column>عملیات</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->categories as $category)
                <flux:table.row :key="$category->id">
                    <flux:table.cell class="whitespace-nowrap font-medium">{{ $category->name }}</flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap">
                        <a
                            class="inline-block rounded-lg !bg-blue-600 px-3 py-1.5 text-xs !text-white no-underline transition hover:!bg-blue-700"
                            style="background-color: #2563eb !important; color: #ffffff !important;"
                            href="{{ \Illuminate\Support\Facades\URL::signedRoute('product', ['category' => $category]) }}"
                        >
                            افزودن به مدل ها یا نمونه های این دسته
                        </a>
                    </flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap">
                        <a
                            class="inline-block rounded-lg !bg-blue-600 px-3 py-1.5 text-xs !text-white no-underline transition hover:!bg-blue-700"
                            style="background-color: #2563eb !important; color: #ffffff !important;"
                            href="{{ \Illuminate\Support\Facades\URL::signedRoute('batch', ['category' => $category]) }}"
                        >
                            افزودن به تعداد هر مدل یا نمونه این دسته
                        </a>
                    </flux:table.cell>


                    <flux:table.cell class="whitespace-nowrap">
                        <div class="flex space-x-2 space-x-reverse">
                            <flux:button variant="primary" color="yellow" size="sm" wire:click="edit({{ $category->id }})">ویرایش</flux:button>
                            <flux:button variant="primary" color="red" size="sm" wire:click="delete_form({{ $category->id }})">حذف</flux:button>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    {{-- مودال افزودن دسته‌بندی جدید --}}
    <flux:modal name="save" class="md:w-96" @close="reset_data">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">دسته بندی جدید</flux:heading>
                <flux:text class="mt-2">افزودن دسته بندی جدید به انبار</flux:text>
            </div>

            @error('save_error')
            <div class="text-xs text-red-600 font-bold bg-red-50 p-2 rounded">{{ $message }}</div>
            @enderror

            <div>
                <flux:input wire:model="name" label="نام دسته بندی" placeholder="نام دسته بندی" autocomplete="off" />
                @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex">
                <flux:spacer/>
                <flux:button wire:click="save()" type="submit" variant="primary">افزودن نهایی</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- مودال ویرایش دسته‌بندی --}}
    <flux:modal name="edit-user" class="md:w-96" @close="reset_data">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">ویرایش دسته بندی</flux:heading>
                <flux:text class="mt-2">برای ویرایش این دسته بندی اطلاعات زیر را کامل کنید</flux:text>
            </div>

            @error('update_error')
            <div class="text-xs text-red-600 font-bold bg-red-50 p-2 rounded">{{ $message }}</div>
            @enderror

            <div>
                <flux:input wire:model="name" label="Name" placeholder="Category Name" autocomplete="off" />
                @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex">
                <flux:spacer/>
                <flux:button wire:click="update()" type="submit" variant="primary">ثبت تغییرات</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- مودال حذف دسته‌بندی --}}
    <flux:modal name="delete-user" class="md:w-96" @close="reset_data">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">حذف دسته بندی</flux:heading>
                <flux:text class="mt-2">آیا از حذف "{{ $name }}" مطمئن هستید ؟</flux:text>
            </div>

            <div class="flex space-x-2 space-x-reverse justify-end">
                <flux:button wire:click="reset_data" variant="ghost">لغو</flux:button>
                <flux:button wire:click="delete()" type="submit" color="red" variant="primary">حذف نهایی</flux:button>
            </div>
        </div>
    </flux:modal>
    <a
        href="{{ URL::signedRoute('ProductHandel') }}"

        class="inline-block rounded-lg !bg-blue-600 px-4 py-2 !text-white no-underline transition hover:!bg-blue-700"
        style="background-color: #2563eb !important; color: #ffffff !important;"
    >
        برگشت
    </a>
</div>

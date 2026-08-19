<div>

    <flux:button wire:click="openSaveModal" variant="primary" color="green">
        افزودن سال جدید
    </flux:button>

    <flux:table :paginate="$this->years">
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'year'" :direction="$sortDirection"
                               wire:click="sort('year')">
                سال
            </flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->years as $year)
                <flux:table.row :key="$year->id">
                    <flux:table.cell class="whitespace-nowrap font-medium">{{ $year->year }}</flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap">
                        <a
                            class="inline-block rounded-lg !bg-blue-600 px-3 py-1.5 text-xs !text-white no-underline transition hover:!bg-blue-700"
                            style="background-color: #2563eb !important; color: #ffffff !important;"
                            href="{{ \Illuminate\Support\Facades\URL::signedRoute('month', ['year' => $year]) }}"
                        >
                            افزودن ماه های این سال
                        </a>
                    </flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap">
                        <a
                            class="inline-block rounded-lg !bg-blue-600 px-3 py-1.5 text-xs !text-white no-underline transition hover:!bg-blue-700"
                            style="background-color: #2563eb !important; color: #ffffff !important;"
                            href="{{ \Illuminate\Support\Facades\URL::signedRoute('week', ['year' => $year]) }}"
                        >
                            افزودن فیش حقوقی ماهیانه هر پرسنل در این سال
                        </a>
                    </flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap">
                        <div class="flex space-x-2 space-x-reverse">
                            <flux:button variant="primary" color="yellow" size="sm"
                                         wire:click="edit({{ $year->id }})">ویرایش
                            </flux:button>
                            <flux:button variant="primary" color="red" size="sm"
                                         wire:click="delete_form({{ $year->id }})">حذف
                            </flux:button>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    <flux:modal name="save" class="md:w-96" @close="reset_data">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">سال جدید</flux:heading>
                <flux:text class="mt-2">افزودن سال مالی و حسابداری </flux:text>
            </div>

            @error('save_error')
            <div class="text-xs text-red-600 font-bold bg-red-50 p-2 rounded">{{ $message }}</div>
            @enderror

            <div>
                <flux:input wire:model="year" label="سال" placeholder="سال" autocomplete="off" />
                @error('year') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex">
                <flux:spacer/>
                <flux:button wire:click="save()" type="submit" variant="primary">افزودن نهایی</flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="edit-user" class="md:w-96" @close="reset_data">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">ویرایش سال</flux:heading>
                <flux:text class="mt-2">برای ویرایش این سال در فرم سال را تغییر دهید</flux:text>
            </div>

            @error('update_error')
            <div class="text-xs text-red-600 font-bold bg-red-50 p-2 rounded">{{ $message }}</div>
            @enderror

            <div>
                <flux:input wire:model="year" label="نام" placeholder="نام سال" autocomplete="off" />
                @error('year') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex">
                <flux:spacer/>
                <flux:button wire:click="update()" type="submit" variant="primary">ثبت تغییرات</flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="delete-user" class="md:w-96" @close="reset_data">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">حذف سال</flux:heading>
                <flux:text class="mt-2">آیا از حذف سال "{{ $this->year }}" مطمئن هستید ؟</flux:text>
            </div>

            <div class="flex space-x-2 space-x-reverse justify-end">
                <flux:modal.close>
                    <flux:button variant="ghost">لغو</flux:button>
                </flux:modal.close>
                <flux:button wire:click="delete()" type="button" color="red" variant="primary">حذف نهایی</flux:button>
            </div>
        </div>
    </flux:modal>

</div>

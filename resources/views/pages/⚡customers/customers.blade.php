<div>
    <flux:table :paginate="$this->customers">
        <flux:table.columns>
            <flux:table.column
                sortable
                :sorted="$sortBy === 'shop_name'"
                :direction="$sortDirection"
                wire:click="sort('shop_name')"
            >
                نام مشتری
            </flux:table.column>

            <flux:table.column>شماره تماس</flux:table.column>
            <flux:table.column>آدرس</flux:table.column>
            <flux:table.column>عملیات</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->customers as $customer)
                <flux:table.row :key="$customer->id">
                    <flux:table.cell class="whitespace-nowrap">{{ $customer->shop_name }}</flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">{{ $customer->phone }}</flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">{{ $customer->address }}</flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap space-x-2">
                        <flux:button
                            variant="primary"
                            color="yellow"
                            wire:click="edit({{ $customer->id }})"
                        >
                            ویرایش
                        </flux:button>

                        <flux:button
                            variant="primary"
                            color="red"
                            wire:click="del_form({{ $customer->id }})"
                        >
                            حذف
                        </flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    {{-- ============ MODAL: EDIT ============ --}}
    <flux:modal name="edit" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">ویرایش مشتری</flux:heading>
                <flux:text class="mt-2">برای ویرایش اطلاعات مشتری هر قسمتی را که می خواهید تغییر دهید</flux:text>
            </div>

            <div class="space-y-3">
                <flux:input wire:model.defer="shop_name" label="نام مشتری" placeholder="نام مشتری"/>
                @error('shop_name') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

                <flux:input wire:model.defer="phone" label="شماره تماس" placeholder="شماره تماس"/>
                @error('phone') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

                <flux:input wire:model.defer="address" label="آدرس" placeholder="آدرس"/>
                @error('address') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            @error('cust_id') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

            <div class="flex gap-2">
                <flux:spacer/>
                <flux:modal.close>
                    <flux:button variant="ghost" wire:click="$refresh">لغو</flux:button>
                </flux:modal.close>

                <flux:button wire:click="update" type="button" variant="primary" wire:loading.attr="disabled">
                    اعمال تغییرات
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- ============ MODAL: DELETE ============ --}}
    <flux:modal name="delete" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">حذف مشتری</flux:heading>
                <flux:text class="mt-2">آیا از حذف مشتری با نام  <b>{{ $shop_name }}</b> مطمئمن هستید ؟</flux:text>
            </div>

            @error('cust_id') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

            <div class="flex gap-2">
                <flux:spacer/>
                <flux:modal.close>
                    <flux:button variant="ghost">لغو</flux:button>
                </flux:modal.close>

                <flux:button wire:click="delete" type="button" variant="primary" color="red" wire:loading.attr="disabled">
                    حذف
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- ============ MODAL: SAVE ============ --}}
    <flux:modal name="save" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">افزودن مشتری</flux:heading>
                <flux:text class="mt-2">برای افزودن مشتری جدید قسمت های پایین را کامل کنید</flux:text>
            </div>

            <div class="space-y-3">
                <flux:input wire:model.defer="shop_name" label="نام مشتری" placeholder="نام مشتری"/>
                @error('shop_name') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

                <flux:input wire:model.defer="phone" label="شماره تماس" placeholder="شماره تماس"/>
                @error('phone') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

                <flux:input wire:model.defer="address" label="آدرس" placeholder="آدرس"/>
                @error('address') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-2">
                <flux:spacer/>
                <flux:modal.close>
                    <flux:button variant="ghost" wire:click="resetForm">لغو</flux:button>
                </flux:modal.close>

                <flux:button wire:click="save" type="button" variant="primary" color="green" wire:loading.attr="disabled">
                    ثبت نهایی
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal.trigger name="save">
        <flux:button wire:click="openSaveModal" variant="primary" color="green">
            افزودن مشتری جدید
        </flux:button>
    </flux:modal.trigger>
    <a
        href="{{ URL::signedRoute('customer_s_a') }}"

        class="inline-block rounded-lg !bg-blue-600 px-4 py-2 !text-white no-underline transition hover:!bg-blue-700"
        style="background-color: #2563eb !important; color: #ffffff !important;"
    >
        برگشت
    </a>
</div>

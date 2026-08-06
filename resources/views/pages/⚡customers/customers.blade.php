<div>
    <flux:table :paginate="$this->customers">
        <flux:table.columns>
            <flux:table.column
                sortable
                :sorted="$sortBy === 'shop_name'"
                :direction="$sortDirection"
                wire:click="sort('shop_name')"
            >
                shop_name
            </flux:table.column>

            <flux:table.column>phone</flux:table.column>
            <flux:table.column>address</flux:table.column>
            <flux:table.column>actions</flux:table.column>
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
                            Edit
                        </flux:button>

                        <flux:button
                            variant="primary"
                            color="red"
                            wire:click="del_form({{ $customer->id }})"
                        >
                            Delete
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
                <flux:heading size="lg">Edit shop</flux:heading>
                <flux:text class="mt-2">Edit store.</flux:text>
            </div>

            <div class="space-y-3">
                <flux:input wire:model.defer="shop_name" label="shop_name" placeholder="Shop_Name"/>
                @error('shop_name') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

                <flux:input wire:model.defer="phone" label="phone" placeholder="Phone"/>
                @error('phone') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

                <flux:input wire:model.defer="address" label="address" placeholder="Address"/>
                @error('address') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            @error('cust_id') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

            <div class="flex gap-2">
                <flux:spacer/>
                <flux:modal.close>
                    <flux:button variant="ghost" wire:click="$refresh">Cancel</flux:button>
                </flux:modal.close>

                <flux:button wire:click="update" type="button" variant="primary" wire:loading.attr="disabled">
                    Update
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- ============ MODAL: DELETE ============ --}}
    <flux:modal name="delete" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete shop</flux:heading>
                <flux:text class="mt-2">Are you sure to delete: <b>{{ $shop_name }}</b> ?</flux:text>
            </div>

            @error('cust_id') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

            <div class="flex gap-2">
                <flux:spacer/>
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>

                <flux:button wire:click="delete" type="button" variant="primary" color="red" wire:loading.attr="disabled">
                    Delete
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- ============ MODAL: SAVE ============ --}}
    <flux:modal name="save" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">New shop</flux:heading>
                <flux:text class="mt-2">Add a new store.</flux:text>
            </div>

            <div class="space-y-3">
                <flux:input wire:model.defer="shop_name" label="shop_name" placeholder="Shop_Name"/>
                @error('shop_name') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

                <flux:input wire:model.defer="phone" label="phone" placeholder="Phone"/>
                @error('phone') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

                <flux:input wire:model.defer="address" label="address" placeholder="Address"/>
                @error('address') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-2">
                <flux:spacer/>
                <flux:modal.close>
                    <flux:button variant="ghost" wire:click="resetForm">Cancel</flux:button>
                </flux:modal.close>

                <flux:button wire:click="save" type="button" variant="primary" color="green" wire:loading.attr="disabled">
                    Final Add
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal.trigger name="save">
        <flux:button wire:click="openSaveModal" variant="primary" color="green">
            Add new shop
        </flux:button>
    </flux:modal.trigger>
</div>

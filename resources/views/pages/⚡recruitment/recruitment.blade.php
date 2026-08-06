<div class="space-y-6">
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

    <div>
        <flux:button variant="primary" color="green" wire:click="openAddModal">Add new user</flux:button>
    </div>

    <flux:table :paginate="$this->profiles">
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'first_name'" :direction="$sortDirection" wire:click="sort('first_name')">
                First Name
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'last_name'" :direction="$sortDirection" wire:click="sort('last_name')">
                Last Name
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'phone'" :direction="$sortDirection" wire:click="sort('phone')">
                Phone number
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'national_code'" :direction="$sortDirection" wire:click="sort('national_code')">
                N_Code
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'address'" :direction="$sortDirection" wire:click="sort('address')">
                Address
            </flux:table.column>
            <flux:table.column>
                Role
            </flux:table.column>
            <flux:table.column>Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->profiles as $profile)
                <flux:table.row :key="$profile->id">
                    <flux:table.cell class="whitespace-nowrap">{{ $profile->first_name }}</flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">{{ $profile->last_name }}</flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">{{ $profile->phone }}</flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">{{ $profile->national_code }}</flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">{{ $profile->address }}</flux:table.cell>

                    <!-- پیدا کردن نام نقش از روی شناسه ذخیره‌شده و متد کمکی کامپوننت -->
                    <flux:table.cell class="whitespace-nowrap">
                        <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                            {{ $this->roles->firstWhere('id', $profile->role_id)->name ?? 'No Role' }}
                        </span>
                    </flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap">
                        <div class="flex space-x-2 space-x-reverse">
                            <flux:button variant="primary" color="yellow" wire:click="edit({{ $profile->id }})">Edit</flux:button>
                            <flux:button variant="primary" color="red" wire:click="delete_form({{ $profile->id }})">Delete</flux:button>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    {{-- مودال افزودن کاربر جدید --}}
    <flux:modal name="add-user" class="md:w-96" @close="reset_deta">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">New user</flux:heading>
                <flux:text class="mt-2">Hiring a new employee.</flux:text>
            </div>

            @error('save_error')
            <div class="text-xs text-red-600 font-bold bg-red-50 p-2 rounded">{{ $message }}</div>
            @enderror

            <div>
                <flux:input wire:model="user_name" label="User Name" placeholder="User name" autocomplete="off" />
                @error('user_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:input type="password" wire:model="password" label="Password" placeholder="Password" autocomplete="new-password" />
                @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:input type="password" wire:model="confirm_password" label="Confirm Password" placeholder="Confirm Password" autocomplete="new-password" />
                @error('confirm_password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:input wire:model="f_name" label="First Name" placeholder="First name"  />
                @error('f_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:input wire:model="l_name" label="Last Name" placeholder="Last name"  />
                @error('l_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:input wire:model="phone" label="Phone" placeholder="Phone" />
                @error('phone') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:input wire:model="national_code" label="National Code" placeholder="National code" />
                @error('national_code') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:input wire:model="address" label="Address" placeholder="Address"  />
                @error('address') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:select wire:model="role_id" label="Role">
                    <option value="">Choose a role...</option>
                    @foreach ($this->roles as $role)
                        <flux:select.option value="{{ $role->id }}" wire:key="add-role-{{ $role->id }}">
                            {{ $role->name }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
                @error('role_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex">
                <flux:spacer/>
                <flux:button wire:click="save()" type="submit" variant="primary">Final Recruitment</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- مودال ویرایش کاربر --}}
    <flux:modal name="edit-user" class="md:w-96" @close="reset_deta">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Edit user</flux:heading>
                <flux:text class="mt-2">Edit employee details.</flux:text>
            </div>

            @error('update_error')
            <div class="text-xs text-red-600 font-bold bg-red-50 p-2 rounded">{{ $message }}</div>
            @enderror

            <div>
                <flux:input wire:model="user_name" label="User Name" placeholder="User name" autocomplete="off" />
                @error('user_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:input type="password" wire:model="password" label="New Password (optional)" placeholder="Leave blank to keep current" autocomplete="new-password" />
                @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:input type="password" wire:model="confirm_password" label="Confirm New Password" placeholder="Confirm Password" autocomplete="new-password" />
                @error('confirm_password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:input wire:model="f_name" label="First Name" placeholder="First name"/>
                @error('f_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:input wire:model="l_name" label="Last Name" placeholder="Last name" />
                @error('l_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:input wire:model="phone" label="Phone" placeholder="Phone" />
                @error('phone') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:input wire:model="national_code" label="National Code" placeholder="National code"/>
                @error('national_code') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:input wire:model="address" label="Address" placeholder="Address" />
                @error('address') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:select wire:model="role_id" label="Role">
                    <option value="">Choose a role...</option>
                    @foreach ($this->roles as $role)
                        <flux:select.option value="{{ $role->id }}" wire:key="edit-role-{{ $role->id }}">
                            {{ $role->name }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
                @error('role_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex">
                <flux:spacer/>
                <flux:button wire:click="update()" type="submit" variant="primary">Update</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- مودال حذف کاربر --}}
    <flux:modal name="delete-user" class="md:w-96" @close="reset_deta">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete user</flux:heading>
                <flux:text class="mt-2">Are you sure you want to delete {{ $f_name.' '.$l_name }}?</flux:text>
            </div>

            <div class="flex space-x-2 space-x-reverse justify-end">
                <flux:button wire:click="reset_deta" variant="ghost">Cancel</flux:button>
                <flux:button wire:click="delete()" type="submit" color="red" variant="primary">Delete</flux:button>
            </div>
        </div>
    </flux:modal>
</div>

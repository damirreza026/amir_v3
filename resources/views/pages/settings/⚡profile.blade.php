<?php

use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('تنظیمات پروفایل')] class extends Component {
    public string $user_name = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->user_name = Auth::user()->user_name;

        // رمز عبور هرگز از قبل نمایش داده نمی‌شود (امنیت)
        $this->password = '';
        $this->password_confirmation = '';
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'user_name'             => ['required', 'string', 'max:255', Rule::unique('users', 'user_name')->ignore($user->id)],
            'password'              => ['nullable', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['nullable', 'string'],
        ], [
            'user_name.required' => 'نام کاربری الزامی است.',
            'user_name.max'      => 'نام کاربری نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
            'user_name.unique'   => 'این نام کاربری قبلاً استفاده شده است.',
            'password.min'       => 'رمز عبور باید حداقل ۸ کاراکتر باشد.',
            'password.confirmed' => 'رمز عبور و تکرار آن مطابقت ندارند.',
        ]);

        $user->user_name = $validated['user_name'];

        // فقط اگر رمز جدید وارد شده باشد، آن را هش و ذخیره کن
        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        $this->password = '';
        $this->password_confirmation = '';

        Flux::toast(variant: 'success', text: 'پروفایل با موفقیت به‌روزرسانی شد.');
    }
};
?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('تنظیمات پروفایل') }}</flux:heading>

    <x-pages::settings.layout :heading="__('پروفایل')" :subheading="__('نام کاربری و رمز عبور خود را ویرایش کنید')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="user_name" :label="__('نام کاربری')" type="text" required autofocus autocomplete="username" />

            <div>
                <flux:input wire:model="password" :label="__('رمز عبور جدید')" type="password" autocomplete="new-password" placeholder="برای تغییر رمز عبور، رمز جدید وارد کنید" />

                <flux:input wire:model="password_confirmation" :label="__('تکرار رمز عبور جدید')" type="password" autocomplete="new-password" placeholder="رمز عبور جدید را دوباره وارد کنید" />

                <flux:text class="mt-2">
                    {{ __('برای تغییر رمز عبور، رمز جدید (حداقل ۸ کاراکتر) وارد کنید. اگر این فیلدها خالی بمانند، رمز فعلی حفظ می‌شود.') }}
                </flux:text>
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full" data-test="update-profile-button">
                        {{ __('ذخیره تغییرات') }}
                    </flux:button>
                </div>
            </div>
        </form>

{{--        <livewire:pages::settings.delete-user-form />--}}
    </x-pages::settings.layout>
</section>

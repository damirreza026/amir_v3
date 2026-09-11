<x-layouts::auth :title="__('Confirm password')">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('تغییر رمز عبور')"
            :description="__('این بخش از برنامه امن است. لطفاً برای ادامه، رمز عبور خود را تأیید کنید')"
        />

        <x-auth-session-status class="text-center" :status="session('status')" />

{{--        <x-passkey-verify--}}
{{--            options-route="passkey.confirm-options"--}}
{{--            submit-route="passkey.confirm"--}}
{{--            :label="__('رمز عبور فعلی خود را وارد کنید')"--}}
{{--            :loading-label="__('Confirming...')"--}}
{{--            :separator="__('Or confirm with password')"--}}
{{--        />--}}

        <form method="POST" action="{{ route('password.confirm.store') }}" class="flex flex-col gap-6">
            @csrf

            <flux:input
                name="password"
                :label="__('رمز عبور')"
                type="password"
                required
                autocomplete="current-password"
                :placeholder="__('رمز عبور فعلی خود را وارد کنید')"
                viewable
            />

            <flux:button variant="primary" type="submit" class="w-full" data-test="confirm-password-button">
                {{ __('تایید') }}
            </flux:button>
        </form>
    </div>
</x-layouts::auth>

<?php

use Livewire\Component;
use Livewire\Attributes\Title;

new #[Title('Appearance settings')] class extends Component {
    //
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('تغییرات ظاهری سایت') }}</flux:heading>

    <x-pages::settings.layout :heading="__('ظاهر یا رنگ بک گرند')" :subheading="__('تنظیمات رنگ قالب سایت شما که پیش فرض تاریک (مشکی) است')">
        <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
            <flux:radio value="light" icon="sun">{{ __('روشن(سفید)') }}</flux:radio>
            <flux:radio value="dark" icon="moon">{{ __('تاریک(مشکی)') }}</flux:radio>
            <flux:radio value="system" icon="computer-desktop">{{ __('سیستم') }}</flux:radio>
        </flux:radio.group>
    </x-pages::settings.layout>
</section>

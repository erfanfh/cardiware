@extends('admin.layout')

@section('title', 'تنظیمات عمومی')

@section('content')
    <x-admin.form action="{{ route('admin.settings.general.store') }}" method="put">
        <x-admin.page>
            <x-admin.tabs
                :tabs="[
                    'main' => 'اصلی',
                    'goftino' => 'گفتینو',
                    'sms-panel' => 'پنل پیامکی',
                    'payment-gateway' => 'پرداخت و مالیات',
                ]"
                active="{{ request()->input('tab') ?: 'main' }}"
            >
                <x-admin.tabs.panel name="main">
                    @include('admin.system.settings.partials.general.main')
                </x-admin.tabs.panel>

                <x-admin.tabs.panel name="goftino">
                    @include('admin.system.settings.partials.general.goftino')
                </x-admin.tabs.panel>

                <x-admin.tabs.panel name="sms-panel">
                    @include('admin.system.settings.partials.general.sms-panel')
                </x-admin.tabs.panel>

                <x-admin.tabs.panel name="payment-gateway">
                    @include('admin.system.settings.partials.general.payment-gateway')
                </x-admin.tabs.panel>
            </x-admin.tabs>
        </x-admin.page>
        <input type="hidden" name="active_tab" id="active_tab_input" value="">

        <x-admin.form.save-button/>
    </x-admin.form>
@endsection

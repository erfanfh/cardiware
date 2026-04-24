@extends('admin.layout')

@section('title', 'مدیریت چیدمان سایدبار')

@section('content')
    <x-admin.form action="{{ route('admin.page-layouts.update', $layout['key']) }}" method="put">
        <x-admin.page>
            <input type="hidden" name="layout_state" data-layout-state-input>

            <div class="space-y-6">
                @foreach($layoutRows as $row)
                    <div class="grid grid-cols-1 gap-6 xl:grid-cols-12 items-stretch">
                        @foreach($row['areas'] as $area)
                            <x-admin.modules.page-layout.area-box
                                :areaKey="$area['key']"
                                :title="$area['title']"
                                :description="$area['description']"
                                :count="count($area['elements'])"
                                :empty="empty($area['elements'])"
                                :width="$area['width']"
                            >
                                @foreach($area['elements'] as $element)
                                    <x-admin.modules.page-layout.element-card
                                        :elementKey="$element['key']"
                                        :title="$element['title']"
                                        :type="$element['type_label']"
                                        :typeColor="$element['type_color']"
                                        :active="$element['is_active']"
                                        :inputName="'elements[' . $element['key'] . '][is_active]'"
                                        :allowedAreas="$element['allowed_areas']"
                                        :settingsUrl="$element['settings_url']"
                                    />
                                @endforeach
                            </x-admin.modules.page-layout.area-box>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </x-admin.page>

        <x-admin.form.save-button/>
    </x-admin.form>
@endsection

@push('scripts')
    @vite(['resources/js/admin/page-layout.js'])
@endpush

@push('styles')
    @vite(['resources/css/admin/page-layout.css'])
@endpush

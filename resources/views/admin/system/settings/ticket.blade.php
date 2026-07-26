@extends('admin.layout')

@section('title', 'تنظیمات تیکت')

@section('content')
    <x-admin.form action="{{ route('admin.settings.ticket.store') }}" method="put">
        <x-admin.page>
            <div class="space-y-6">
                <x-admin.metabox title="نکات ارسال تیکت">
                    <x-admin.field>
                        <x-admin.form.repeater
                            :items="get_setting('ticket', 'ticket_submission_tips')"
                            name="ticket_submission_tips"
                            title="نکته ها"
                            addText="افزودن نکته"
                            headingKey="text"
                            :defaultItem="[
                                'text' => '',
                            ]"
                        >
                            <div class="grid grid-cols-1 gap-4">
                                <x-admin.field label="متن">
                                    <x-admin.form.input x-model="it.text"
                                                        x-bind:name="field(i, 'text')"/>
                                </x-admin.field>
                            </div>
                        </x-admin.form.repeater>
                    </x-admin.field>
                </x-admin.metabox>
            </div>
        </x-admin.page>

        <x-admin.form.save-button/>
    </x-admin.form>
@endsection

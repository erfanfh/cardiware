@php
    $savedItems = $product
        ? $product->teamMembers
            ->map(fn ($productTeamMember) => [
                'id' => $productTeamMember->id,
                'team_member_id' => (string) $productTeamMember->teamMember->id,
                'team_member_title' => "{$productTeamMember->teamMember->first_name} {$productTeamMember->teamMember->last_name} ({$productTeamMember->teamMember->mobile})",
                'sort_order' => (int) ($productTeamMember->sort_order ?? 0),
            ])
            ->values()
            ->all()
        : [];

    $items = old('team_members', $savedItems);
@endphp

<x-admin.form.repeater
    name="team_members"
    :items="$items"
    :defaultItem="[
        'id' => null,
        'team_member_id' => '',
        'team_member_title' => '',
        'sort_order' => 0,
    ]"
    addText="افزودن عضو"
    emptyText="هنوز عضوی اضافه نشده است."
    headingPlaceholder="عضو جدید"
>
    <div class="space-y-4">
        <input
            type="hidden"
            x-bind:name="field(i, 'id')"
            x-model="it.id"
        >

        <input
            type="hidden"
            x-bind:name="field(i, 'sort_order')"
            x-bind:value="i"
        >

        <div class="grid grid-cols-1 gap-4">
            <x-admin.field>
                <x-admin.form.async-select
                    x-bind:name="field(i, 'team_member_id')"
                    x-bind:data-initial-option="
                        it.team_member_id ? JSON.stringify({
                            value: String(it.team_member_id),
                            label: it.team_member_title || ('#' + it.team_member_id)
                        }) : ''
                    "
                    x-on:mw-async-select-change="
                        it.team_member_id = $event.detail.value ?? '';
                        it.team_member_title = $event.detail.label ?? '';
                    "
                    label="عضو تیم"
                    :multiple="false"
                    :search-url="route('admin.team-members.ajax-search')"
                    placeholder="عضو تیم را انتخاب کنید..."
                />
            </x-admin.field>
        </div>
    </div>
</x-admin.form.repeater>

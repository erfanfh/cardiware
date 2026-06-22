<x-admin.field required>
    <x-admin.form.editor
        name="installation_step_body"
        :value="old('installation_step_body', $product?->installationSteps?->first()?->body)"
        preset="full"
        height="500"
    />
</x-admin.field>

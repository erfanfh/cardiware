// Each Alpine repeater clone needs its own TinyMCE identity and lifecycle.
export function repeaterEditor() {
    return {
        init() {
            const source = this.$el.querySelector('textarea[data-editor]');
            source.id = `editor_repeater_${window.crypto.randomUUID()}`;
            source.dataset.editorKey = source.id;

            this.$nextTick(() => {
                if (this.$el.isConnected) {
                    window.AdminEditor.init(this.$el);
                }
            });
        },

        destroy() {
            window.AdminEditor.destroy(this.$el);
        },
    };
}

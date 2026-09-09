import tinymce from 'tinymce';
import {analyzeReadability, extractReadabilityBlocks} from './readability.js';

export function readabilityComponent(fieldName = 'body') {
    let timer;
    let field;
    let editor;
    let onAddEditor;
    const events = 'init input change SetContent Undo Redo';
    const labels = {good: 'خوب', improvement: 'نیاز به بهبود', problem: 'نیاز به اصلاح', neutral: 'در انتظار متن'};
    const colors = {
        good: 'bg-emerald-50 text-emerald-700 border-emerald-200',
        improvement: 'bg-amber-50 text-amber-700 border-amber-200',
        problem: 'bg-red-50 text-red-700 border-red-200',
        neutral: 'bg-slate-50 text-slate-600 border-slate-200',
    };
    let schedule;
    return {
        readability: analyzeReadability([]),
        init() {
            if (!this.readabilityEnabled) return;
            this.$nextTick(() => {
                field = this.$el.closest('form')?.querySelector(`textarea[name="${CSS.escape(fieldName)}"]`);
                if (!field) return;
                schedule = () => {
                    clearTimeout(timer);
                    timer = setTimeout(() => this.refreshReadability(), 400);
                };
                const attach = instance => {
                    if (instance.getElement() !== field) return;
                    editor?.off(events, schedule);
                    editor = instance;
                    editor.on(events, schedule);
                    schedule();
                };
                onAddEditor = event => attach(event.editor);
                tinymce.on('AddEditor', onAddEditor);
                const instance = tinymce.get(field.id);
                if (instance) attach(instance);
                field.addEventListener('input', schedule);
                field.addEventListener('change', schedule);
                this.refreshReadability();
            });
        },
        refreshReadability() {
            const current = field && tinymce.get(field.id);
            const html = current?.initialized ? current.getContent() : field?.value || '';
            this.readability = analyzeReadability(extractReadabilityBlocks(html));
            this.$dispatch('readability-updated', this.readability.state === 'ready' ? this.readability.issues : null);
        },
        readabilityLabel(status) { return labels[status] || labels.neutral; },
        readabilityColor(status) { return colors[status] || colors.neutral; },
        destroy() {
            clearTimeout(timer);
            if (onAddEditor) tinymce.off('AddEditor', onAddEditor);
            if (schedule) {
                editor?.off(events, schedule);
                field?.removeEventListener('input', schedule);
                field?.removeEventListener('change', schedule);
            }
        },
    };
}

document.addEventListener('alpine:init', () => {
    window.Alpine.data('seoReadability', readabilityComponent);
});

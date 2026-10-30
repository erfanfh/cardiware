export function productLandingEditor(content) {
    return {
        landingContent: content,
        dirty: false,
        init() {
            this.$watch('landingContent', () => { this.dirty = true; });
        },
        clone(value) {
            return JSON.parse(JSON.stringify(value));
        },
        mediaPreview(path) {
            if (!path) return '';
            return /^(https?:\/\/|\/)/i.test(path) ? path : '/' + path;
        },
        add(list, example) {
            if (list.length < 200) list.push(this.clone(example));
        },
        move(list, index, offset) {
            const target = index + offset;
            if (target < 0 || target >= list.length) return;
            const [item] = list.splice(index, 1);
            list.splice(target, 0, item);
        },
    };
}

if (typeof window !== 'undefined') window.productLandingEditor = productLandingEditor;

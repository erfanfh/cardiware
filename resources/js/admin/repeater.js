import Sortable from 'sortablejs'

window.mwRepeater = function (opts) {
    const makeKey = () => Date.now().toString(36) + Math.random().toString(36).slice(2)

    return {
        items: Array.isArray(opts.items) ? opts.items : [],
        name: opts.name || 'items',
        defaultItem: opts.defaultItem || {},
        sortable: opts.sortable !== false,

        __currentIndex: 0,

        init() {
            this.items = this.items.map((it) => {
                if (typeof it !== 'object' || it === null) it = {}
                if (!it.__key) it.__key = makeKey()
                if (it._open === undefined) it._open = true
                return it
            })

            if (this.sortable) this.initSortable()
            this.reorder()
        },

        // ✅ مهم: این باعث میشه field('title') بدون i هم درست کار کنه
        setCurrent(i) {
            this.__currentIndex = i
        },

        // ✅ field فقط key می‌گیرد (مثل ACF / WP Repeater)
        field(key, i = null) {
            const idx = (i === null ? this.__currentIndex : i)
            return `${this.name}[${idx}][${key}]`
        },

        initSortable() {
            new Sortable(this.$refs.list, {
                animation: 150,
                handle: '.drag-handle',

                onEnd: (evt) => {
                    const moved = this.items.splice(evt.oldIndex, 1)[0]
                    this.items.splice(evt.newIndex, 0, moved)

                    // ✅ بعد از drag/drop index ها عوض میشن → فقط reorder کافی است
                    this.reorder()
                },
            })
        },

        add() {
            const it = JSON.parse(JSON.stringify(this.defaultItem || {}))
            it.__key = makeKey()
            it._open = true
            this.items.push(it)
            this.reorder()
        },

        remove(i) {
            this.items.splice(i, 1)
            this.reorder()
        },

        reorder() {
            this.items.forEach((it, idx) => {
                it.sort_order = idx
            })
        },

        toggle(i) {
            this.items[i]._open = !this.items[i]._open
        },

        collapseAll() {
            this.items.forEach(it => it._open = false)
        },

        expandAll() {
            this.items.forEach(it => it._open = true)
        },
    }
}

import Sortable from 'sortablejs'

window.mwRepeater = function (opts = {}) {
    const makeKey = () => {
        if (window.crypto && typeof window.crypto.randomUUID === 'function') {
            return window.crypto.randomUUID()
        }

        return Date.now().toString(36) + Math.random().toString(36).slice(2)
    }

    const deepClone = (value) => {
        return JSON.parse(JSON.stringify(value || {}))
    }

    return {
        items: Array.isArray(opts.items) ? deepClone(opts.items) : [],
        name: opts.name || 'items',
        defaultItem: opts.defaultItem || {},
        sortable: opts.sortable !== false,

        __sortableInstance: null,
        __renderNonce: 0,

        init() {
            this.items = this.items.map((it, index) => {
                const item = (typeof it === 'object' && it !== null) ? deepClone(it) : {}

                if (!item.__key) {
                    item.__key = makeKey()
                }

                if (item._open === undefined) {
                    item._open = false
                }

                if (item.sort_order === undefined || item.sort_order === null || item.sort_order === '') {
                    item.sort_order = index
                }

                return item
            })

            this.reorder()

            this.$nextTick(() => {
                if (this.sortable) {
                    this.initSortable()
                }
            })
        },

        initSortable() {
            if (!this.$refs.list) return

            if (this.__sortableInstance) {
                this.__sortableInstance.destroy()
                this.__sortableInstance = null
            }

            this.__sortableInstance = new Sortable(this.$refs.list, {
                animation: 180,
                handle: '.drag-handle',
                draggable: '[data-repeater-item]',
                ghostClass: 'repeater-sortable-ghost',
                chosenClass: 'repeater-sortable-chosen',
                dragClass: 'repeater-sortable-drag',

                onEnd: (evt) => {
                    if (evt.oldIndex === evt.newIndex) return
                    if (evt.oldIndex == null || evt.newIndex == null) return

                    const moved = this.items.splice(evt.oldIndex, 1)[0]
                    this.items.splice(evt.newIndex, 0, moved)

                    this.reorder()
                    this.refreshDom()
                },
            })
        },

        add(position = 'end') {
            const item = deepClone(this.defaultItem || {})

            item.__key = makeKey()
            item._open = true

            if (position === 'start') {
                this.items.unshift(item)
            } else {
                this.items.push(item)
            }

            this.reorder()
            this.refreshDom()
        },

        remove(index) {
            this.items.splice(index, 1)
            this.reorder()
            this.refreshDom()
        },

        reorder() {
            this.items.forEach((item, index) => {
                item.sort_order = index
            })
        },

        refreshDom() {
            this.__renderNonce++

            this.$nextTick(() => {
                if (this.sortable) {
                    this.initSortable()
                }
            })
        },

        toggle(index) {
            this.items[index]._open = !this.items[index]._open
        },

        collapseAll() {
            this.items.forEach((item) => {
                item._open = false
            })

            this.refreshDom()
        },

        expandAll() {
            this.items.forEach((item) => {
                item._open = true
            })

            this.refreshDom()
        },

        field(index, key = null) {
            let base = `${this.name}[${index}]`

            if (!key) {
                return base
            }

            String(key)
                .split('.')
                .filter(Boolean)
                .forEach((part) => {
                    base += `[${part}]`
                })

            return base
        },
    }
}

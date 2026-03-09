import TomSelect from "tom-select";
import "tom-select/dist/css/tom-select.css";
import collapse from '@alpinejs/collapse'
import './repeater.js'

document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('Sidebar');
    const toggleBtn = document.getElementById('SidebarToggle');
    const backdrop = document.getElementById('SidebarBackdrop');

    if (!sidebar || !toggleBtn || !backdrop) {
        return;
    }

    function openSidebar() {
        sidebar.classList.add('sidebar--open');
        backdrop.classList.add('sidebar-backdrop--visible');
    }

    function closeSidebar() {
        sidebar.classList.remove('sidebar--open');
        backdrop.classList.remove('sidebar-backdrop--visible');
    }

    toggleBtn.addEventListener('click', () => {
        const isOpen = sidebar.classList.contains('sidebar--open');
        if (isOpen) {
            closeSidebar();
        } else {
            openSidebar();
        }
    });

    backdrop.addEventListener('click', closeSidebar);

    window.addEventListener('resize', () => {
        if (window.innerWidth > 1024) {
            sidebar.classList.remove('sidebar--open');
            backdrop.classList.remove('sidebar-backdrop--visible');
        }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.ts-multiple').forEach((el) => {
        new TomSelect(el, {
            create: false,
            plugins: {
                'remove_button': {
                    title: 'حذف',
                },
                'checkbox_options': {
                    'checkedClassNames': ['ts-checked'],
                    'uncheckedClassNames': ['ts-unchecked'],
                }
            },
            sortField: {
                field: "text",
                direction: "asc"
            },
        });
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const select = document.querySelector('#category-select');

    const ts = new TomSelect(select, {
        valueField: 'id',
        labelField: 'title',
        searchField: 'title',
        create: false,
        plugins: {
            'remove_button': {
                title: 'حذف',
            },
            'checkbox_options': {
                'checkedClassNames': ['ts-checked'],
                'uncheckedClassNames': ['ts-unchecked'],
            }
        },
        load: function (query, callback) {
            if (!query.length) return callback();
            fetch(`/admin/categories/fetch?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(json => {
                    callback(json);
                }).catch(() => {
                callback();
            });
        }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const parentSelect = document.querySelector('#parent-category-select');

    const ts = new TomSelect(parentSelect, {
        valueField: 'id',
        labelField: 'title',
        searchField: 'title',
        create: false,
        plugins: {
            'remove_button': {
                title: 'حذف',
            },
            'checkbox_options': {
                'checkedClassNames': ['ts-checked'],
                'uncheckedClassNames': ['ts-unchecked'],
            }
        },
        load: function (query, callback) {
            if (!query.length) return callback();
            fetch(`/admin/categories/fetch?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(json => {
                    callback(json);
                }).catch(() => {
                callback();
            });
        }
    });
});

Livewire.on('init-category-select', () => {

    const el = document.getElementById('category-select');

    if (el.tomselect) {
        el.tomselect.destroy();
    }

    new TomSelect(el, {
        valueField: 'id',
        labelField: 'title',
        searchField: 'title',
        plugins: {
            'remove_button': {
                title: 'حذف',
            },
            'checkbox_options': {
                'checkedClassNames': ['ts-checked'],
                'uncheckedClassNames': ['ts-unchecked'],
            }
        },
        load: function (query, callback) {
            if (!query.length) return callback();
            fetch(`/admin/categories/fetch?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(json => {
                    callback(json);
                }).catch(() => {
                callback();
            });
        }
    });

});

document.addEventListener('DOMContentLoaded', () => {
    const select = document.querySelector('#tag-select');

    const ts = new TomSelect(select, {
        valueField: 'id',
        labelField: 'title',
        searchField: 'title',
        create: false,
        plugins: {
            'remove_button': {
                title: 'حذف',
            },
            'checkbox_options': {
                'checkedClassNames': ['ts-checked'],
                'uncheckedClassNames': ['ts-unchecked'],
            }
        },
        load: function (query, callback) {
            if (!query.length) return callback();
            fetch(`/admin/tags/fetch?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(json => {
                    callback(json);
                }).catch(() => {
                callback();
            });
        }
    });
});

document.addEventListener('alpine:init', () => {
    Alpine.plugin(collapse)
})


/*** ================== Start Slug management ================== ***/
// --- slugify (FA+EN) ---
window.mwSlugify = function (input) {
    if (!input) return '';
    let s = String(input).trim().toLowerCase();

    const fa = '۰۱۲۳۴۵۶۷۸۹';
    const ar = '٠١٢٣٤٥٦٧٨٩';
    s = s.replace(/[۰-۹]/g, d => fa.indexOf(d))
        .replace(/[٠-٩]/g, d => ar.indexOf(d));

    s = s.replace(/ي/g, 'ی').replace(/ك/g, 'ک');
    s = s.replace(/[\s_]+/g, '-');
    s = s.replace(/[^a-z0-9\u0600-\u06FF\-]+/g, '');
    s = s.replace(/-+/g, '-').replace(/^-|-$/g, '');
    return s;
};

// --- WP-like editor ---
window.mwWpSlugEditor = function (opts) {
    return {
        title: opts.initialTitle || '',
        slug: opts.initialSlug || '',
        _slugBeforeEdit: '',
        editingSlug: false,
        manualSlug: false,      // وقتی کاربر ویرایش کنه true میشه
        checking: false,
        uniqueOk: true,
        uniqueMsg: '',
        suggestedSlug: '',

        _debounce: null,

        init() {
            // مثل وردپرس: در edit اگر slug وجود دارد => اتومات تغییر نکند
            if (this.slug && this.slug.length > 0) {
                this.manualSlug = true;
                this.uniqueOk = true;
            } else {
                // create: auto
                this.slug = window.mwSlugify(this.title);
            }

            this.$watch('title', (v) => {
                if (this.manualSlug) return;
                this.slug = window.mwSlugify(v);
                this.queueCheck();
            });

            this.$watch('slug', () => this.queueCheck());

            // اولین چک
            this.queueCheck();
        },

        startEdit() {
            this.editingSlug = true;
            this.manualSlug = true;
            this._slugBeforeEdit = this.slug;
            this.$nextTick(() => this.$refs.slugInput?.focus());
        },

        applyEdit() {
            this.slug = window.mwSlugify(this.slug);
            this.editingSlug = false;
            this.queueCheck(true);
        },

        cancelEdit() {
            this.slug = this._slugBeforeEdit;
            this.editingSlug = false;
            this.queueCheck(true);
        },

        resetAuto() {
            this.manualSlug = false;
            this.slug = window.mwSlugify(this.title);
            this.editingSlug = false;
            this.queueCheck(true);
        },

        useSuggested() {
            if (!this.suggestedSlug) return;
            this.slug = this.suggestedSlug;
            this.editingSlug = false;
            this.queueCheck(true);
        },

        queueCheck(immediate = false) {
            if (!opts.checkUrl) return; // اگر endpoint ندی، فقط UI داخلی کار می‌کند

            clearTimeout(this._debounce);
            this._debounce = setTimeout(() => this.checkUnique(), immediate ? 0 : 350);
        },

        async checkUnique() {
            if (!opts.checkUrl) return;

            const slug = (this.slug || '').trim();
            if (!slug) {
                this.uniqueOk = true;
                this.uniqueMsg = '';
                this.suggestedSlug = '';
                return;
            }

            this.checking = true;
            this.uniqueMsg = '';
            this.suggestedSlug = '';

            try {
                const url = new URL(opts.checkUrl, window.location.origin);
                url.searchParams.set('slug', slug);
                if (opts.currentId) url.searchParams.set('ignore', opts.currentId);

                const res = await fetch(url.toString(), {
                    headers: {'Accept': 'application/json'}
                });
                const data = await res.json();

                this.uniqueOk = !!data.unique;
                this.uniqueMsg = data.message || (data.unique ? '' : 'اسلاگ تکراری است');
                this.suggestedSlug = data.suggested_slug || '';
            } catch (e) {
                // اگر endpoint در دسترس نبود، مزاحم UX نشیم
                this.uniqueOk = true;
                this.uniqueMsg = '';
                this.suggestedSlug = '';
            } finally {
                this.checking = false;
            }
        }
    }
};
/*** ================== End Slug management ================== ***/

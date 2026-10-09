import TomSelect from "tom-select";
import "tom-select/dist/css/tom-select.css";
import collapse from '@alpinejs/collapse'
import './toast.js'
import './commerce-analytics.js'
import './system-reports.js'
import './poll-analytics.js'
import './repeater.js'
import './colorpicker.js'
import './async-select.js'
import './editor.js'
import './seo/readability-component.js'
import './icon-picker.js'
import './mobile.js'
import './post-edit-lock.js'

window.addEventListener('admin-inline-edit-started', () => {
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            const editForm = document.querySelector('[data-admin-inline-edit-form]');

            if (!editForm) {
                return;
            }

            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            editForm.scrollIntoView({
                behavior: reduceMotion ? 'auto' : 'smooth',
                block: 'start',
            });

            const firstField = editForm.querySelector(
                'input:not([type="hidden"]):not([disabled]), textarea:not([disabled]), select:not([disabled])'
            );

            firstField?.focus({preventScroll: true});
        });
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('Sidebar');
    const toggleBtn = document.getElementById('SidebarToggle');
    const backdrop = document.getElementById('SidebarBackdrop');
    const closeBtn = document.getElementById('SidebarClose');

    if (!sidebar || !toggleBtn || !backdrop) {
        return;
    }

    function openSidebar() {
        sidebar.classList.add('sidebar--open');
        backdrop.classList.add('sidebar-backdrop--visible');
        toggleBtn.setAttribute('aria-expanded', 'true');
        toggleBtn.setAttribute('aria-label', 'بستن منو');
        sidebar.inert = false;
        document.body.classList.add('admin-sidebar-open');
        closeBtn?.focus();
    }

    function closeSidebar(restoreFocus = false) {
        sidebar.classList.remove('sidebar--open');
        backdrop.classList.remove('sidebar-backdrop--visible');
        toggleBtn.setAttribute('aria-expanded', 'false');
        toggleBtn.setAttribute('aria-label', 'باز کردن منو');
        document.body.classList.remove('admin-sidebar-open');
        sidebar.inert = window.innerWidth < 1024;
        if (restoreFocus) toggleBtn.focus();
    }

    sidebar.inert = window.innerWidth < 1024;

    toggleBtn.addEventListener('click', () => {
        const isOpen = sidebar.classList.contains('sidebar--open');
        if (isOpen) {
            closeSidebar(true);
        } else {
            openSidebar();
        }
    });

    closeBtn?.addEventListener('click', () => closeSidebar(true));
    backdrop.addEventListener('click', () => closeSidebar(true));
    document.addEventListener('keydown', (event) => {
        if (!sidebar.classList.contains('sidebar--open')) return;

        if (event.key === 'Escape') {
            closeSidebar(true);
            return;
        }

        if (event.key === 'Tab') {
            const focusable = [...sidebar.querySelectorAll('a[href], button:not([disabled]), input:not([disabled])')]
                .filter((element) => element.getClientRects().length > 0);
            if (!focusable.length) return;

            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) closeSidebar();
        else sidebar.inert = !sidebar.classList.contains('sidebar--open');
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

    if (!select) return;

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

    if (!parentSelect) return;

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

    if (!el) return;

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

    if (!select) return;

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

document.addEventListener('DOMContentLoaded', () => {
    const select = document.querySelector('#author-select');

    if (!select) return;

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
            fetch(`/admin/users/fetch?q=${encodeURIComponent(query)}`)
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

            const scope = (opts.scope || '').trim();

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
                url.searchParams.set('scope', scope);
                if (opts.currentId) url.searchParams.set('ignore', opts.currentId);

                const res = await fetch(url.toString(), {
                    headers: {'Accept': 'application/json'}
                });
                const data = await res.json();

                this.uniqueOk = !!data.available;
                this.uniqueMsg = data.message || (data.available ? '' : 'اسلاگ تکراری است');
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

// --- Form confirmation befor closing ---
let formChanged = false;
const confirmationForm = document.querySelector('.confirmationForm');

confirmationForm?.addEventListener("input", function () {
    formChanged = true;
});
window.addEventListener("beforeunload", function (event) {
    if (formChanged) {
        event.preventDefault();
        event.returnValue = "";
    }
});
confirmationForm?.addEventListener("submit", function () {
    formChanged = false;
});
/*** ================== End Form confirmation befor closing ================== ***/

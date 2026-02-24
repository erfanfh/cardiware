import TomSelect from "tom-select";
import "tom-select/dist/css/tom-select.css";

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
                'remove_button' :{
                    title:'حذف',
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
            'remove_button' :{
                title:'حذف',
            },
            'checkbox_options': {
                'checkedClassNames': ['ts-checked'],
                'uncheckedClassNames': ['ts-unchecked'],
            }
        },
        load: function(query, callback) {
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
            'remove_button' :{
                title:'حذف',
            },
            'checkbox_options': {
                'checkedClassNames': ['ts-checked'],
                'uncheckedClassNames': ['ts-unchecked'],
            }
        },
        load: function(query, callback) {
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
            'remove_button' :{
                title:'حذف',
            },
            'checkbox_options': {
                'checkedClassNames': ['ts-checked'],
                'uncheckedClassNames': ['ts-unchecked'],
            }
        },
        load: function(query, callback) {
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
            'remove_button' :{
                title:'حذف',
            },
            'checkbox_options': {
                'checkedClassNames': ['ts-checked'],
                'uncheckedClassNames': ['ts-unchecked'],
            }
        },
        load: function(query, callback) {
            if (!query.length) return callback();
            fetch(`/admin/tags/search?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(json => {
                    callback(json);
                }).catch(() => {
                callback();
            });
        }
    });
});

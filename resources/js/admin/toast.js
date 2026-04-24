(function () {
    const MAX_TOASTS = 4;
    const DEFAULT_DURATION = 3200;

    function ensureContainer() {
        let container = document.getElementById('admin-toast-container');

        if (container) {
            return container;
        }

        container = document.createElement('div');
        container.id = 'admin-toast-container';
        container.className =
            'fixed top-4 left-4 z-[9999] flex w-full max-w-sm flex-col gap-3 pointer-events-none';

        document.body.appendChild(container);

        return container;
    }

    function icon(type) {
        if (type === 'success') {
            return `
                <div class="flex h-9 w-9 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                    ✓
                </div>
            `;
        }

        return `
            <div class="flex h-9 w-9 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
                !
            </div>
        `;
    }

    function toastClass(type) {
        if (type === 'success') {
            return 'border-emerald-200';
        }

        return 'border-rose-200';
    }

    function show(message, type = 'success', duration = DEFAULT_DURATION) {
        const container = ensureContainer();

        while (container.children.length >= MAX_TOASTS) {
            container.removeChild(container.lastChild);
        }

        const item = document.createElement('div');

        item.className = `
            pointer-events-auto translate-x-10 opacity-0 transition-all duration-300
            rounded-3xl border bg-white shadow-2xl ${toastClass(type)}
        `;

        item.innerHTML = `
            <div class="flex items-start gap-3 p-4">
                ${icon(type)}

                <div class="min-w-0 flex-1">
                    <div class="text-sm font-bold text-slate-800">
                        ${type === 'success' ? 'موفق' : 'خطا'}
                    </div>

                    <div class="mt-1 text-sm leading-6 text-slate-600 break-words">
                        ${message}
                    </div>
                </div>

                <button
                    type="button"
                    class="flex h-8 w-8 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                >
                    ×
                </button>
            </div>
        `;

        const closeButton = item.querySelector('button');

        const remove = () => {
            item.classList.remove('translate-x-0', 'opacity-100');
            item.classList.add('translate-x-10', 'opacity-0');

            setTimeout(() => {
                item.remove();
            }, 250);
        };

        closeButton.addEventListener('click', remove);

        container.prepend(item);

        requestAnimationFrame(() => {
            item.classList.remove('translate-x-10', 'opacity-0');
            item.classList.add('translate-x-0', 'opacity-100');
        });

        setTimeout(remove, duration);
    }

    window.toast = {
        success(message, duration) {
            show(message, 'success', duration);
        },

        error(message, duration) {
            show(message, 'error', duration);
        },
    };
})();

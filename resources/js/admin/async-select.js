import TomSelect from 'tom-select';

function debounce(fn, delay = 300) {
    let timeout = null;

    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn.apply(this, args), delay);
    };
}

function buildUrl(url, query) {
    const finalUrl = new URL(url, window.location.origin);
    finalUrl.searchParams.set('q', query);
    return finalUrl.toString();
}

function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value ?? '';
    return div.innerHTML;
}

document.addEventListener('alpine:init', () => {
    Alpine.data('mwAsyncSelect', (config = {}) => ({
        tom: null,
        element: null,

        multiple: !!config.multiple,
        placeholder: config.placeholder || 'جستجو و انتخاب...',
        searchUrl: config.searchUrl || '',
        minChars: Number(config.minChars || 2),
        maxOptions: Number(config.maxOptions || 20),
        maxItems: config.maxItems ?? null,
        allowClear: !!config.allowClear,
        initialOptions: Array.isArray(config.initialOptions) ? config.initialOptions : [],

        isLoading: false,
        hasError: false,

        init(selectElement) {
            if (!selectElement || selectElement.tomselect) {
                return;
            }

            this.element = selectElement;

            this.tom = new TomSelect(selectElement, {
                valueField: 'value',
                labelField: 'label',
                searchField: ['label'],
                maxOptions: this.maxOptions,
                maxItems: this.maxItems,
                preload: false,
                create: false,
                persist: false,
                hidePlaceholder: false,
                closeAfterSelect: !this.multiple,
                selectOnTab: true,
                plugins: this.multiple ? ['remove_button'] : [],
                render: {
                    no_results: () => this.renderNoResults(),
                    loading: () => this.renderLoadingDropdown(),
                    option: (data) => this.renderOption(data),
                    item: (data) => this.renderItem(data),
                    option_create: () => this.renderHint('امکان ایجاد آیتم جدید فعال نیست.'),
                },
                shouldLoad: (query) => {
                    this.hasError = false;
                    this.syncLoadingIndicator();

                    return query.trim().length >= this.minChars;
                },
                load: debounce((query, callback) => {
                    if (!this.searchUrl || query.trim().length < this.minChars) {
                        callback();
                        return;
                    }

                    this.isLoading = true;
                    this.hasError = false;
                    this.syncLoadingIndicator();

                    fetch(buildUrl(this.searchUrl, query), {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    })
                        .then(async (response) => {
                            if (!response.ok) {
                                throw new Error(`Request failed with status ${response.status}`);
                            }

                            return response.json();
                        })
                        .then((json) => {
                            this.isLoading = false;
                            this.hasError = false;
                            this.syncLoadingIndicator();

                            callback(Array.isArray(json.items) ? json.items : []);
                        })
                        .catch(() => {
                            this.isLoading = false;
                            this.hasError = true;
                            this.syncLoadingIndicator();

                            callback();
                            this.refreshDropdownState();
                        });
                }, 300),
                onInitialize: () => {
                    this.seedInitialOptions();
                    this.decorateControl();
                    this.syncLoadingIndicator();
                },
                onItemAdd: () => {
                    if (!this.multiple || !this.tom) {
                        return;
                    }

                    this.tom.setTextboxValue('');
                    this.tom.lastValue = '';
                    this.tom.clearActiveOption();
                    this.tom.refreshOptions(false);

                    if (this.tom.control_input) {
                        this.tom.control_input.value = '';
                        this.tom.control_input.dispatchEvent(new Event('input', {bubbles: true}));
                    }

                    this.tom.close();
                },
                onType: (query) => {
                    if (query.trim().length > 0 && query.trim().length < this.minChars) {
                        this.hasError = false;
                        this.refreshDropdownState(
                            this.renderHint(`حداقل ${this.minChars} کاراکتر وارد کنید.`)
                        );
                        return;
                    }

                    if (!query.trim().length) {
                        this.hasError = false;
                        this.refreshDropdownState(
                            this.renderHint('برای جستجو شروع به تایپ کنید.')
                        );
                    }
                },
                onDropdownOpen: () => {
                    const query = this.tom?.control_input?.value?.trim() || '';

                    if (!query.length) {
                        this.refreshDropdownState(
                            this.renderHint('برای جستجو شروع به تایپ کنید.')
                        );
                        return;
                    }

                    if (query.length < this.minChars) {
                        this.refreshDropdownState(
                            this.renderHint(`حداقل ${this.minChars} کاراکتر وارد کنید.`)
                        );
                        return;
                    }

                    if (this.hasError) {
                        this.refreshDropdownState(this.renderError());
                    }
                },
            });
        },

        seedInitialOptions() {
            if (!this.tom || !this.initialOptions.length) {
                return;
            }

            this.initialOptions.forEach((item) => {
                if (!item || item.value === undefined || item.label === undefined) {
                    return;
                }

                this.tom.addOption({
                    value: String(item.value),
                    label: String(item.label),
                });
            });

            this.tom.refreshOptions(false);
        },

        decorateControl() {
            if (!this.tom) {
                return;
            }

            const control = this.tom.control;
            if (!control) {
                return;
            }

            if (!control.querySelector('.mw-async-select__loading-indicator')) {
                const loadingIndicator = document.createElement('div');
                loadingIndicator.className = 'mw-async-select__loading-indicator';
                loadingIndicator.innerHTML = `<span class="mw-async-select__spinner"></span>`;
                loadingIndicator.style.display = 'none';
                control.appendChild(loadingIndicator);
            }

            if (!this.multiple && this.allowClear && !control.querySelector('.mw-async-select__clear')) {
                const clearButton = document.createElement('button');
                clearButton.type = 'button';
                clearButton.className = 'mw-async-select__clear';
                clearButton.tabIndex = -1;
                clearButton.innerHTML = '&times;';

                clearButton.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();

                    this.tom.clear(true);
                    this.tom.refreshOptions(false);
                });

                control.appendChild(clearButton);
            }
        },

        syncLoadingIndicator() {
            if (!this.tom?.control) {
                return;
            }

            const indicator = this.tom.control.querySelector('.mw-async-select__loading-indicator');

            if (!indicator) {
                return;
            }

            indicator.style.display = this.isLoading ? 'inline-flex' : 'none';
        },

        refreshDropdownState(html = null) {
            if (!this.tom?.dropdown_content) {
                return;
            }

            if (html) {
                this.tom.dropdown_content.innerHTML = html;
                return;
            }

            if (this.hasError) {
                this.tom.dropdown_content.innerHTML = this.renderError();
            }
        },

        renderOption(data) {
            const label = escapeHtml(data.label ?? '');
            const badge = data.badge
                ? `<span class="mw-async-select__option-badge">${escapeHtml(data.badge)}</span>`
                : '';

            return `
                <div class="mw-async-select__option">
                    <span class="mw-async-select__option-label">${label}</span>
                    ${badge}
                </div>
            `;
        },

        renderItem(data) {
            return `<div>${escapeHtml(data.label ?? '')}</div>`;
        },

        renderNoResults() {
            if (this.hasError) {
                return this.renderError();
            }

            return `
                <div class="mw-async-select__state mw-async-select__state--empty">
                    <svg class="mw-async-select__state-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 104.473 8.699l2.664 2.664a.75.75 0 101.06-1.06l-2.663-2.664A5.5 5.5 0 009 3.5zM5 9a4 4 0 118 0 4 4 0 01-8 0z" clip-rule="evenodd"/>
                    </svg>
                    <span>نتیجه‌ای پیدا نشد.</span>
                </div>
            `;
        },

        renderLoadingDropdown() {
            return `
                <div class="mw-async-select__state mw-async-select__state--loading">
                    <span class="mw-async-select__spinner" aria-hidden="true"></span>
                    <span>در حال جستجو...</span>
                </div>
            `;
        },

        renderError() {
            return `
                <div class="mw-async-select__state mw-async-select__state--error">
                    <svg class="mw-async-select__state-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M18 10A8 8 0 114 4.126V3.75a.75.75 0 00-1.5 0v1.69A8 8 0 0018 10zm-8.75-3.25a.75.75 0 011.5 0v3.5a.75.75 0 01-1.5 0v-3.5zm.75 7a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                    </svg>
                    <span>خطا در دریافت اطلاعات. دوباره تلاش کنید.</span>
                </div>
            `;
        },

        renderHint(text) {
            return `
                <div class="mw-async-select__state mw-async-select__state--hint">
                    <svg class="mw-async-select__state-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M18 10A8 8 0 112 10a8 8 0 0116 0zm-7.25-3a.75.75 0 10-1.5 0 .75.75 0 001.5 0zM10 8.75a.75.75 0 00-.75.75v3.5a.75.75 0 001.5 0V9.5a.75.75 0 00-.75-.75z" clip-rule="evenodd"/>
                    </svg>
                    <span>${escapeHtml(text)}</span>
                </div>
            `;
        },
    }));
});

window.downloadsBlock = function () {
    return {
        _buttonTimers: new WeakMap(),

        setButtonCopiedState(button) {
            if (!button) {
                return;
            }

            const label = button.querySelector('.js-copy-text');

            if (!label) {
                return;
            }

            const defaultText = button.dataset.defaultText ?? label.textContent ?? '';
            const copiedText = button.dataset.copiedText ?? 'کپی شد';
            const oldTimer = this._buttonTimers.get(button);

            if (oldTimer) {
                clearTimeout(oldTimer);
            }

            button.classList.add('is-copied');
            label.textContent = copiedText;

            const timer = setTimeout(() => {
                button.classList.remove('is-copied');
                label.textContent = defaultText;
                this._buttonTimers.delete(button);
            }, 1000);

            this._buttonTimers.set(button, timer);
        },

        async copyText(text, button = null) {
            const normalizedText = text ?? '';

            try {
                await navigator.clipboard.writeText(normalizedText);
                this.setButtonCopiedState(button);
            } catch (e) {
                const textarea = document.createElement('textarea');
                textarea.value = normalizedText;
                document.body.appendChild(textarea);
                textarea.select();

                try {
                    document.execCommand('copy');
                    this.setButtonCopiedState(button);
                } catch (_) {
                    //
                }

                document.body.removeChild(textarea);
            }
        },
    };
};

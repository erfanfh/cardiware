document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-post-edit-lock]');
    if (!form) return;

    const status = document.querySelector('[data-post-edit-lock-status]');
    const normalMessage = status.textContent.trim();
    const submitButtons = [...form.querySelectorAll('button[type="submit"], input[type="submit"]')];
    let lost = false;
    let submitting = false;
    let approvedSubmit = false;
    let approvedEvent = null;
    let checkingSubmit = false;
    let renewal = null;

    function showStatus(message, blocked) {
        status.textContent = message;
        status.classList.toggle('text-amber-800', blocked);
        submitButtons.forEach(button => { button.disabled = blocked; });
    }

    function payload() {
        const data = new FormData();
        data.set('_token', form.querySelector('[name="_token"]').value);
        data.set('edit_lock_token', form.querySelector('[name="edit_lock_token"]').value);
        return data;
    }

    async function renew() {
        if (lost || submitting) return false;
        if (renewal) return renewal;

        renewal = (async () => {
            try {
                const response = await fetch(form.dataset.renewUrl, {
                    method: 'POST', body: payload(), credentials: 'same-origin',
                    headers: { Accept: 'application/json' },
                    signal: AbortSignal.timeout(10000),
                });
                if (response.ok) {
                    showStatus(normalMessage, false);
                    return true;
                }
                if ([401, 403, 404, 419, 423].includes(response.status)) {
                    lost = true;
                    const data = await response.json().catch(() => ({}));
                    showStatus(data.message || 'دسترسی ویرایش این صفحه پایان یافته است. تغییرات خود را کپی کنید و نوشته را دوباره باز کنید.', true);
                } else {
                    showStatus('ارتباط با سرور برقرار نشد. تا برقراری ارتباط، ذخیره متوقف است؛ تغییرات شما در همین صفحه باقی می‌ماند.', true);
                }
            } catch {
                showStatus('ارتباط با سرور برقرار نشد. تا برقراری ارتباط، ذخیره متوقف است؛ تغییرات شما در همین صفحه باقی می‌ماند.', true);
            }
            return false;
        })();

        try {
            return await renewal;
        } finally {
            renewal = null;
        }
    }

    // Capture before the shared form handler so a failed check keeps the unsaved-change warning.
    form.addEventListener('submit', async event => {
        if (approvedSubmit) {
            approvedEvent = event;
            return;
        }
        event.preventDefault();
        event.stopImmediatePropagation();
        if (checkingSubmit || submitting) return;
        checkingSubmit = true;
        const submitter = event.submitter;
        const renewed = await renew();
        checkingSubmit = false;
        if (!renewed) return;

        approvedSubmit = true;
        approvedEvent = null;
        submitting = true;
        form.requestSubmit(submitter || undefined);
        approvedSubmit = false;
        // Validation or another handler may cancel submission; resume renewal then.
        if (!approvedEvent || approvedEvent.defaultPrevented) submitting = false;
    }, true);

    let timer = setInterval(renew, 30000);
    window.addEventListener('online', renew);
    window.addEventListener('pageshow', event => {
        if (event.persisted) {
            submitting = false;
            timer = setInterval(renew, 30000);
            renew();
        }
    });
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') renew();
    });
    window.addEventListener('pagehide', () => {
        clearInterval(timer);
        if (submitting || lost) return;
        fetch(form.dataset.releaseUrl, {
            method: 'POST', body: payload(), credentials: 'same-origin',
            headers: { Accept: 'application/json' }, keepalive: true,
        }).catch(() => {});
    });
});

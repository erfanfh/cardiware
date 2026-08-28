let initialized = false;

export default function initTimeSpentTracker() {
    if (initialized) return;

    initialized = true;

    const element = document.querySelector('[data-loyalty-time-spent]');

    if (!element) return;

    const targetType = element.dataset.targetType;
    const targetId = element.dataset.targetId;

    if (!targetType || !targetId) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    if (!csrf) return;

    let token = null;
    let completed = false;
    let completing = false;
    let activeSeconds = 0;
    let requiredSeconds = 0;
    let timer = null;

    const request = async (url, body = {}) => {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify(body),
        });

        if (!response.ok) {
            throw new Error(`Loyalty request failed with status ${response.status}`);
        }

        return response.json();
    };

    const showEarnToast = (notification) => {
        if (!notification) return;

        window.dispatchEvent(new CustomEvent('loyalty:earned', {
            detail: notification,
        }));
    };

    const stop = () => {
        if (timer) {
            window.clearInterval(timer);
            timer = null;
        }
    };

    const complete = async () => {
        if (!token || completed || completing) return;
        if (activeSeconds < requiredSeconds) return;

        completing = true;

        try {
            const result = await request('/loyalty/time-spent/complete', {
                token,
            });

            completed = true;
            stop();

            showEarnToast(result.notification);
        } catch {
            // A temporary failure should not permanently kill the tracker.
        } finally {
            completing = false;
        }
    };

    request('/loyalty/time-spent/start', {
        target_type: targetType,
        target_id: targetId,
    }).then((data) => {
        token = data.token;
        requiredSeconds = Math.max(1, Number(data.required_seconds) || 1);

        timer = window.setInterval(() => {
            if (completed) {
                stop();
                return;
            }

            if (document.visibilityState !== 'visible' || !document.hasFocus()) {
                return;
            }

            activeSeconds += 1;

            if (activeSeconds >= requiredSeconds) {
                complete();
            }
        }, 1000);
    }).catch(() => {
        stop();
    });
}

let initialized = false;

export default function initTimeSpentTracker() {
    if (initialized) return;

    initialized = true;

    const element = document.querySelector('[data-loyalty-time-spent]');

    if (!element || !window.Laravel?.user) return;

    const targetType = element.dataset.targetType;
    const targetId = element.dataset.targetId;

    if (!targetType || !targetId) return;

    let token = null;
    let completed = false;
    let active = document.visibilityState === 'visible';

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    const request = async (url, body = {}) => {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
            },
            body: JSON.stringify(body),
            keepalive: true,
        });

        if (!response.ok) {
            throw new Error('Loyalty request failed');
        }

        return response.json();
    };

    const complete = async () => {
        if (!token || completed || !active) return;

        try {
            const result = await request('/loyalty/time-spent/complete', {
                token,
            });

            completed = true;

                        if (data.earned_points > 0) {
                            window.dispatchEvent(new CustomEvent('loyalty:toast', {
                                detail: {
                                    message: `+${data.earned_points} امتیاز باشگاه مخاطبان دریافت کردید`
                                }
                            }));
                        }

            if (result.earned_points && window.showToast) {
                window.showToast(
                    `+${result.earned_points} امتیاز باشگاه مخاطبان دریافت کردید`
                );
            }
        } catch {
            // ignore
        }
    };

    document.addEventListener('visibilitychange', () => {
        active = document.visibilityState === 'visible';
    });

    window.addEventListener('pagehide', complete);

    request('/loyalty/time-spent/start', {
        target_type: targetType,
        target_id: targetId,
    }).then((data) => {
        token = data.token;

        const timer = setInterval(() => {
            if (completed) {
                clearInterval(timer);
                return;
            }

            complete();
        }, 5000);
    }).catch(() => {});
}

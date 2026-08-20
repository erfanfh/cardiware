export default function initTimeSpentTracker() {
    const elements = document.querySelectorAll('[data-loyalty-time-spent]');

    if (!elements.length || !window.Laravel?.user) {
        return;
    }

    elements.forEach((element) => {
        const targetType = element.dataset.targetType;
        const targetId = element.dataset.targetId;

        if (!targetType || !targetId) {
            return;
        }

        let token = null;
        let completed = false;

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
            });

            if (!response.ok) {
                throw new Error('Loyalty request failed');
            }

            return response.json();
        };

        request('/loyalty/time-spent/start', {
            target_type: targetType,
            target_id: targetId,
        }).then((data) => {
            token = data.token;

            window.setInterval(async () => {
                if (!completed && token) {
                    try {
                        await request('/loyalty/time-spent/complete', {
                            token,
                        });

                        completed = true;
                    } catch (e) {
                    }
                }
            }, 5000);
        }).catch(() => {});
    });
}

export default function storyViewer(stories = []) {
    let timer = null;
    let previousFocus = null;
    let previousOverflow = '';
    let lastTick = 0;
    let resumeAfterHold = false;
    let resumeAfterVisibility = false;

    return {
        stories,
        isOpen: false,
        index: 0,
        elapsedMs: 0,
        paused: false,
        imageError: false,

        get current() {
            return this.stories[this.index] || {};
        },

        get durationMs() {
            return this.current.durationMs || 15000;
        },

        openByIndex(index) {
            if (!Number.isInteger(index) || !this.stories[index]) return;
            if (!this.isOpen) {
                previousFocus = document.activeElement;
                previousOverflow = document.body.style.overflow;
                document.body.style.overflow = 'hidden';
            }
            this.stopTimer();
            this.index = index;
            this.isOpen = true;
            this.resetTimer();
            timer = setInterval(() => this.tick(), 50);
            this.$nextTick(() => this.$refs.closeButton?.focus());
        },

        close() {
            if (!this.isOpen) return;
            this.stopTimer();
            this.isOpen = false;
            this.paused = false;
            document.body.style.overflow = previousOverflow;
            previousFocus?.focus();
        },

        resetTimer() {
            this.elapsedMs = 0;
            this.paused = false;
            this.imageError = false;
            resumeAfterHold = false;
            resumeAfterVisibility = false;
            lastTick = Date.now();
        },

        tick() {
            if (!this.isOpen || this.paused) return;
            const now = Date.now();
            this.elapsedMs += now - lastTick;
            lastTick = now;
            if (this.elapsedMs >= this.durationMs) this.next();
        },

        next() {
            if (!this.isOpen) return;
            if (this.index >= this.stories.length - 1) return this.close();
            this.index++;
            this.resetTimer();
        },

        prev() {
            if (!this.isOpen || this.index === 0) return;
            this.index--;
            this.resetTimer();
        },

        pause() {
            if (!this.isOpen || this.paused) return;
            this.tick();
            this.paused = this.isOpen;
        },

        resume() {
            if (!this.isOpen || !this.paused) return;
            lastTick = Date.now();
            this.paused = false;
        },

        hold() {
            const wasRunning = !this.paused;
            this.pause();
            resumeAfterHold = wasRunning;
        },

        release() {
            if (resumeAfterHold) this.resume();
            resumeAfterHold = false;
        },

        visibilityChanged() {
            if (!this.isOpen) return;
            if (document.hidden) {
                const wasRunning = !this.paused;
                this.pause();
                resumeAfterVisibility = wasRunning;
            } else {
                if (resumeAfterVisibility) this.resume();
                resumeAfterVisibility = false;
            }
        },

        progressStyle(index) {
            const progress = index < this.index ? 100 : index > this.index ? 0
                : Math.min(100, this.elapsedMs / this.durationMs * 100);
            return `width:${this.isOpen ? progress : 0}%`;
        },

        handleKeydown(event) {
            if (!this.isOpen) return;
            if (['Escape', 'ArrowRight', 'ArrowLeft', ' '].includes(event.key)) {
                // Keep Space available for focused buttons and links.
                if (event.key === ' ' && event.target?.closest('button, a')) return;
                event.preventDefault();
                if (event.key === 'Escape') this.close();
                if (event.key === 'ArrowRight') this.prev();
                if (event.key === 'ArrowLeft') this.next();
                if (event.key === ' ') this.paused ? this.resume() : this.pause();
            }
            if (event.key !== 'Tab') return;
            const buttons = [...this.$refs.dialog.querySelectorAll('button:not(:disabled), a[href]')]
                .filter(element => element.getClientRects().length > 0);
            const first = buttons[0];
            const last = buttons.at(-1);
            if (event.shiftKey && (document.activeElement === first || !this.$refs.dialog.contains(document.activeElement))) {
                event.preventDefault();
                last?.focus();
            } else if (!event.shiftKey && (document.activeElement === last || !this.$refs.dialog.contains(document.activeElement))) {
                event.preventDefault();
                first?.focus();
            }
        },

        stopTimer() {
            if (timer !== null) clearInterval(timer);
            timer = null;
        },

        destroy() {
            this.close();
            this.stopTimer();
        },
    };
}

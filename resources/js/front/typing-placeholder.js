export default function typingPlaceholder() {
    return {
        texts: [
            "دنبال چی می‌گردی؟",
            "دنبال پچ های لیگ ایران هستی؟",
            "فیس پک تیم های فوتبال رو میخوای؟",
            "میخوای بازی هاتو آپدیت کنی؟",
            "دنبال آموزش های فنی هستی؟",
            "کرک بازی های جدید رو میخوای؟",
            "تو Cardiware همه چیز پیدا میشه!",
        ],

        currentText: "",
        textIndex: 0,
        charIndex: 0,
        typing: true,
        timeoutId: null,

        init() {
            this.loop();
        },

        loop() {
            const current = this.texts[this.textIndex];

            if (this.typing) {
                this.currentText = current.substring(0, this.charIndex++);

                if (this.charIndex > current.length) {
                    this.typing = false;
                    this.timeoutId = setTimeout(() => this.loop(), 1200);
                    return;
                }
            } else {
                this.currentText = current.substring(0, this.charIndex--);

                if (this.charIndex <= 0) {
                    this.typing = true;
                    this.textIndex = (this.textIndex + 1) % this.texts.length;
                }
            }

            this.timeoutId = setTimeout(() => this.loop(), this.typing ? 70 : 40);
        },

        destroy() {
            if (this.timeoutId) {
                clearTimeout(this.timeoutId);
            }
        },
    };
}

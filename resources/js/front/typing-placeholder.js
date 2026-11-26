export default function typingPlaceholder() {
    return {
        texts: [
            "What are you looking for?",
            "Looking for Iranian league patches?",
            "Want football team face packs?",
            "Want to update your games?",
            "Looking for technical tutorials?",
            "Looking for game cracks?",
            "you Cardiware Find everything here!",
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

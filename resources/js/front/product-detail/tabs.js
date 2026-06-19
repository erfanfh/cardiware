export default function productTabs() {
    return {
        activeSection: 'product-content',
        activeIndex: 0,
        isSticky: false,

        headerBase: 130,
        adminBar: 41,
        isAdminBarVisible: false,

        sections: [
            {id: 'product-content', label: 'توضیحات'},
            {id: 'installation-steps', label: 'راهنمای نصب'},
            {id: 'faqs', label: 'سوالات متداول'},
            {id: 'releases', label: 'تغییرات'},
            {id: 'comments', label: 'دیدگاه‌ها'},
        ],

        init() {
            this.$nextTick(() => {
                this.observeSections();
                this.initSticky();
                this.updateIndex();
            });
        },

        get headerOffset() {
            return this.headerBase + (this.isAdminBarVisible ? this.adminBar : 0);
        },

        get availableSections() {
            return this.sections.filter(s => document.getElementById(s.id));
        },

        scrollTo(id) {
            const el = document.getElementById(id);
            if (!el) return;

            const top = el.getBoundingClientRect().top + window.scrollY;

            window.scrollTo({
                top: top - this.headerOffset - 12,
                behavior: 'smooth',
            });

            this.activeSection = id;
            this.updateIndex();
        },

        updateIndex() {
            const index = this.availableSections.findIndex(
                s => s.id === this.activeSection
            );

            this.activeIndex = index >= 0 ? index : 0;
        },

        observeSections() {
            const observer = new IntersectionObserver(
                (entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            this.activeSection = entry.target.id;
                            this.updateIndex();
                        }
                    });
                },
                {
                    root: null,
                    rootMargin: `-${this.headerOffset}px 0px -55% 0px`,
                    threshold: 0.15,
                }
            );

            this.sections.forEach(s => {
                const el = document.getElementById(s.id);
                if (el) observer.observe(el);
            });
        },

        initSticky() {
            const el = this.$refs.stickyAnchor;
            if (!el) return;

            const observer = new IntersectionObserver(
                ([entry]) => {
                    this.isSticky = !entry.isIntersecting;
                },
                {threshold: 1}
            );

            observer.observe(el);
        },
    };
}

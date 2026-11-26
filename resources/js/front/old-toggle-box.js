document.addEventListener('DOMContentLoaded', function () {

    function slideUp(element, duration = 300) {
        element.style.height = element.offsetHeight + 'px';
        element.offsetHeight; // force repaint

        element.style.transitionProperty = 'height, margin, padding';
        element.style.transitionDuration = duration + 'ms';
        element.style.overflow = 'hidden';

        element.style.height = 0;
        element.style.paddingTop = 0;
        element.style.paddingBottom = 0;
        element.style.marginTop = 0;
        element.style.marginBottom = 0;

        setTimeout(() => {
            element.style.display = 'none';
            element.style.removeProperty('height');
            element.style.removeProperty('padding-top');
            element.style.removeProperty('padding-bottom');
            element.style.removeProperty('margin-top');
            element.style.removeProperty('margin-bottom');
            element.style.removeProperty('overflow');
            element.style.removeProperty('transition-duration');
            element.style.removeProperty('transition-property');
        }, duration);
    }

    function slideDown(element, duration = 300) {
        element.style.removeProperty('display');

        let display = window.getComputedStyle(element).display;
        if (display === 'none') display = 'block';

        element.style.display = display;

        let height = element.scrollHeight;

        element.style.overflow = 'hidden';
        element.style.height = 0;
        element.style.paddingTop = 0;
        element.style.paddingBottom = 0;
        element.style.marginTop = 0;
        element.style.marginBottom = 0;

        element.offsetHeight;

        element.style.transitionProperty = "height, margin, padding";
        element.style.transitionDuration = duration + "ms";

        element.style.height = height + 'px';

        element.style.removeProperty('padding-top');
        element.style.removeProperty('padding-bottom');
        element.style.removeProperty('margin-top');
        element.style.removeProperty('margin-bottom');

        setTimeout(() => {
            element.style.removeProperty('height');
            element.style.removeProperty('overflow');
            element.style.removeProperty('transition-duration');
            element.style.removeProperty('transition-property');
        }, duration);
    }

    function slideToggle(element, duration = 300) {
        if (window.getComputedStyle(element).display === 'none') {
            slideDown(element, duration);
        } else {
            slideUp(element, duration);
        }
    }

    function initToggle(titleSelector, boxSelector, contentSelector) {
        document.addEventListener('click', function (event) {
            const title = event.target.closest(titleSelector);
            if (!title) return;

            const box = title.closest(boxSelector);
            if (!box) return;

            const content = box.querySelector(contentSelector);
            if (!content) return;

            slideToggle(content, 300);
        });
    }

    // Closed by default
    document.querySelectorAll('.sh313_toggle_box_content, .properties_dl_box_content')
        .forEach(el => el.style.display = 'none');

    initToggle('.sh313_toggle_box_title', '.sh313_toggle_box', '.sh313_toggle_box_content');
    initToggle('.properties_dl_box_title', '.properties_dl_box', '.properties_dl_box_content');

});

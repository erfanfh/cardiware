import '@simonwep/pickr/dist/themes/nano.min.css';

import Pickr from '@simonwep/pickr';

document.addEventListener('DOMContentLoaded', function () {
    const pickerWrappers = document.querySelectorAll('.color-picker-wrapper');

    if (pickerWrappers.length === 0) {
        return;
    }

    pickerWrappers.forEach(wrapper => {
        const defaultValue = wrapper.dataset.value || wrapper.dataset.default || '#ff0000';
        const inputName = wrapper.dataset.name || 'color';

        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = inputName;
        hiddenInput.value = defaultValue;
        wrapper.appendChild(hiddenInput);

        const button = document.createElement('button');
        button.className = 'pickr-button';
        button.style.backgroundColor = defaultValue;
        button.style.width = '40px';
        button.style.height = '40px';
        button.style.border = '2px solid #ccc';
        button.style.borderRadius = '4px';
        button.style.cursor = 'pointer';
        wrapper.appendChild(button);

        const pickr = Pickr.create({
            el: button,
            theme: 'nano',
            default: defaultValue,
            i18n: {
                'btn:clear': 'پاک کردن',
                'btn:save': 'ذخیره'
            },

            components: {
                preview: true,  // نمایش رنگ فعلی و قبلی
                opacity: true,  // اسلایدر شفافیت
                hue: true,      // اسلایدر رنگ

                interaction: {
                    hex: false,   // نمایش کد HEX
                    rgba: false,  // نمایش RGBA
                    input: true, // فیلد ورودی دستی
                    clear: true, // دکمه پاک کردن
                    save: true   // دکمه ذخیره
                }
            }
        });

        pickr.on('change', (color) => {
            const hexaColor = color.toHEXA().toString();
            button.style.backgroundColor = hexaColor;
            hiddenInput.value = hexaColor;
        });

        pickr.on('save', (color) => {
            if (color) {
                const hexaColor = color.toHEXA().toString();
                hiddenInput.value = hexaColor;
            }
        });

        pickr.on('clear', () => {
            pickr.setColor(defaultValue);
        });
    });
});

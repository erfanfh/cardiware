import Sortable from 'sortablejs';

function collectLayoutState(root = document) {
    const areas = root.querySelectorAll('[data-layout-area]');
    const state = {};

    areas.forEach((area) => {
        const areaKey = area.dataset.areaKey;
        const elements = area.querySelectorAll('[data-layout-element]');
        state[areaKey] = Array.from(elements).map((element) => element.dataset.elementKey);
    });

    return state;
}

function updateEmptyStates(root = document) {
    const areas = root.querySelectorAll('[data-layout-area]');

    areas.forEach((area) => {
        const list = area.querySelector('[data-layout-elements-list]');
        const empty = area.querySelector('[data-layout-empty]');

        if (!list || !empty) {
            return;
        }

        const hasElements = list.querySelector('[data-layout-element]') !== null;

        empty.classList.toggle('hidden', hasElements);
    });
}

function updateSerializedState(root = document) {
    const input = root.querySelector('[data-layout-state-input]');

    if (!input) {
        return;
    }

    input.value = JSON.stringify(collectLayoutState(root));
}

function initializeLayoutSortables(root = document) {
    const areas = root.querySelectorAll('[data-layout-area]');

    if (!areas.length) {
        return;
    }

    areas.forEach((area) => {
        const list = area.querySelector('[data-layout-elements-list]');

        if (!list) {
            return;
        }

        Sortable.create(list, {
            group: area.dataset.sortableGroup || 'layout-elements',
            animation: 180,
            easing: 'cubic-bezier(0.2, 0, 0, 1)',
            handle: '[data-drag-handle]',
            draggable: '[data-layout-element]',
            ghostClass: 'layout-sortable-ghost',
            chosenClass: 'layout-sortable-chosen',
            dragClass: 'layout-sortable-drag',
            fallbackOnBody: true,
            swapThreshold: 0.65,

            onStart() {
                document.documentElement.classList.add('layout-is-sorting');
            },

            onEnd() {
                document.documentElement.classList.remove('layout-is-sorting');
                updateEmptyStates(root);
                updateSerializedState(root);

                const state = collectLayoutState(root);
                console.log('Layout state:', state);
            },

            onAdd() {
                updateEmptyStates(root);
            },

            onRemove() {
                updateEmptyStates(root);
            },
        });
    });

    updateEmptyStates(root);
    updateSerializedState(root);
}

document.addEventListener('DOMContentLoaded', () => {
    initializeLayoutSortables(document);
});

window.initializeLayoutSortables = initializeLayoutSortables;
window.collectLayoutState = collectLayoutState;

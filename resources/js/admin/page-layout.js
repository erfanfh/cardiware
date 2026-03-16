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

function getAllowedAreas(element) {
    const raw = element?.dataset?.allowedAreas ?? '';

    return raw
        .split(',')
        .map((item) => item.trim())
        .filter(Boolean);
}

function isDropAllowed(draggedElement, targetAreaElement) {
    if (!draggedElement || !targetAreaElement) {
        return false;
    }

    const targetAreaKey = targetAreaElement.dataset.areaKey;
    const allowedAreas = getAllowedAreas(draggedElement);

    if (!allowedAreas.length) {
        return true;
    }

    return allowedAreas.includes(targetAreaKey);
}

function setAreaVisualState(areaElement, state = null) {
    areaElement.classList.remove(
        'layout-area-allowed',
        'layout-area-forbidden'
    );

    if (state === 'allowed') {
        areaElement.classList.add('layout-area-allowed');
    }

    if (state === 'forbidden') {
        areaElement.classList.add('layout-area-forbidden');
    }
}

function clearAllAreaVisualStates(root = document) {
    root.querySelectorAll('[data-layout-area]').forEach((area) => {
        setAreaVisualState(area, null);
    });
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

            onStart(event) {
                document.documentElement.classList.add('layout-is-sorting');

                const draggedElement = event.item;

                areas.forEach((areaElement) => {
                    const state = isDropAllowed(draggedElement, areaElement)
                        ? 'allowed'
                        : 'forbidden';

                    setAreaVisualState(areaElement, state);
                });
            },

            onMove(event) {
                const draggedElement = event.dragged;
                const targetArea = event.to.closest('[data-layout-area]');

                if (!targetArea) {
                    return false;
                }

                return isDropAllowed(draggedElement, targetArea);
            },

            onEnd() {
                document.documentElement.classList.remove('layout-is-sorting');

                clearAllAreaVisualStates(root);
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

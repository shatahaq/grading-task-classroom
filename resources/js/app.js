import './bootstrap';
import { createIcons, icons } from 'lucide';

const refreshIcons = () => {
    createIcons({
        icons,
        attrs: {
            'stroke-width': '1.8',
            'aria-hidden': 'true',
        },
    });
};

window.refreshLucideIcons = refreshIcons;

const bindSidebar = () => {
    const sidebar = document.querySelector('[data-sidebar]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');
    const openButtons = document.querySelectorAll('[data-sidebar-open]');
    const closeButtons = document.querySelectorAll('[data-sidebar-close]');

    const open = () => {
        sidebar?.classList.remove('-translate-x-[calc(100%+2rem)]');
        backdrop?.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    const close = () => {
        sidebar?.classList.add('-translate-x-[calc(100%+2rem)]');
        backdrop?.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    openButtons.forEach((button) => button.addEventListener('click', open));
    closeButtons.forEach((button) => button.addEventListener('click', close));
    backdrop?.addEventListener('click', close);
};

const countUp = (element) => {
    const target = Number.parseFloat(element.dataset.countUp || element.textContent || '0');

    if (Number.isNaN(target)) {
        return;
    }

    const decimals = Number.parseInt(element.dataset.decimals || '0', 10);
    const duration = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 850;
    const startTime = performance.now();

    const draw = (time) => {
        const progress = duration === 0 ? 1 : Math.min((time - startTime) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);

        element.textContent = (target * eased).toFixed(decimals);

        if (progress < 1) {
            requestAnimationFrame(draw);
        }
    };

    requestAnimationFrame(draw);
};

const bindFileInputs = () => {
    document.querySelectorAll('[data-file-input]').forEach((input) => {
        const fileName = document.querySelector(`[data-file-name="${input.id}"]`);
        const defaultText = fileName?.dataset.defaultText || 'Pilih file';

        input.addEventListener('change', () => {
            if (fileName) {
                fileName.textContent = input.files?.[0]?.name || defaultText;
            }
        });
    });
};

const bindLocalSearch = () => {
    document.querySelectorAll('[data-search-target]').forEach((input) => {
        const selector = input.dataset.searchTarget;
        const empty = document.querySelector(input.dataset.searchEmpty || '');

        input.addEventListener('input', () => {
            const query = input.value.trim().toLowerCase();
            let visible = 0;

            document.querySelectorAll(selector).forEach((item) => {
                const haystack = (item.dataset.searchText || item.textContent || '').toLowerCase();
                const match = haystack.includes(query);
                item.hidden = !match;
                visible += match ? 1 : 0;
            });

            if (empty) {
                empty.hidden = visible !== 0;
            }
        });
    });
};

const bindAccordions = () => {
    document.querySelectorAll('[data-accordion-trigger]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const target = document.querySelector(trigger.dataset.accordionTrigger);
            const expanded = trigger.getAttribute('aria-expanded') === 'true';

            trigger.setAttribute('aria-expanded', expanded ? 'false' : 'true');

            if (target) {
                target.hidden = expanded;
            }
        });
    });
};

const bindCompletionMeter = () => {
    const form = document.querySelector('[data-completion-form]');
    const meter = document.querySelector('[data-completion-meter]');
    const label = document.querySelector('[data-completion-label]');

    if (!form || !meter || !label) {
        return;
    }

    const fields = Array.from(form.querySelectorAll('[required]'));
    const update = () => {
        const completed = fields.filter((field) => {
            if (field.type === 'file') return field.files?.length;
            if (field.type === 'checkbox') return field.checked;
            return String(field.value || '').trim().length;
        }).length;
        const percent = fields.length ? Math.round((completed / fields.length) * 100) : 0;

        meter.value = percent;
        label.textContent = `${percent}% siap`;
    };

    fields.forEach((field) => {
        field.addEventListener('input', update);
        field.addEventListener('change', update);
    });
    update();
};

const revealSkeletons = () => {
    window.setTimeout(() => {
        document.querySelectorAll('[data-skeleton]').forEach((item) => {
            item.hidden = true;
        });
        document.querySelectorAll('[data-hydrated]').forEach((item) => {
            item.hidden = false;
        });
    }, 360);
};

document.addEventListener('DOMContentLoaded', () => {
    bindSidebar();
    bindFileInputs();
    bindLocalSearch();
    bindAccordions();
    bindCompletionMeter();
    revealSkeletons();
    document.querySelectorAll('[data-count-up]').forEach(countUp);
    refreshIcons();
});

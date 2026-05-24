document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('[data-sidebar]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');
    const openButton = document.querySelector('[data-sidebar-open]');

    const closeSidebar = () => {
        sidebar?.classList.remove('is-open');
        if (backdrop) backdrop.hidden = true;
    };

    openButton?.addEventListener('click', () => {
        sidebar?.classList.add('is-open');
        if (backdrop) backdrop.hidden = false;
    });
    backdrop?.addEventListener('click', closeSidebar);

    document.querySelectorAll('[data-file-input]').forEach((input) => {
        input.addEventListener('change', () => {
            const label = document.querySelector(`[data-file-name="${input.id}"]`);
            if (label) label.textContent = input.files?.[0]?.name || 'Belum ada file';
        });
    });

    document.querySelectorAll('[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!confirm(form.dataset.confirm || 'Lanjutkan aksi ini?')) {
                event.preventDefault();
            }
        });
    });

    const courseSearch = document.querySelector('[data-course-search]');
    const courseCards = Array.from(document.querySelectorAll('[data-course-card]'));
    const courseSearchEmpty = document.querySelector('[data-course-search-empty]');

    courseSearch?.addEventListener('input', () => {
        const query = courseSearch.value.trim().toLowerCase();
        let visibleCount = 0;

        courseCards.forEach((card) => {
            const matches = (card.dataset.courseSearchText || card.textContent || '').toLowerCase().includes(query);
            card.hidden = !matches;
            visibleCount += matches ? 1 : 0;
        });

        if (courseSearchEmpty) {
            courseSearchEmpty.hidden = visibleCount !== 0;
        }
    });

    document.querySelectorAll('[data-due-date-control]').forEach((control) => {
        const hiddenInput = control.querySelector('[data-due-datetime]');
        const dateInput = control.querySelector('[data-due-date]');
        const timeInput = control.querySelector('[data-due-time]');
        const pad = (value) => String(value).padStart(2, '0');
        const toDateValue = (date) => `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;

        const syncDueDate = () => {
            if (!hiddenInput || !dateInput || !timeInput) return;

            hiddenInput.value = dateInput.value ? `${dateInput.value}T${timeInput.value || '23:59'}` : '';
        };

        dateInput?.addEventListener('input', syncDueDate);
        timeInput?.addEventListener('input', syncDueDate);

        control.querySelectorAll('[data-due-preset]').forEach((button) => {
            button.addEventListener('click', () => {
                if (!dateInput || !timeInput) return;

                const preset = button.dataset.duePreset;

                if (preset === 'clear') {
                    dateInput.value = '';
                    timeInput.value = '';
                    syncDueDate();
                    return;
                }

                const target = new Date();

                if (preset === 'tomorrow') {
                    target.setDate(target.getDate() + 1);
                } else if (preset === 'three-days') {
                    target.setDate(target.getDate() + 3);
                } else if (preset === 'week') {
                    target.setDate(target.getDate() + 7);
                }

                dateInput.value = toDateValue(target);
                timeInput.value = '23:59';
                syncDueDate();
            });
        });

        syncDueDate();
    });

    document.querySelectorAll('[data-trigger-grading]').forEach((button) => {
        button.addEventListener('click', async () => {
            const message = document.querySelector('[data-grading-message]');
            const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
            button.disabled = true;
            button.textContent = 'Memproses...';
            if (message) message.textContent = 'Workflow grading sedang dipanggil.';

            try {
                const response = await fetch(button.dataset.url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                    body: '{}',
                });
                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(payload.message || 'Workflow gagal dipanggil.');
                }

                if (message) message.textContent = `${payload.message} ${payload.saved_results || 0} hasil disimpan.`;
                window.setTimeout(() => window.location.reload(), 900);
            } catch (error) {
                if (message) message.textContent = error.message;
                button.disabled = false;
                button.textContent = 'Jalankan Grading';
            }
        });
    });
});

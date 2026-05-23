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

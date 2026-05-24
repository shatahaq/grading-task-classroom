<section class="landing-hero">
    <div class="landing-nav">
        <a class="landing-brand" href="<?= e(route('home')) ?>">
            <span class="brand-mark" aria-hidden="true">
                <img src="<?= e(asset('autograde-icon.svg')) ?>" alt="">
            </span>
            <span>AutoGrade AI</span>
        </a>
        <div class="landing-nav-actions">
            <a class="landing-nav-login" href="#tentang">Pelajari dulu</a>
            <a class="google-login-button nav-google-button" href="<?= e(url(route('login'))) ?>">
                <span class="google-mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" focusable="false">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.1c-.22-.66-.35-1.36-.35-2.1s.13-1.44.35-2.1V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l3.66-2.84z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06L5.84 9.9C6.71 7.3 9.14 5.38 12 5.38z"/>
                    </svg>
                </span>
                <span>Masuk dengan Google</span>
            </a>
        </div>
    </div>

    <div class="landing-hero-content">
        <p class="eyebrow">Google Classroom grading workspace</p>
        <h1>AutoGrade AI</h1>
        <p>Platform pendamping dosen untuk menyiapkan tugas, membaca konteks rubrik, menjalankan penilaian berbantuan AI, dan meninjau hasilnya sebelum dipakai.</p>
    </div>
</section>

<section id="tentang" class="landing-section landing-intro-section">
    <div class="landing-intro-copy">
        <p class="eyebrow">Tentang platform</p>
        <h2>Dirancang untuk alur penilaian dosen.</h2>
        <p>AutoGrade AI menghubungkan Google Classroom, file tugas, rubrik, dan workflow AI ke dalam satu ruang kerja. Dosen tetap memegang kontrol akhir, sementara proses membaca jawaban, menyiapkan feedback, dan menandai hasil yang perlu review menjadi lebih tertata.</p>
    </div>
    <div class="landing-intro-points" aria-label="Fokus AutoGrade AI">
        <article>
            <span>Classroom</span>
            <strong>Mulai dari kelas yang sudah dipakai</strong>
        </article>
        <article>
            <span>Rubrik</span>
            <strong>Konteks penilaian tetap jadi acuan</strong>
        </article>
        <article>
            <span>Review</span>
            <strong>Hasil akhir tetap ditinjau dosen</strong>
        </article>
    </div>
</section>

<section class="landing-section landing-workflow-section">
    <div class="landing-image-frame">
        <img src="<?= e(asset('landing-workflow.png')) ?>" alt="Ilustrasi alur kerja penilaian berbantuan AI">
    </div>
    <div class="landing-workflow-copy">
        <p class="eyebrow">Alur kerja</p>
        <h2>Dari tugas ke review dalam satu jalur.</h2>
        <div class="landing-steps">
            <article>
                <span>Konteks</span>
                <strong>Ambil konteks</strong>
                <p>Soal, rubrik, dan kunci jawaban menjadi dasar penilaian.</p>
            </article>
            <article>
                <span>Grading</span>
                <strong>Jalankan grading</strong>
                <p>Workflow AI membaca submission dan menyiapkan hasil terstruktur.</p>
            </article>
            <article>
                <span>Review</span>
                <strong>Tinjau hasil</strong>
                <p>Dosen memutuskan hasil final, approval, dan feedback lanjutan.</p>
            </article>
        </div>
    </div>
</section>

<section class="landing-section landing-feature-section">
    <article>
        <h3>Terhubung ke Classroom</h3>
        <p>Mulai dari kelas dan tugas yang sudah dipakai dosen sehari-hari.</p>
    </article>
    <article>
        <h3>Rubrik tetap jadi acuan</h3>
        <p>Penilaian diarahkan oleh dokumen konteks, bukan sekadar skor mentah.</p>
    </article>
    <article>
        <h3>Review sebelum final</h3>
        <p>Hasil AI disiapkan untuk ditinjau, disetujui, atau diperbaiki.</p>
    </article>
</section>

<section class="landing-section landing-cta-section">
    <div>
        <p class="eyebrow">Mulai bekerja</p>
        <h2>Masuk ketika siap menghubungkan Classroom.</h2>
    </div>
    <a class="google-login-button" href="<?= e(url(route('login'))) ?>">
        <span class="google-mark" aria-hidden="true">
            <svg viewBox="0 0 24 24" focusable="false">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.1c-.22-.66-.35-1.36-.35-2.1s.13-1.44.35-2.1V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l3.66-2.84z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06L5.84 9.9C6.71 7.3 9.14 5.38 12 5.38z"/>
            </svg>
        </span>
        <span>Masuk dengan Google</span>
    </a>
</section>

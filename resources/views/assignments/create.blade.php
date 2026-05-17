@extends('layouts.app', [
    'title' => 'Buat Tugas - AutoGrade AI',
    'pageTitle' => 'Buat Tugas',
    'pageCaption' => 'Publikasikan tugas dan siapkan konteks AI.',
])

@section('content')
    @php
        $labelClass = 'mb-1 block text-sm font-bold text-paper-ink/80';
        $inputClass = 'block w-full rounded-md border border-paper-line/50 bg-white px-4 py-2.5 text-sm text-paper-ink shadow-[inset_0_1px_3px_rgba(0,0,0,0.02)] transition-all focus:border-paper-marker focus:outline-none focus:ring-4 focus:ring-paper-marker/30';
        $textareaClass = 'block w-full rounded-md border border-paper-line/50 bg-white px-4 py-2.5 text-sm text-paper-ink shadow-[inset_0_1px_3px_rgba(0,0,0,0.02)] transition-all focus:border-paper-marker focus:outline-none focus:ring-4 focus:ring-paper-marker/30 resize-y';
        $panelClass = 'paper-sheet p-6 sm:p-8 rounded-[1rem] relative mb-6';
        $steps = [
            ['title' => 'Informasi', 'icon' => 'file-text'],
            ['title' => 'Penilaian', 'icon' => 'settings-2'],
            ['title' => 'Berkas', 'icon' => 'cloud-upload'],
            ['title' => 'Otomasi', 'icon' => 'bot'],
        ];
    @endphp

    @if ($notice)
        <section role="status">
            {{ $notice }}
        </section>
    @endif

    <form method="POST" action="{{ route('assignments.store') }}" enctype="multipart/form-data" data-completion-form class="max-w-4xl mx-auto">
        @csrf

        <div class="grid gap-6">
            <section class="paper-sheet p-8 sm:p-10 mb-2">
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-paper-ink/70">Google Classroom assignment</p>
                <h1 class="mt-4 max-w-2xl font-serif text-3xl font-bold tracking-tight text-paper-ink sm:text-4xl">Buat tugas yang siap dinilai sejak pertama dipublikasi.</h1>
                <p class="mt-4 max-w-xl text-sm font-medium leading-relaxed text-paper-ink/80">Isi instruksi, batas penilaian, dan tiga file konteks agar workflow grading punya pegangan yang jelas.</p>
            </section>

            <section class="{{ $panelClass }}" aria-labelledby="assignment-info-title">
                <header class="mb-6 flex items-start gap-4">
                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-paper-blue text-paper-ink shadow-sm rotate-[-2deg]"><i data-lucide="file-text"></i></span>
                    <div>
                        <h2 id="assignment-info-title" class="font-serif text-xl font-bold text-paper-ink">Informasi Tugas</h2>
                        <p class="text-xs font-medium text-paper-ink/70">Kelas dan instruksi utama.</p>
                    </div>
                </header>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="course_id" class="{{ $labelClass }}">Kelas</label>
                        <select id="course_id" name="course_id" class="{{ $inputClass }}" required>
                            @forelse ($courses as $course)
                                <option value="{{ $course['id'] }}" @selected(old('course_id', $selectedCourse) === $course['id'])>{{ $course['name'] }} - {{ $course['section'] ?? '-' }}</option>
                            @empty
                                <option value="" disabled selected>Belum ada kelas tersedia</option>
                            @endforelse
                        </select>
                        @error('course_id')<p class="mt-1 text-xs font-bold text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="title" class="{{ $labelClass }}">Judul Tugas</label>
                        <input id="title" name="title" type="text" value="{{ old('title') }}" placeholder="Contoh: Tugas PHP Pertemuan 5" class="{{ $inputClass }}" required>
                        @error('title')<p class="mt-1 text-xs font-bold text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="description" class="{{ $labelClass }}">Deskripsi</label>
                        <textarea id="description" name="description" rows="5" placeholder="Instruksi, konteks tugas, dan ekspektasi jawaban mahasiswa..." class="{{ $textareaClass }}">{{ old('description') }}</textarea>
                        @error('description')<p class="mt-1 text-xs font-bold text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            <section class="{{ $panelClass }}" aria-labelledby="grading-settings-title">
                <header class="mb-6 flex items-start gap-4">
                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-paper-blue text-paper-ink shadow-sm rotate-2"><i data-lucide="settings-2"></i></span>
                    <div>
                        <h2 id="grading-settings-title" class="font-serif text-xl font-bold text-paper-ink">Pengaturan Penilaian</h2>
                        <p class="text-xs font-medium text-paper-ink/70">Skor, mode, batas minimum, dan tenggat.</p>
                    </div>
                </header>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="max_score" class="{{ $labelClass }}">Skor Maksimum</label>
                        <input id="max_score" name="max_score" type="number" min="0" max="999.99" step="0.01" value="{{ old('max_score', 100) }}" class="{{ $inputClass }}" required>
                        @error('max_score')<p class="mt-1 text-xs font-bold text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="grade_mode" class="{{ $labelClass }}">Grade Mode</label>
                        <select id="grade_mode" name="grade_mode" class="{{ $inputClass }}">
                            <option value="draft" @selected(old('grade_mode', 'draft') === 'draft')>Draft</option>
                            <option value="final" @selected(old('grade_mode') === 'final')>Final</option>
                            <option value="none" @selected(old('grade_mode') === 'none')>Tanpa Nilai</option>
                        </select>
                        @error('grade_mode')<p class="mt-1 text-xs font-bold text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="min_answer_length" class="{{ $labelClass }}">Minimum Panjang Jawaban</label>
                        <input id="min_answer_length" name="min_answer_length" type="number" min="0" max="10000" step="1" value="{{ old('min_answer_length', 120) }}" class="{{ $inputClass }}" required>
                        @error('min_answer_length')<p class="mt-1 text-xs font-bold text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="due_date" class="{{ $labelClass }}">Tenggat Waktu</label>
                        <input id="due_date" name="due_date" type="datetime-local" value="{{ old('due_date') }}" class="{{ $inputClass }}">
                        @error('due_date')<p class="mt-1 text-xs font-bold text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>

                <label class="mt-6 flex cursor-pointer items-start justify-between gap-4 rounded-xl border border-paper-line bg-paper-bg/50 p-4 transition-all hover:bg-paper-blue/20">
                    <span class="flex items-center gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-white text-paper-ink shadow-sm"><i data-lucide="calendar-clock" class="h-5 w-5"></i></span>
                        <span class="min-w-0">
                            <span class="block text-sm font-bold text-paper-ink">Tutup Saat Tenggat Habis</span>
                            <span class="block text-xs font-medium text-paper-ink/70">Pengumpulan otomatis ditutup setelah deadline.</span>
                        </span>
                    </span>
                    <span class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full bg-paper-line transition-colors peer-checked:bg-paper-marker">
                        <input type="checkbox" name="close_on_due" value="1" @checked(old('close_on_due')) class="peer sr-only">
                        <span class="inline-block h-4 w-4 translate-x-1 rounded-full bg-white transition-transform peer-checked:translate-x-6"></span>
                    </span>
                </label>
            </section>

            <section class="{{ $panelClass }}" aria-labelledby="upload-title">
                <header class="mb-6 flex items-start gap-4">
                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-paper-blue text-paper-ink shadow-sm rotate-[-3deg]"><i data-lucide="cloud-upload"></i></span>
                    <div>
                        <h2 id="upload-title" class="font-serif text-xl font-bold text-paper-ink">Unggah Berkas</h2>
                        <p class="text-xs font-medium text-paper-ink/70">Soal, rubrik, dan kunci jawaban.</p>
                    </div>
                </header>

                <div class="grid gap-5 md:grid-cols-3">
                    @foreach ([
                        ['field' => 'question_file', 'label' => 'File Soal', 'icon' => 'help-circle'],
                        ['field' => 'rubric_file', 'label' => 'File Rubrik', 'icon' => 'list-checks'],
                        ['field' => 'answer_key_file', 'label' => 'File Kunci Jawaban', 'icon' => 'key-round'],
                    ] as $file)
                        <div>
                            <label for="{{ $file['field'] }}" class="group relative flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-paper-line bg-paper-bg/30 p-6 text-center transition-all hover:border-paper-marker hover:bg-paper-blue/10">
                                <input id="{{ $file['field'] }}" name="{{ $file['field'] }}" type="file" data-file-input required class="sr-only">
                                <span class="relative mb-3 grid h-12 w-12 place-items-center rounded-full bg-white text-paper-ink shadow-sm transition-transform group-hover:scale-110 group-hover:rotate-6">
                                    <i data-lucide="{{ $file['icon'] }}" class="h-6 w-6"></i>
                                    <span class="absolute -bottom-1 -right-1 grid h-5 w-5 place-items-center rounded-full bg-paper-marker text-paper-ink shadow-sm"><i data-lucide="plus" class="h-3 w-3"></i></span>
                                </span>
                                <span class="block text-sm font-bold text-paper-ink">{{ $file['label'] }}</span>
                                <span class="mt-1 block text-[11px] font-medium text-paper-ink/60" data-file-name="{{ $file['field'] }}" data-default-text="Seret file atau klik">Seret file atau klik</span>
                                <span class="mt-2 block rounded bg-paper-line/50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-paper-ink/60">PDF, DOCX, TXT</span>
                            </label>
                            @error($file['field'])<p class="mt-1 text-center text-xs font-bold text-red-500">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="{{ $panelClass }}" aria-labelledby="automation-title">
                <header class="mb-6 flex items-start gap-4">
                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-paper-blue text-paper-ink shadow-sm rotate-2"><i data-lucide="bot"></i></span>
                    <div>
                        <h2 id="automation-title" class="font-serif text-xl font-bold text-paper-ink">Opsi Otomatis</h2>
                        <p class="text-xs font-medium text-paper-ink/70">Tentukan aksi setelah AI selesai.</p>
                    </div>
                </header>

                <div class="grid gap-3">
                    @foreach ([
                        ['name' => 'auto_approval', 'title' => 'Auto Approval', 'desc' => 'Nilai disetujui otomatis jika confidence HIGH.', 'icon' => 'circle-check'],
                        ['name' => 'auto_email', 'title' => 'Auto Email Feedback', 'desc' => 'Feedback penilaian dikirim ke email mahasiswa.', 'icon' => 'send'],
                    ] as $toggle)
                        <label class="flex cursor-pointer items-start justify-between gap-4 rounded-xl border border-paper-line bg-paper-bg/50 p-4 transition-all hover:bg-paper-blue/20">
                            <span class="flex items-center gap-3">
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-white text-paper-ink shadow-sm"><i data-lucide="{{ $toggle['icon'] }}" class="h-5 w-5"></i></span>
                                <span class="min-w-0">
                                    <span class="block text-sm font-bold text-paper-ink">{{ $toggle['title'] }}</span>
                                    <span class="block text-xs font-medium text-paper-ink/70">{{ $toggle['desc'] }}</span>
                                </span>
                            </span>
                            <span class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full bg-paper-line transition-colors peer-checked:bg-paper-marker">
                                <input type="checkbox" name="{{ $toggle['name'] }}" value="1" @checked(old($toggle['name'])) class="peer sr-only">
                                <span class="inline-block h-4 w-4 translate-x-1 rounded-full bg-white transition-transform peer-checked:translate-x-6"></span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </section>

            <footer class="sticky bottom-0 z-20 mt-8 flex items-center justify-end gap-4 border-t border-paper-line bg-paper-bg/80 px-4 py-4 backdrop-blur-md sm:px-8 mx-[-1.25rem] sm:mx-[-2rem] mb-[-2.5rem]">
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('dashboard') }}" class="group relative inline-flex items-center justify-center bg-transparent px-5 py-2.5 text-sm font-bold text-paper-ink">
                        <span class="absolute inset-0 rounded-sm bg-white shadow-[0_2px_4px_rgba(30,58,138,0.1),_0_0_0_1px_rgba(30,58,138,0.05)] transition-all group-hover:-rotate-1 group-hover:scale-105"></span>
                        <span class="relative inline-flex items-center gap-2"><i data-lucide="arrow-left" class="h-4 w-4"></i> Batal</span>
                    </a>
                    <button type="submit" class="group relative inline-flex items-center justify-center bg-transparent px-6 py-2.5 text-sm font-bold text-paper-ink">
                        <span class="absolute inset-0 rounded-sm bg-paper-marker shadow-[0_2px_4px_rgba(30,58,138,0.1),_0_0_0_1px_rgba(30,58,138,0.05)] transition-all group-hover:rotate-1 group-hover:scale-105"></span>
                        <span class="relative inline-flex items-center gap-2"><i data-lucide="sparkles" class="h-4 w-4"></i> Simpan & Publikasikan</span>
                    </button>
                </div>
            </footer>
        </div>
    </form>
@endsection

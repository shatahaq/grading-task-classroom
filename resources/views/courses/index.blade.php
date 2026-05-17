@extends('layouts.app', [
    'title' => 'Kelas Saya - AutoGrade AI',
    'pageTitle' => 'Kelas Saya',
    'pageCaption' => 'Kelas Google Classroom yang siap dinilai.',
])

@section('content')
    @if ($notice)
        <section role="status">
            {{ $notice }}
        </section>
    @endif

    @if (count($courses))
        <section class="grid gap-6 sm:grid-cols-3 xl:grid-cols-4 mb-6">
            <div class="sm:col-span-2 xl:col-span-3 paper-sheet p-8 sm:p-10 rounded-[1rem] relative">
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-paper-ink/70">Classroom map</p>
                <h1 class="mt-4 font-serif text-3xl font-bold tracking-tight text-paper-ink sm:text-4xl">Pilih kelas yang butuh keputusan hari ini.</h1>
            </div>
            <div class="paper-sheet p-6 sm:p-8 rounded-[1rem] relative flex flex-col justify-between">
                <div class="paper-clip"></div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-paper-ink/70">Kelas aktif</p>
                <p class="mt-2 text-5xl font-extrabold tracking-tight text-paper-ink" data-count-up="{{ count($courses) }}">{{ count($courses) }}</p>
            </div>
        </section>

        <section aria-label="Daftar kelas Google Classroom" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($courses as $course)
                @php
                    $students = $course['students'] ?? null;
                    $assignmentCount = $course['classroom_assignment_count'] ?? (is_array($course['assignments'] ?? null) ? count($course['assignments']) : null);
                    $lastGraded = $course['last_graded'] ?? null;
                    $featured = $loop->first;
                @endphp

                <a href="{{ route('courses.show', ['courseId' => $course['id']]) }}" class="group block h-full outline-none">
                    <article class="paper-sheet flex h-full flex-col rounded-[1rem] p-6 transition-all duration-300 group-hover:-translate-y-1 group-hover:shadow-[0_24px_54px_rgba(30,58,138,0.16),_0_3px_10px_rgba(30,58,138,0.08)] group-focus:ring-4 group-focus:ring-paper-marker/30">
                        <header class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <span class="marker-highlight inline-flex items-center gap-1.5 px-2 py-0.5 text-[10px] font-bold uppercase tracking-widest text-paper-ink mb-2">
                                    Google Classroom
                                </span>
                                <h2 class="truncate font-serif text-xl font-bold text-paper-ink group-hover:text-paper-marker-dark transition-colors">{{ $course['name'] }}</h2>
                                <p class="mt-1 truncate text-xs font-medium text-paper-ink/60">{{ $course['section'] ?? 'Kelas Google' }} · 2026</p>
                            </div>
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-paper-blue/20 text-paper-ink transition-transform group-hover:rotate-6 group-hover:scale-110">
                                <i data-lucide="graduation-cap" class="h-5 w-5"></i>
                            </span>
                        </header>

                        <div class="mt-6 mb-4 flex flex-wrap gap-4 text-sm font-bold text-paper-ink/80">
                            <span class="flex items-center gap-2">
                                <i data-lucide="users" class="h-4 w-4 opacity-50"></i>
                                <span>{{ $students ?? '-' }}</span> mahasiswa
                            </span>
                            <span class="flex items-center gap-2">
                                <i data-lucide="clipboard-list" class="h-4 w-4 opacity-50"></i>
                                <span>{{ $assignmentCount ?? '-' }}</span> tugas
                            </span>
                        </div>

                        <footer class="mt-auto flex items-center justify-between gap-2 border-t border-paper-line/50 pt-4">
                            <p class="flex items-center gap-2 text-xs font-medium text-paper-ink/60">
                                <i data-lucide="clock" class="h-3.5 w-3.5"></i>
                                <span class="truncate">{{ $lastGraded ? 'AutoGrade '.$lastGraded : 'Belum ada grading' }}</span>
                            </p>
                            <span class="flex items-center gap-1 text-xs font-bold uppercase tracking-wider text-paper-ink transition-transform group-hover:translate-x-1">
                                Detail
                                <i data-lucide="chevron-right" class="h-3 w-3"></i>
                            </span>
                        </footer>
                    </article>
                </a>
            @endforeach
        </section>
    @else
        <section class="flex min-h-[16rem] flex-col items-center justify-center p-8 text-center text-paper-ink">
            <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-paper-blue text-paper-ink shadow-sm rotate-3">
                <i data-lucide="graduation-cap" class="h-8 w-8"></i>
            </span>
            <h2 class="mt-6 font-serif text-2xl font-bold">Belum ada kelas</h2>
            <p class="mt-2 max-w-md text-sm font-medium leading-relaxed opacity-80">Hubungkan Google Classroom untuk menarik kelas dan coursework yang Anda ampu.</p>
            <a href="{{ route('auth.google.redirect') }}" class="group relative mt-6 inline-flex items-center justify-center bg-transparent px-6 py-3 text-sm font-bold text-paper-ink">
                <span class="absolute inset-0 rounded-sm bg-white shadow-[0_2px_4px_rgba(30,58,138,0.1),_0_0_0_1px_rgba(30,58,138,0.05)] transition-all group-hover:-rotate-2 group-hover:scale-105"></span>
                <span class="relative inline-flex items-center gap-2"><i data-lucide="plug-zap" class="h-4 w-4"></i> Hubungkan Google</span>
            </a>
        </section>
    @endif
@endsection

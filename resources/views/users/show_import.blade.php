<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>نتائج الاستيراد — {{ config('app.name', 'Laravel') }}</title>

        @fonts

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
        <style>
            .import-progress-track {
                height: 1rem;
                border-radius: 9999px;
                background-color: #cbd5e1;
                border: 2px solid #64748b;
                overflow: hidden;
            }

            .import-progress-fill {
                height: 100%;
                min-width: 0;
                border-radius: 9999px;
                background: linear-gradient(to left, #0c4a6e, #0284c7);
                box-shadow: inset 0 1px 0 rgb(255 255 255 / 0.4);
                transition: width 0.5s ease-out;
            }

            .import-progress-fill--complete {
                width: 100%;
                background: linear-gradient(to left, #047857, #10b981);
            }
        </style>
    </head>
    <body class="min-h-screen p-6 lg:p-8 bg-white text-slate-800">
        <main class="w-full max-w-4xl mx-auto">
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm shadow-sky-100 ring-1 ring-sky-100">
                <div class="h-1 bg-sky-500" aria-hidden="true"></div>

                <div class="p-6 lg:p-8">
                    <header class="mb-6 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div class="flex gap-4 items-start">
                            <span class="shrink-0 flex items-center justify-center w-12 h-12 rounded-xl bg-sky-50 text-sky-600 ring-1 ring-sky-100">
                                <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </span>
                            <div>
                                <h1 class="text-xl font-semibold text-sky-900">نتائج الاستيراد</h1>
                                <p class="mt-1 text-sm text-slate-500">
                                    @if ($import->file_name)
                                        <span class="text-slate-700">{{ $import->file_name }}</span>
                                    @endif
                                    @if ($import->file_size)
                                        <span class="text-slate-400"> · {{ number_format((int) $import->file_size) }} بايت</span>
                                    @endif
                                </p>
                                <p class="mt-0.5 text-xs text-slate-400">
                                    {{ $import->created_at?->timezone(config('app.timezone'))->format('Y-m-d H:i') }}
                                </p>
                            </div>
                        </div>
                        <a
                            href="{{ route('users.import') }}"
                            class="inline-flex items-center justify-center shrink-0 px-4 py-2 text-sm font-medium text-sky-700 bg-sky-50 hover:bg-sky-100 rounded-xl ring-1 ring-sky-100 transition-colors"
                        >
                            استيراد ملف جديد
                        </a>
                    </header>

                    @php
                        $progressPercent = $import->total_records > 0
                            ? min(100, (int) round(($import->processed_records / $import->total_records) * 100))
                            : 0;
                        $importComplete = $import->status === \App\ImportStatusEnum::COMPLETE;
                        $progressBarWidth = $progressPercent > 0 && $progressPercent < 4 ? 4 : $progressPercent;
                    @endphp

                    <div id="import-progress-section" class="mb-6">
                        @if (! $importComplete)
                            <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-4 text-sm text-sky-900">
                                <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                                    <span class="font-medium">جاري الاستيراد...</span>
                                    <span id="import-progress-text" class="text-sky-700 tabular-nums">
                                        {{ number_format($import->processed_records) }} / {{ number_format($import->total_records) }}
                                    </span>
                                </div>
                                <div class="import-progress-track w-full" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $progressPercent }}">
                                    <div id="import-progress-fill" class="import-progress-fill" style="width: {{ $progressBarWidth }}%"></div>
                                </div>
                                <p class="mt-2 text-xs text-sky-600 tabular-nums">
                                    <span id="import-progress-percent">{{ $progressPercent }}</span>%
                                </p>
                            </div>
                        @else
                            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm text-emerald-900">
                                <p class="font-medium mb-3">اكتمل الاستيراد</p>
                                <div class="import-progress-track w-full">
                                    <div class="import-progress-fill import-progress-fill--complete"></div>
                                </div>
                                <p class="mt-2 text-xs text-emerald-700 tabular-nums">100%</p>
                            </div>
                        @endif
                    </div>

                    <div id="import-stats" class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                        <div class="rounded-xl border border-sky-100 bg-sky-50/50 p-4">
                            <p class="text-sm text-slate-500 mb-1">إجمالي السجلات</p>
                            <p id="stat-total-records" class="text-2xl font-semibold text-sky-900">{{ number_format($import->total_records) }}</p>
                        </div>
                        <div class="rounded-xl border border-emerald-100 bg-emerald-50/40 p-4">
                            <p class="text-sm text-slate-500 mb-1">ناجحة</p>
                            <p id="stat-total-success" class="text-2xl font-semibold text-emerald-800">{{ number_format($import->total_success) }}</p>
                        </div>
                        <div class="rounded-xl border border-red-100 bg-red-50/40 p-4">
                            <p class="text-sm text-slate-500 mb-1">فاشلة</p>
                            <p id="stat-total-failed" class="text-2xl font-semibold text-red-800/90">{{ number_format($import->total_faild) }}</p>
                        </div>
                    </div>

                    <section>
                        <h2 class="text-sm font-medium text-sky-800 mb-3 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-sky-400" aria-hidden="true"></span>
                            الصفوف الفاشلة
                        </h2>

                        <div id="import-failed-content">
                        @if ($failedResults->isEmpty())
                            <div class="rounded-xl border border-sky-100 bg-sky-50/30 px-6 py-10 text-center">
                                <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 mb-3">
                                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                </span>
                                <p class="text-sm font-medium text-slate-700">لا توجد صفوف فاشلة</p>
                                <p class="text-xs text-slate-500 mt-1">جميع السجلات المعالجة نجحت أو لم تُسجَّل أخطاء بعد.</p>
                            </div>
                        @else
                            <div class="overflow-x-auto rounded-xl ring-1 ring-sky-100">
                                <table class="w-full text-start border-collapse text-sm min-w-[640px]">
                                    <thead>
                                        <tr class="bg-sky-100 text-sky-900">
                                            <th class="py-3 px-3 font-medium w-12">#</th>
                                            <th class="py-3 px-3 font-medium w-20">رقم الصف</th>
                                            <th class="py-3 px-3 font-medium">الاسم</th>
                                            <th class="py-3 px-3 font-medium">البريد</th>
                                            <th class="py-3 px-3 font-medium">كلمة المرور</th>
                                            <th class="py-3 px-3 font-medium min-w-[180px]">الخطأ</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white text-slate-600">
                                        @foreach ($failedResults as $index => $result)
                                            @php
                                                $values = $result->values ?? [];
                                            @endphp
                                            <tr class="border-b border-sky-50 last:border-0">
                                                <td class="py-3 px-3 text-slate-400">{{ $index + 1 }}</td>
                                                <td class="py-3 px-3 font-mono text-slate-700">{{ $result->row }}</td>
                                                <td class="py-3 px-3">{{ $values['name'] ?? '—' }}</td>
                                                <td class="py-3 px-3 break-all">{{ $values['email'] ?? '—' }}</td>
                                                <td class="py-3 px-3 font-mono text-xs text-slate-500">{{ $values['password'] ?? '—' }}</td>
                                                <td class="py-3 px-3 text-red-700/90">
                                                    @if (empty($result->errors))
                                                        —
                                                    @else
                                                        <ul class="list-disc list-inside space-y-0.5 text-xs leading-relaxed">
                                                            @foreach ($result->errors as $field => $messages)
                                                                @foreach ((array) $messages as $message)
                                                                    <li>
                                                                        @if (is_string($field) && $field !== '')
                                                                            <span class="text-slate-500">{{ $field }}:</span>
                                                                        @endif
                                                                        {{ $message }}
                                                                    </li>
                                                                @endforeach
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                        </div>
                    </section>
                </div>
            </div>
        </main>

        <script>
            (function () {
                const pollUrl = @json(route('imports.show', $import));
                const pollIntervalMs = 2000;
                let pollTimer = null;

                function formatNumber(value) {
                    return new Intl.NumberFormat('en-US').format(value ?? 0);
                }

                function progressPercent(processed, total, completed) {
                    if (completed) {
                        return 100;
                    }

                    if (!total) {
                        return 0;
                    }

                    return Math.min(100, Math.round((processed / total) * 100));
                }

                function progressBarWidth(percent) {
                    if (percent > 0 && percent < 4) {
                        return 4;
                    }

                    return percent;
                }

                function renderProgressSection(data) {
                    const processed = data.processed_records ?? 0;
                    const total = data.total_records ?? 0;
                    const percent = progressPercent(processed, total, data.completed);
                    const section = document.getElementById('import-progress-section');

                    if (data.completed) {
                        section.innerHTML = `
                            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm text-emerald-900">
                                <p class="font-medium mb-1">اكتمل الاستيراد</p>
                                <p class="text-xs text-emerald-800 mb-3 tabular-nums">
                                    ناجحة: ${formatNumber(data.total_success)} · فاشلة: ${formatNumber(data.total_faild)}
                                </p>
                                <div class="import-progress-track w-full">
                                    <div class="import-progress-fill import-progress-fill--complete"></div>
                                </div>
                                <p class="mt-2 text-xs text-emerald-700 tabular-nums">100%</p>
                            </div>
                        `;
                        return;
                    }

                    const barWidth = progressBarWidth(percent);

                    section.innerHTML = `
                        <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-4 text-sm text-sky-900">
                            <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                                <span class="font-medium">جاري الاستيراد...</span>
                                <span class="text-sky-700 tabular-nums">${formatNumber(processed)} / ${formatNumber(total)}</span>
                            </div>
                            <div class="import-progress-track w-full" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="${percent}">
                                <div class="import-progress-fill" style="width: ${barWidth}%"></div>
                            </div>
                            <p class="mt-2 text-xs text-sky-800 font-medium tabular-nums">${percent}%</p>
                        </div>
                    `;
                }

                function renderFailedTable(failedResults) {
                    if (!failedResults.length) {
                        return `
                            <div class="rounded-xl border border-sky-100 bg-sky-50/30 px-6 py-10 text-center">
                                <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 mb-3">
                                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                </span>
                                <p class="text-sm font-medium text-slate-700">لا توجد صفوف فاشلة</p>
                                <p class="text-xs text-slate-500 mt-1">جميع السجلات المعالجة نجحت أو لم تُسجَّل أخطاء بعد.</p>
                            </div>
                        `;
                    }

                    const rows = failedResults.map(function (result, index) {
                        const values = result.values ?? {};
                        const errors = result.errors ?? {};
                        let errorsHtml = '—';

                        if (Object.keys(errors).length > 0) {
                            errorsHtml = '<ul class="list-disc list-inside space-y-0.5 text-xs leading-relaxed">' +
                                Object.entries(errors).map(function ([field, messages]) {
                                    return (Array.isArray(messages) ? messages : [messages]).map(function (message) {
                                        const fieldLabel = field ? `<span class="text-slate-500">${field}:</span> ` : '';
                                        return `<li>${fieldLabel}${message}</li>`;
                                    }).join('');
                                }).join('') +
                                '</ul>';
                        }

                        return `
                            <tr class="border-b border-sky-50 last:border-0">
                                <td class="py-3 px-3 text-slate-400">${index + 1}</td>
                                <td class="py-3 px-3 font-mono text-slate-700">${result.row}</td>
                                <td class="py-3 px-3">${values.name ?? '—'}</td>
                                <td class="py-3 px-3 break-all">${values.email ?? '—'}</td>
                                <td class="py-3 px-3 font-mono text-xs text-slate-500">${values.password ?? '—'}</td>
                                <td class="py-3 px-3 text-red-700/90">${errorsHtml}</td>
                            </tr>
                        `;
                    }).join('');

                    return `
                        <div class="overflow-x-auto rounded-xl ring-1 ring-sky-100">
                            <table class="w-full text-start border-collapse text-sm min-w-[640px]">
                                <thead>
                                    <tr class="bg-sky-100 text-sky-900">
                                        <th class="py-3 px-3 font-medium w-12">#</th>
                                        <th class="py-3 px-3 font-medium w-20">رقم الصف</th>
                                        <th class="py-3 px-3 font-medium">الاسم</th>
                                        <th class="py-3 px-3 font-medium">البريد</th>
                                        <th class="py-3 px-3 font-medium">كلمة المرور</th>
                                        <th class="py-3 px-3 font-medium min-w-[180px]">الخطأ</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white text-slate-600">${rows}</tbody>
                            </table>
                        </div>
                    `;
                }

                async function pollImportStatus() {
                    try {
                        const response = await fetch(pollUrl, {
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                        });

                        if (!response.ok) {
                            return;
                        }

                        const data = await response.json();
                        console.log('import status:', data);

                        document.getElementById('stat-total-records').textContent = formatNumber(data.total_records);
                        document.getElementById('stat-total-success').textContent = formatNumber(data.total_success);
                        document.getElementById('stat-total-failed').textContent = formatNumber(data.total_faild);

                        renderProgressSection(data);

                        document.getElementById('import-failed-content').innerHTML = renderFailedTable(data.failed_results ?? []);

                        if (data.completed && pollTimer !== null) {
                            clearInterval(pollTimer);
                            pollTimer = null;
                        }
                    } catch (error) {
                        console.error(error);
                    }
                }

                pollImportStatus();
                pollTimer = setInterval(pollImportStatus, pollIntervalMs);
            })();
        </script>
    </body>
</html>

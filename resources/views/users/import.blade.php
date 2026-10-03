<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>استيراد المستخدمين — {{ config('app.name', 'Laravel') }}</title>

        @fonts

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen flex items-center justify-center p-6 lg:p-8 bg-white text-slate-800">
        <main class="w-full max-w-lg">
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm shadow-sky-100 ring-1 ring-sky-100">
                <div class="h-1 bg-sky-500" aria-hidden="true"></div>

                <div class="p-6 lg:p-8">
                    <header class="mb-6 flex gap-4 items-start">
                        <span class="shrink-0 flex items-center justify-center w-12 h-12 rounded-xl bg-sky-50 text-sky-600 ring-1 ring-sky-100">
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </span>
                        <div>
                            <h1 class="text-xl font-semibold text-sky-900">
                                استيراد المستخدمين
                            </h1>
                            <p class="mt-1 text-sm text-slate-500 leading-relaxed">
                                ارفع ملف CSV أو Excel يحتوي على بيانات المستخدمين. الصف الأول يجب أن يكون عناوين الأعمدة.
                            </p>
                        </div>
                    </header>

                    <section class="mb-6 rounded-xl border border-sky-100 bg-sky-50/40 p-4 text-sm">
                        <h2 class="font-medium text-sky-800 mb-2 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-sky-400" aria-hidden="true"></span>
                            تنسيق الملف
                        </h2>
                        <p class="text-slate-500 mb-3">الأعمدة المطلوبة بالترتيب:</p>
                        <div class="overflow-x-auto rounded-lg ring-1 ring-sky-100">
                            <table class="w-full text-start border-collapse text-[13px]">
                                <thead>
                                    <tr class="bg-sky-100 text-sky-900">
                                        <th class="py-2.5 px-3 font-medium">name</th>
                                        <th class="py-2.5 px-3 font-medium">email</th>
                                        <th class="py-2.5 px-3 font-medium">password</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white text-slate-600">
                                    <tr>
                                        <td class="py-2.5 px-3">أحمد محمد</td>
                                        <td class="py-2.5 px-3">ahmed@example.com</td>
                                        <td class="py-2.5 px-3 font-mono text-slate-500">Secret123!</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    @if ($errors->any())
                        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="import-form" method="POST" action="{{ route('import') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf

                        <div>
                            <label
                                id="drop-zone"
                                for="file"
                                class="flex flex-col items-center justify-center gap-2 w-full min-h-[148px] px-4 py-6 border-2 border-dashed border-sky-200 rounded-xl cursor-pointer transition-colors bg-white hover:border-sky-400 hover:bg-sky-50/50 focus-within:ring-2 focus-within:ring-sky-300/60 focus-within:border-sky-400"
                            >
                                <span class="flex items-center justify-center w-14 h-14 rounded-full bg-sky-50 text-sky-500">
                                    <svg class="w-7 h-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                                    </svg>
                                </span>
                                <span class="text-sm font-medium text-sky-900 text-center">اسحب الملف هنا أو انقر للاختيار</span>
                                <span class="text-xs text-sky-500/90">CSV · XLSX · XLS</span>
                                <input
                                    type="file"
                                    name="file"
                                    id="file"
                                    accept=".csv,.xlsx,.xls,text/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                                    class="sr-only"
                                    required
                                >
                            </label>
                            @error('file')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p id="file-preview" class="mt-3 hidden text-sm rounded-lg px-3 py-2 bg-sky-50 border border-sky-100 text-slate-600">
                                <span class="font-medium text-sky-800" id="file-name"></span>
                                <span id="file-size" class="ms-2 text-sky-600"></span>
                            </p>
                        </div>

                        <button
                            type="submit"
                            id="submit-btn"
                            disabled
                            class="w-full py-3 px-5 rounded-xl text-sm font-medium text-white transition-colors bg-sky-500 hover:bg-sky-600 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-sky-500"
                        >
                            استيراد المستخدمين
                        </button>
                    </form>
                </div>
            </div>
        </main>

        <script>
            (function () {
                const input = document.getElementById('file');
                const dropZone = document.getElementById('drop-zone');
                const preview = document.getElementById('file-preview');
                const fileName = document.getElementById('file-name');
                const fileSize = document.getElementById('file-size');
                const submitBtn = document.getElementById('submit-btn');

                const dragActiveClasses = [
                    'border-sky-400',
                    'bg-sky-50/80',
                ];

                function formatSize(bytes) {
                    if (bytes < 1024) {
                        return bytes + ' بايت';
                    }
                    if (bytes < 1024 * 1024) {
                        return (bytes / 1024).toFixed(1) + ' ك.ب';
                    }
                    return (bytes / (1024 * 1024)).toFixed(1) + ' م.ب';
                }

                function showFile(file) {
                    if (!file) {
                        preview.classList.add('hidden');
                        submitBtn.disabled = true;
                        return;
                    }
                    fileName.textContent = file.name;
                    fileSize.textContent = '(' + formatSize(file.size) + ')';
                    preview.classList.remove('hidden');
                    submitBtn.disabled = false;
                }

                input.addEventListener('change', function () {
                    showFile(input.files[0] ?? null);
                });

                ['dragenter', 'dragover'].forEach(function (eventName) {
                    dropZone.addEventListener(eventName, function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        dropZone.classList.add(...dragActiveClasses);
                    });
                });

                ['dragleave', 'drop'].forEach(function (eventName) {
                    dropZone.addEventListener(eventName, function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        dropZone.classList.remove(...dragActiveClasses);
                    });
                });

                dropZone.addEventListener('drop', function (e) {
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        input.files = files;
                        showFile(files[0]);
                    }
                });

                const form = document.getElementById('import-form');
                const showImportUrlTemplate = @json(route('imports.show', ['import' => '__IMPORT_ID__']));
                function buildShowUrl(importId) {
                    return showImportUrlTemplate.replace('__IMPORT_ID__', String(importId));
                }

                form.addEventListener('submit', async function (e) {
                    e.preventDefault();

                    if (!input.files?.length) {
                        return;
                    }

                    submitBtn.disabled = true;
                    submitBtn.textContent = 'جاري الرفع...';

                    const formData = new FormData(form);

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                        });

                        if (!response.ok) {
                            throw new Error('Import request failed');
                        }

                        const payload = await response.json();
                        const importId = payload.import_id;

                        if (!importId) {
                            throw new Error('Missing import_id in response');
                        }

                        window.location.assign(buildShowUrl(importId));
                    } catch (error) {
                        console.error(error);
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'استيراد المستخدمين';
                    }
                });
            })();
        </script>
    </body>
</html>

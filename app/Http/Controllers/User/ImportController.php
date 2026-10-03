<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\ImportResultStatusEnum;
use App\Imports\UsersImport;
use App\ImportStatusEnum;
use App\Models\Import;
use App\Models\ImportResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel as FacadesExcel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportController extends Controller
{
    public function import(Request $request): JsonResponse
    {
        $import = DB::transaction(function () use ($request) {
            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getRealPath());

            $worksheet = $spreadsheet->getActiveSheet();

            $totalRecords = max(
                0,
                $worksheet->getHighestDataRow() - 1
            );

            $import = Import::create([
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'total_records' => $totalRecords,
            ]);

            FacadesExcel::import(new UsersImport($import), $request->file('file'));

            return $import;
        });

        return response()->json([
            'import_id' => $import->id,
        ]);
    }

    public function show(Request $request, Import $import): View|JsonResponse|StreamedResponse
    {
        if ($request->boolean('stream')) {
            return $this->streamImportProgress($import);
        }

        if ($request->expectsJson()) {
            return $this->importStatusJson($import);
        }

        $failedResults = $import->results()
            ->where('status', ImportResultStatusEnum::FAILD)
            ->orderBy('row')
            ->get();

        return view('users.show_import', [
            'import' => $import,
            'failedResults' => $failedResults,
        ]);
    }

    private function importStatusJson(Import $import): JsonResponse
    {
        $import->refresh();

        $failedResults = $import->results()
            ->where('status', ImportResultStatusEnum::FAILD)
            ->orderBy('row')
            ->get();

        return response()->json([
            'import_id' => $import->id,
            'total_records' => $import->total_records,
            'processed_records' => $import->processed_records,
            'total_success' => $import->total_success,
            'total_faild' => $import->total_faild,
            'status' => $import->status,
            'completed' => $import->status === ImportStatusEnum::COMPLETE,
            'failed_results' => $failedResults,
        ]);
    }

    private function streamImportProgress(Import $import): StreamedResponse
    {
        return response()->stream(
            function () use ($import) {
                $lastProcessed = 0;

                while (true) {
                    $import->refresh();
                    if ($import->processed_records > $lastProcessed) {
                        $results = ImportResult::query()
                            ->where('import_id', $import->id)
                            ->where('row', '>', $lastProcessed)
                            ->get();

                        foreach ($results as $result) {
                            echo 'data: ' . json_encode([
                                'row' => $result->row,
                                'status' => $result->status,
                                'error' => $result->errors,
                            ]) . "\n\n";

                            ob_flush();
                            flush();
                        }

                        $lastProcessed = $import->processed_records;
                    }

                    if ($import->status === ImportStatusEnum::COMPLETE) {
                        echo "event: completed\n";
                        echo 'data: ' . json_encode([
                            'success' => $import->total_success,
                            'failed' => $import->total_faild,
                        ]) . "\n\n";

                        ob_flush();
                        flush();

                        break;
                    }

                    sleep(2);
                }
            },
            200,
            [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'Connection' => 'keep-alive',
                'X-Accel-Buffering' => 'no',
            ]
        );
    }
}

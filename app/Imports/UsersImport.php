<?php

namespace App\Imports;

use App\ImportResultStatusEnum;
use App\ImportStatusEnum;
use App\Models\Import;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Validators\Failure;

class UsersImport implements OnEachRow, ShouldQueue, SkipsOnFailure, WithBatchInserts, WithChunkReading, WithHeadingRow, WithValidation
{
    use Importable, SkipsFailures;

    public function __construct(public Import $import)
    {
        //
    }

    public function onRow(Row $row): void
    {
        User::create([
            'name' => $row['name'],
            'email' => $row['email'],
            'password' => $row['password'],
        ]);

        $this->import->incrementEach(['processed_records' => 1, 'total_success' => 1]);
        $this->import->results()->create([
            'row' => $row->getIndex() - 1,
            'status' => ImportResultStatusEnum::SUCCESS,
        ]);

        $import = $this->import->refresh();

        if ($import->processed_records == $import->total_records) {
            $import->update(['status' => ImportStatusEnum::COMPLETE]);
        }
    }

    public function onFailure(Failure ...$failures): void
    {
        foreach ($failures as $failure) {
            $this->import->incrementEach(['processed_records' => 1, 'total_faild' => 1]);

            $this->import->results()->create([
                'status' => ImportResultStatusEnum::FAILD,
                'row' => $failure->row(),
                'values' => $failure->values(),
                'errors' => $failure->errors(),
            ]);
            $import = $this->import->refresh();

            if ($import->processed_records == $import->total_records) {
                $import->update(['status' => ImportStatusEnum::COMPLETE]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
        ];
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function batchSize(): int
    {
        return 100;
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->encryptTextColumnIfPlain('deliverable_submissions', 'notes');
        $this->encryptTextColumnIfPlain('deliverable_submissions', 'grade_feedback');
        $this->encryptTextColumnIfPlain('classroom_grades', 'feedback');
    }

    public function down(): void
    {
        // No-op by design: avoid writing plaintext back in the DB.
    }

    private function encryptTextColumnIfPlain(string $table, string $column): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return;
        }

        DB::table($table)
            ->select(['id', $column])
            ->orderBy('id')
            ->chunkById(100, function ($rows) use ($table, $column): void {
                foreach ($rows as $row) {
                    $value = $row->{$column} ?? null;
                    if (!is_string($value) || trim($value) === '') {
                        continue;
                    }

                    if ($this->looksEncrypted($value)) {
                        continue;
                    }

                    DB::table($table)
                        ->where('id', $row->id)
                        ->update([$column => Crypt::encryptString($value)]);
                }
            });
    }

    private function looksEncrypted(string $value): bool
    {
        try {
            Crypt::decryptString($value);
            return true;
        } catch (\Throwable $exception) {
            return false;
        }
    }
};


<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
     // Note: Disable wrapping migration in a transaction
     // otherwise, CREATE INDEX CONCURRENTLY will fail
    public $withinTransaction = false;

    public function up(): void
    {
        // index is built without exclusive table lock
        DB::statement('CREATE INDEX CONCURRENTLY idx_job_listings_employer_id ON job_listings (employer_id)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX CONCURRENTLY IF EXISTS idx_job_listings_employer_id');
    }
};

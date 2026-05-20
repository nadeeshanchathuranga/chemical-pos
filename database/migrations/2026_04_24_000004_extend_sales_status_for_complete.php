<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE sales MODIFY status ENUM('Open', 'Closed', 'Complete') NOT NULL DEFAULT 'Closed'");
        DB::statement("UPDATE sales SET status = 'Complete' WHERE status = 'Closed' AND (is_credit = 0 OR balance_due = 0)");
    }

    public function down(): void
    {
        DB::statement("UPDATE sales SET status = 'Closed' WHERE status = 'Complete'");
        DB::statement("ALTER TABLE sales MODIFY status ENUM('Open', 'Closed') NOT NULL DEFAULT 'Closed'");
    }
};

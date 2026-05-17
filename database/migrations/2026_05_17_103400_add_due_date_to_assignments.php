<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dateTime('due_date')->nullable()->after('min_answer_length');
            $table->boolean('close_on_due')->default(false)->after('due_date');
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn(['due_date', 'close_on_due']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            if (! Schema::hasColumn('contact_messages', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('notified_owner');
            }
            if (! Schema::hasColumn('contact_messages', 'status')) {
                $table->string('status', 20)->default('new')->after('read_at');
            }
            $table->index('read_at');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            if (Schema::hasColumn('contact_messages', 'status')) {
                $table->dropIndex(['status']);
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('contact_messages', 'read_at')) {
                $table->dropIndex(['read_at']);
                $table->dropColumn('read_at');
            }
        });
    }
};

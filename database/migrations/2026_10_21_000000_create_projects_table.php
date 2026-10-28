<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Same shape as the separate Portfolio CRM's projects table (so records can
     * be imported column-for-column), plus two additions this application owns:
     *
     * - is_demo: flags template placeholder content so the site keeps labelling
     *   it honestly.
     * - sort_order: the public display order, which used to be the order of the
     *   project array in config/portfolio.php.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('summary', 300)->nullable();
            $table->text('description')->nullable();

            // Longer-form narrative used by the public portfolio detail page.
            $table->text('problem')->nullable();
            $table->text('purpose')->nullable();
            $table->json('features')->nullable();
            $table->json('highlights')->nullable();
            $table->text('contribution')->nullable();
            $table->string('role', 120)->nullable();
            $table->string('year', 20)->nullable();

            $table->json('technologies')->nullable();
            $table->string('category', 80)->nullable()->index();
            $table->string('live_demo_url', 2048)->nullable();
            $table->string('repository_url', 2048)->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_demo')->default(false)->index();
            $table->string('status', 20)->default('draft')->index();
            $table->string('cover_image')->nullable();
            $table->json('gallery')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitor_sessions', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->char('visitor_hash', 64)->index();
            $table->char('ip_hash', 64)->nullable()->index();
            $table->string('country_code', 8)->nullable()->index();
            $table->string('device_type', 20)->nullable()->index();
            $table->string('browser', 60)->nullable()->index();
            $table->string('operating_system', 60)->nullable()->index();
            $table->string('language', 20)->nullable();
            $table->string('timezone', 80)->nullable();
            $table->unsignedSmallInteger('screen_width')->nullable();
            $table->unsignedSmallInteger('screen_height')->nullable();
            $table->unsignedSmallInteger('viewport_width')->nullable();
            $table->unsignedSmallInteger('viewport_height')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('landing_path', 2048)->nullable();
            $table->string('exit_path', 2048)->nullable();
            $table->string('referrer_host', 255)->nullable()->index();
            $table->string('utm_source', 255)->nullable()->index();
            $table->string('utm_medium', 255)->nullable()->index();
            $table->string('utm_campaign', 255)->nullable()->index();
            $table->string('utm_content', 255)->nullable();
            $table->string('utm_term', 255)->nullable();
            $table->unsignedInteger('page_views_count')->default(0);
            $table->unsignedInteger('events_count')->default(0);
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->timestamp('consented_at')->nullable();
            $table->timestamp('first_seen_at')->index();
            $table->timestamp('last_seen_at')->index();
            $table->timestamps();
        });

        Schema::create('visitor_page_views', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('visitor_session_id')->constrained()->cascadeOnDelete();
            $table->uuid('client_id')->unique();
            $table->string('path', 2048);
            $table->string('title', 500)->nullable();
            $table->string('referrer_host', 255)->nullable();
            $table->string('locale', 10)->nullable()->index();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->unsignedTinyInteger('scroll_depth')->default(0);
            $table->timestamp('viewed_at')->index();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
            $table->index(['visitor_session_id', 'viewed_at']);
        });

        Schema::create('visitor_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('visitor_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('visitor_page_view_id')->nullable()->constrained()->nullOnDelete();
            $table->uuid('client_id')->unique();
            $table->string('name', 50)->index();
            $table->string('label', 255)->nullable();
            $table->string('target_path', 2048)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();
            $table->index(['visitor_session_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_events');
        Schema::dropIfExists('visitor_page_views');
        Schema::dropIfExists('visitor_sessions');
    }
};

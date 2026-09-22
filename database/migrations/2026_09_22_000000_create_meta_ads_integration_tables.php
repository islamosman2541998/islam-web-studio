<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table): void {
            $table->string('meta_lead_id')->nullable()->unique();
            $table->string('meta_page_id')->nullable()->index();
            $table->string('meta_form_id')->nullable()->index();
            $table->string('meta_campaign_id')->nullable()->index();
            $table->string('meta_campaign_name')->nullable();
            $table->string('meta_adset_id')->nullable()->index();
            $table->string('meta_adset_name')->nullable();
            $table->string('meta_ad_id')->nullable()->index();
            $table->string('meta_ad_name')->nullable();
            $table->string('meta_platform')->nullable();
            $table->dateTime('meta_created_at')->nullable()->index();
            $table->json('meta_payload')->nullable();
        });

        Schema::create('meta_ad_insights', function (Blueprint $table): void {
            $table->id();
            $table->date('date')->index();
            $table->string('account_id')->index();
            $table->string('account_name')->nullable();
            $table->string('campaign_id')->nullable()->index();
            $table->string('campaign_name')->nullable();
            $table->string('adset_id')->nullable()->index();
            $table->string('adset_name')->nullable();
            $table->string('ad_id')->index();
            $table->string('ad_name')->nullable();
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('reach')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->unsignedBigInteger('link_clicks')->default(0);
            $table->unsignedInteger('leads')->default(0);
            $table->decimal('spend', 14, 2)->default(0);
            $table->decimal('cpc', 14, 4)->default(0);
            $table->decimal('cpm', 14, 4)->default(0);
            $table->decimal('ctr', 14, 4)->default(0);
            $table->decimal('cost_per_lead', 14, 4)->default(0);
            $table->string('currency', 8)->default('EGP');
            $table->timestamps();
            $table->unique(['date', 'account_id', 'ad_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_ad_insights');
        Schema::table('leads', function (Blueprint $table): void {
            $table->dropColumn([
                'meta_lead_id', 'meta_page_id', 'meta_form_id', 'meta_campaign_id', 'meta_campaign_name',
                'meta_adset_id', 'meta_adset_name', 'meta_ad_id', 'meta_ad_name', 'meta_platform',
                'meta_created_at', 'meta_payload',
            ]);
        });
    }
};

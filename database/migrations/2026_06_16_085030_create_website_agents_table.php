<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('website_agents', function (Blueprint $table) {
        $table->id();
        $table->string('url');
        $table->string('business_name')->nullable();
        $table->longText('scraped_content')->nullable();
        $table->enum('status', ['pending', 'scraping', 'ready', 'failed'])->default('pending');
        $table->string('session_id')->nullable();
        $table->timestamps();
    });

    Schema::create('support_tickets', function (Blueprint $table) {
        $table->id();
        $table->foreignId('website_agent_id')->constrained()->onDelete('cascade');
        $table->string('visitor_name')->nullable();
        $table->string('visitor_email')->nullable();
        $table->text('issue');
        $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
        $table->enum('status', ['open', 'escalated', 'resolved'])->default('open');
        $table->longText('conversation')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('website_agents');
    }
};

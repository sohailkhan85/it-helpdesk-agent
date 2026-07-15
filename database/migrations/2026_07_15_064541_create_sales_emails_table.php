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
    Schema::create('sales_emails', function (Blueprint $table) {
        $table->id();
        $table->string('sender_name');
        $table->string('sender_company');
        $table->string('product_service');
        $table->string('target_company');
        $table->string('target_name')->nullable();
        $table->string('target_role')->nullable();
        $table->text('pain_point');
        $table->string('tone')->default('professional');
        $table->longText('generated_email')->nullable();
        $table->longText('followup_sequence')->nullable();
        $table->longText('linkedin_message')->nullable();
        $table->longText('subject_lines')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_emails');
    }
};

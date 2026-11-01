<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            // Contact form fields
            $table->string('name');
            $table->string('company')->nullable();          // Club, gym or brand
            $table->string('email');
            $table->string('whatsapp')->nullable();
            $table->string('product_line');
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('quantity')->nullable();         // Pieces per design
            $table->string('destination_country');
            $table->string('customisation')->nullable();
            $table->text('message');                        // What do you need made
            // Admin handling
            $table->string('status')->default('new');       // new | in_progress | quoted | closed | spam
            $table->text('admin_notes')->nullable();
            $table->timestamp('read_at')->nullable();
            // Meta
            $table->string('source_url', 500)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};

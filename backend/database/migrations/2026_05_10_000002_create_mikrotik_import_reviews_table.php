<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mikrotik_import_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mikrotik_device_id')->constrained('mikrotik_devices')->cascadeOnDelete();
            $table->string('username');
            $table->string('password')->nullable();
            $table->string('profile')->nullable();
            $table->string('comment')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('mikrotik_import_reviews');
    }
};

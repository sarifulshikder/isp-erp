<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uplink_monitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('olt_id')->constrained('olt_devices')->cascadeOnDelete();
            $table->string('port_name');        // GE1, GE2
            $table->string('description');      // UpR-Link, BpR-Link
            $table->string('role');             // master, backup
            $table->enum('status', ['up', 'down', 'unknown'])->default('unknown');
            $table->enum('previous_status', ['up', 'down', 'unknown'])->default('unknown');
            $table->boolean('is_active_port')->default(false); // কোনটা এখন traffic দিচ্ছে
            $table->timestamp('last_changed_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('uplink_monitors');
    }
};

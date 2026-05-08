<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // OLT devices table
        Schema::create('olt_devices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('brand'); // vsol, bdcom
            $table->string('ip');
            $table->integer('port')->default(80);
            $table->string('username');
            $table->string('password');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamp('last_polled_at')->nullable();
            $table->timestamps();
        });

        // ONU monitoring data table
        Schema::create('onu_monitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('olt_id')->constrained('olt_devices')->onDelete('cascade');
            $table->string('onu_id');        // EPON0/1:1
            $table->string('mac')->nullable();
            $table->string('description')->nullable();
            $table->string('pon_port')->nullable(); // PON1, PON2
            $table->enum('status', ['online', 'offline', 'unknown'])->default('unknown');
            $table->decimal('rx_power', 6, 2)->nullable();  // dBm
            $table->decimal('tx_power', 6, 2)->nullable();  // dBm
            $table->enum('signal_status', ['normal', 'warning', 'critical', 'unknown'])->default('unknown');
            $table->boolean('alert_sent')->default(false);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });

        // ONU signal history
        Schema::create('onu_signal_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('olt_id')->constrained('olt_devices')->onDelete('cascade');
            $table->string('onu_id');
            $table->string('mac')->nullable();
            $table->decimal('rx_power', 6, 2)->nullable();
            $table->enum('signal_status', ['normal', 'warning', 'critical', 'unknown'])->default('unknown');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onu_signal_histories');
        Schema::dropIfExists('onu_monitors');
        Schema::dropIfExists('olt_devices');
    }
};

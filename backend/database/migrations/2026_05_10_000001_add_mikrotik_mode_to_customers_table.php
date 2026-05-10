<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->enum('mikrotik_mode', ['freeradius_only', 'specific', 'all'])
                  ->default('freeradius_only')
                  ->after('zone_id');
            $table->foreignId('mikrotik_device_id')
                  ->nullable()
                  ->after('mikrotik_mode')
                  ->constrained('mikrotik_devices')
                  ->nullOnDelete();
        });
    }
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['mikrotik_device_id']);
            $table->dropColumn(['mikrotik_mode', 'mikrotik_device_id']);
        });
    }
};

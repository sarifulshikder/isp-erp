<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Customers এ location
        Schema::table('customers', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->after('address');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
        });

        // OLT Devices এ location
        Schema::table('olt_devices', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
        });

        // Zones এ location
        Schema::table('zones', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
        });

        // Splitters table
        Schema::create('splitters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('1:8');
            $table->foreignId('olt_device_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('zone_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('address')->nullable();
            $table->enum('status', ['active', 'inactive', 'faulty'])->default('active');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // Fiber routes table
        Schema::create('fiber_routes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['olt_to_splitter', 'splitter_to_customer', 'olt_to_customer'])->default('olt_to_splitter');
            $table->foreignId('olt_device_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('splitter_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->json('coordinates');
            $table->string('color')->default('#FF6B35');
            $table->enum('status', ['active', 'inactive', 'damaged'])->default('active');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
        Schema::table('olt_devices', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
        Schema::table('zones', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
        Schema::dropIfExists('fiber_routes');
        Schema::dropIfExists('splitters');
    }
};

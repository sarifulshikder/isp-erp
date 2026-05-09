<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->enum('connection_type', ['pppoe', 'hotspot', 'static_ip'])
                  ->default('pppoe')
                  ->after('status');
            $table->string('mac_address')->nullable()->after('connection_type');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['connection_type', 'mac_address']);
        });
    }
};

<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('olt_devices', function (Blueprint $table) {
            $table->integer('snmp_port')->default(161)->after('port');
            $table->string('snmp_community')->default('public')->after('snmp_port');
        });
    }

    public function down(): void
    {
        Schema::table('olt_devices', function (Blueprint $table) {
            $table->dropColumn(['snmp_port', 'snmp_community']);
        });
    }
};

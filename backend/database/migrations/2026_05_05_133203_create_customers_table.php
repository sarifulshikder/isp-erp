<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->unique();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('username')->unique();
            $table->string('password');
            $table->foreignId('package_id')->constrained()->onDelete('restrict');
            $table->date('connection_date');
            $table->date('expire_date');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->string('mikrotik_profile')->nullable();
            $table->decimal('balance', 10, 2)->default(0);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('customers');
    }
};

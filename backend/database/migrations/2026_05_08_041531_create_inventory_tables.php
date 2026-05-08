<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('inventory_categories')->onDelete('restrict');
            $table->string('name');
            $table->string('serial_number')->nullable()->unique();
            $table->string('model')->nullable();
            $table->string('brand')->nullable();
            $table->enum('status', ['available', 'assigned', 'damaged', 'lost'])->default('available');
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->date('purchase_date')->nullable();
            $table->foreignId('assigned_customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->date('assigned_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
        Schema::dropIfExists('inventory_categories');
    }
};

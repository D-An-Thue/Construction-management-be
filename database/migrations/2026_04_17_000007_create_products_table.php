<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('Products')) {
            return;
        }

        Schema::create('Products', function (Blueprint $table) {
            $table->increments('Id');
            $table->string('ProductCode', 100)->unique();
            $table->string('ProductName', 255);
            $table->string('UnitName', 100);
            $table->boolean('IsDeleted')->default(false);
            $table->unsignedInteger('CreatedBy')->nullable()->default(0);
            $table->unsignedInteger('UpdatedBy')->nullable();
            $table->unsignedInteger('DeleteBy')->nullable();
            $table->dateTime('CreatedAt')->nullable();
            $table->dateTime('UpdatedAt')->nullable();
            $table->dateTime('DeleteAt')->nullable();

            $table->foreign('CreatedBy')->references('Id')->on('Persons')->nullOnDelete();
            $table->foreign('UpdatedBy')->references('Id')->on('Persons')->nullOnDelete();
            $table->foreign('DeleteBy')->references('Id')->on('Persons')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Products');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('AppSettings')) {
            return;
        }

        Schema::create('AppSettings', function (Blueprint $table) {
            $table->increments('Id');
            $table->string('AvatarUrl', 500)->nullable();
            $table->string('AppName', 500);
            $table->string('ContactEmail', 200)->nullable();
            $table->string('DomainWebsite', 500)->nullable();
            $table->text('ConfigJson')->nullable();
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
        Schema::dropIfExists('AppSettings');
    }
};

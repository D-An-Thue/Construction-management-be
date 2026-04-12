<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('Transactions', function (Blueprint $table) {
            $table->string('id', 64)->primary();
            $table->unsignedInteger('userID');
            $table->unsignedTinyInteger('TypeTransaction')->default(0);
            $table->text('Description')->nullable();
            $table->dateTime('When')->nullable();
            $table->uuid('TransactionId')->nullable();

            $table->foreign('userID')->references('Id')->on('Persons')->cascadeOnDelete();
        });

        Schema::create('Uploads', function (Blueprint $table) {
            $table->increments('Id');
            $table->string('OriginalName', 500);
            $table->string('StoredName', 500);
            $table->string('Disk', 100)->default('local');
            $table->string('Path', 1000);
            $table->string('MimeType', 255)->default('');
            $table->unsignedBigInteger('Size')->default(0);
            $table->unsignedInteger('CreatedBy')->nullable()->default(0);
            $table->dateTime('CreatedAt')->nullable();

            $table->foreign('CreatedBy')->references('Id')->on('Persons')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Uploads');
        Schema::dropIfExists('Transactions');
        Schema::dropIfExists('Products');
    }
};

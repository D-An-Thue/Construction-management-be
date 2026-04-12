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
        Schema::create('Groups', function (Blueprint $table) {
            $table->increments('Id');
            $table->string('GroupName', 500);
            $table->string('Description', 500)->default('');
            $table->bigInteger('Amount')->default(1000);
            $table->bigInteger('MinimumAmount')->default(1000);
            $table->bigInteger('MaximumAmount')->default(1000);
            $table->unsignedTinyInteger('GroupStatus')->default(2);
            $table->uuid('TransactionId');
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

        Schema::create('PersonGroups', function (Blueprint $table) {
            $table->increments('Id');
            $table->unsignedInteger('GroupId');
            $table->unsignedInteger('PersonId');
            $table->string('NickName', 500);
            $table->dateTime('JoinDate')->nullable();
            $table->boolean('IsAdmin')->default(false);
            $table->unsignedTinyInteger('JoinEnums')->default(1);
            $table->uuid('TransactionId');
            $table->boolean('IsDeleted')->default(false);
            $table->unsignedInteger('CreatedBy')->nullable()->default(0);
            $table->unsignedInteger('UpdatedBy')->nullable();
            $table->unsignedInteger('DeleteBy')->nullable();
            $table->dateTime('CreatedAt')->nullable();
            $table->dateTime('UpdatedAt')->nullable();
            $table->dateTime('DeleteAt')->nullable();

            $table->foreign('GroupId')->references('Id')->on('Groups')->cascadeOnDelete();
            $table->foreign('PersonId')->references('Id')->on('Persons')->cascadeOnDelete();
            $table->foreign('CreatedBy')->references('Id')->on('Persons')->nullOnDelete();
            $table->foreign('UpdatedBy')->references('Id')->on('Persons')->nullOnDelete();
            $table->foreign('DeleteBy')->references('Id')->on('Persons')->nullOnDelete();
        });

        Schema::create('Notifications', function (Blueprint $table) {
            $table->increments('Id');
            $table->string('Title', 500);
            $table->string('Message', 500);
            $table->boolean('IsDeleted')->default(false);
            $table->unsignedInteger('CreatedBy')->nullable()->default(0);
            $table->unsignedInteger('UpdatedBy')->nullable();
            $table->unsignedInteger('DeleteBy')->nullable();
            $table->dateTime('CreatedAt')->useCurrent();
            $table->dateTime('UpdatedAt')->useCurrent()->useCurrentOnUpdate();
            $table->dateTime('DeleteAt')->nullable();

            $table->foreign('CreatedBy')->references('Id')->on('Persons')->nullOnDelete();
            $table->foreign('UpdatedBy')->references('Id')->on('Persons')->nullOnDelete();
            $table->foreign('DeleteBy')->references('Id')->on('Persons')->nullOnDelete();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Notifications');
        Schema::dropIfExists('PersonGroups');
        Schema::dropIfExists('Groups');
    }
};

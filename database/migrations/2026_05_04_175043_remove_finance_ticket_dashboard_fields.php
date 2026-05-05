<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('TaskCollections', function (Blueprint $table) {
            $table->dropColumn('Cost');
            $table->dropColumn('TicketReferenceIds');
            $table->dropColumn('TransactionId');
        });

        Schema::table('Groups', function (Blueprint $table) {
            $table->dropColumn('Amount');
            $table->dropColumn('MinimumAmount');
            $table->dropColumn('MaximumAmount');
            $table->dropColumn('TransactionId');
        });

        Schema::dropIfExists('Tickets');
        Schema::dropIfExists('Transactions');
    }

    public function down(): void
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

        Schema::create('Tickets', function (Blueprint $table) {
            $table->increments('Id');
            $table->unsignedInteger('GroupId');
            $table->string('Title', 500);
            $table->text('Description')->nullable();
            $table->unsignedInteger('ApproveForUserId')->nullable();
            $table->unsignedInteger('AssignToUserID')->nullable();
            $table->unsignedTinyInteger('Status')->default(0);
            $table->unsignedTinyInteger('Priority')->default(1);
            $table->unsignedTinyInteger('TicketType')->default(0);
            $table->decimal('Amount', 18, 2)->default(0);
            $table->uuid('TransactionId');
            $table->boolean('IsDeleted')->default(false);
            $table->unsignedInteger('CreatedBy')->nullable()->default(0);
            $table->unsignedInteger('UpdatedBy')->nullable();
            $table->unsignedInteger('DeleteBy')->nullable();
            $table->dateTime('CreatedAt')->nullable();
            $table->dateTime('UpdatedAt')->nullable();
            $table->dateTime('DeleteAt')->nullable();

            $table->foreign('GroupId')->references('Id')->on('Groups')->cascadeOnDelete();
            $table->foreign('ApproveForUserId')->references('Id')->on('PersonGroups')->nullOnDelete();
            $table->foreign('AssignToUserID')->references('Id')->on('PersonGroups')->nullOnDelete();
            $table->foreign('CreatedBy')->references('Id')->on('Persons')->nullOnDelete();
            $table->foreign('UpdatedBy')->references('Id')->on('Persons')->nullOnDelete();
            $table->foreign('DeleteBy')->references('Id')->on('Persons')->nullOnDelete();
        });

        Schema::table('Groups', function (Blueprint $table) {
            $table->bigInteger('Amount')->default(1000);
            $table->bigInteger('MinimumAmount')->default(1000);
            $table->bigInteger('MaximumAmount')->default(1000);
            $table->uuid('TransactionId');
        });

        Schema::table('TaskCollections', function (Blueprint $table) {
            $table->json('TicketReferenceIds')->nullable();
            $table->decimal('Cost', 18, 2)->default(0);
            $table->uuid('TransactionId');
        });
    }
};

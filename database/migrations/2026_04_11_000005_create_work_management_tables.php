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
        Schema::create('TaskCollections', function (Blueprint $table) {
            $table->increments('Id');
            $table->string('TaskTitle', 500);
            $table->text('TaskDescription')->nullable();
            $table->unsignedInteger('GroupId');
            $table->unsignedInteger('AssignToUserId')->nullable();
            $table->unsignedTinyInteger('Status')->default(0);
            $table->unsignedTinyInteger('Priority')->default(1);
            $table->json('ReferenceGroupUserID')->nullable();
            $table->json('AttachLink')->nullable();
            $table->json('TicketReferenceIds')->nullable();
            $table->decimal('Cost', 18, 2)->default(0);
            $table->dateTime('DueDate')->nullable();
            $table->uuid('TransactionId');
            $table->boolean('IsDeleted')->default(false);
            $table->unsignedInteger('CreatedBy')->nullable()->default(0);
            $table->unsignedInteger('UpdatedBy')->nullable();
            $table->unsignedInteger('DeleteBy')->nullable();
            $table->dateTime('CreatedAt')->nullable();
            $table->dateTime('UpdatedAt')->nullable();
            $table->dateTime('DeleteAt')->nullable();

            $table->foreign('GroupId')->references('Id')->on('Groups')->cascadeOnDelete();
            $table->foreign('AssignToUserId')->references('Id')->on('PersonGroups')->nullOnDelete();
            $table->foreign('CreatedBy')->references('Id')->on('Persons')->nullOnDelete();
            $table->foreign('UpdatedBy')->references('Id')->on('Persons')->nullOnDelete();
            $table->foreign('DeleteBy')->references('Id')->on('Persons')->nullOnDelete();
        });

        Schema::create('SubTasks', function (Blueprint $table) {
            $table->increments('Id');
            $table->unsignedInteger('TaskId');
            $table->string('Title', 500);
            $table->text('Description')->nullable();
            $table->unsignedTinyInteger('Type')->default(0);
            $table->unsignedTinyInteger('Status')->default(0);
            $table->unsignedTinyInteger('Priority')->default(1);
            $table->unsignedInteger('AssignToUserId')->nullable();
            $table->dateTime('DueDate')->nullable();
            $table->boolean('IsDeleted')->default(false);
            $table->unsignedInteger('CreatedBy')->nullable()->default(0);
            $table->unsignedInteger('UpdatedBy')->nullable();
            $table->unsignedInteger('DeleteBy')->nullable();
            $table->dateTime('CreatedAt')->nullable();
            $table->dateTime('UpdatedAt')->nullable();
            $table->dateTime('DeleteAt')->nullable();

            $table->foreign('TaskId')->references('Id')->on('TaskCollections')->cascadeOnDelete();
            $table->foreign('AssignToUserId')->references('Id')->on('PersonGroups')->nullOnDelete();
            $table->foreign('CreatedBy')->references('Id')->on('Persons')->nullOnDelete();
            $table->foreign('UpdatedBy')->references('Id')->on('Persons')->nullOnDelete();
            $table->foreign('DeleteBy')->references('Id')->on('Persons')->nullOnDelete();
        });

        Schema::create('TaskComments', function (Blueprint $table) {
            $table->increments('Id');
            $table->unsignedInteger('TaskId');
            $table->unsignedInteger('CommentByUserId');
            $table->text('Content');
            $table->unsignedInteger('ParentCommentId')->nullable();
            $table->boolean('IsDeleted')->default(false);
            $table->unsignedInteger('CreatedBy')->nullable()->default(0);
            $table->unsignedInteger('UpdatedBy')->nullable();
            $table->unsignedInteger('DeleteBy')->nullable();
            $table->dateTime('CreatedAt')->nullable();
            $table->dateTime('UpdatedAt')->nullable();
            $table->dateTime('DeleteAt')->nullable();

            $table->foreign('TaskId')->references('Id')->on('TaskCollections')->cascadeOnDelete();
            $table->foreign('CommentByUserId')->references('Id')->on('PersonGroups')->cascadeOnDelete();
            $table->foreign('ParentCommentId')->references('Id')->on('TaskComments')->nullOnDelete();
            $table->foreign('CreatedBy')->references('Id')->on('Persons')->nullOnDelete();
            $table->foreign('UpdatedBy')->references('Id')->on('Persons')->nullOnDelete();
            $table->foreign('DeleteBy')->references('Id')->on('Persons')->nullOnDelete();
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Tickets');
        Schema::dropIfExists('TaskComments');
        Schema::dropIfExists('SubTasks');
        Schema::dropIfExists('TaskCollections');
    }
};

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
        Schema::create('ChatConversations', function (Blueprint $table) {
            $table->increments('Id');
            $table->unsignedTinyInteger('Type');
            $table->unsignedInteger('GroupId')->nullable();
            $table->string('DirectKey', 100)->nullable();
            $table->unsignedInteger('LastMessageId')->nullable();
            $table->dateTime('LastMessageAt')->nullable();

            $table->boolean('IsDeleted')->default(false);
            $table->unsignedInteger('CreatedBy')->nullable()->default(0);
            $table->unsignedInteger('UpdatedBy')->nullable();
            $table->unsignedInteger('DeleteBy')->nullable();
            $table->dateTime('CreatedAt')->nullable();
            $table->dateTime('UpdatedAt')->nullable();
            $table->dateTime('DeleteAt')->nullable();

            $table->foreign('GroupId')->references('Id')->on('Groups')->cascadeOnDelete();
            $table->foreign('CreatedBy')->references('Id')->on('Persons')->nullOnDelete();
            $table->foreign('UpdatedBy')->references('Id')->on('Persons')->nullOnDelete();
            $table->foreign('DeleteBy')->references('Id')->on('Persons')->nullOnDelete();

            $table->unique(['Type', 'GroupId']);
            $table->unique('DirectKey');
            $table->index('Type');
            $table->index('LastMessageAt');
        });

        Schema::create('ChatConversationParticipants', function (Blueprint $table) {
            $table->increments('Id');
            $table->unsignedInteger('ConversationId');
            $table->unsignedInteger('PersonId');
            $table->unsignedInteger('LastReadMessageId')->nullable();
            $table->dateTime('LastReadAt')->nullable();
            $table->dateTime('MutedUntil')->nullable();

            $table->boolean('IsDeleted')->default(false);
            $table->unsignedInteger('CreatedBy')->nullable()->default(0);
            $table->unsignedInteger('UpdatedBy')->nullable();
            $table->unsignedInteger('DeleteBy')->nullable();
            $table->dateTime('CreatedAt')->nullable();
            $table->dateTime('UpdatedAt')->nullable();
            $table->dateTime('DeleteAt')->nullable();

            $table->foreign('ConversationId')->references('Id')->on('ChatConversations')->cascadeOnDelete();
            $table->foreign('PersonId')->references('Id')->on('Persons')->cascadeOnDelete();
            $table->foreign('CreatedBy')->references('Id')->on('Persons')->nullOnDelete();
            $table->foreign('UpdatedBy')->references('Id')->on('Persons')->nullOnDelete();
            $table->foreign('DeleteBy')->references('Id')->on('Persons')->nullOnDelete();

            $table->unique(['ConversationId', 'PersonId']);
            $table->index('PersonId');
        });

        Schema::create('ChatMessages', function (Blueprint $table) {
            $table->increments('Id');
            $table->unsignedInteger('ConversationId');
            $table->unsignedInteger('SenderPersonId');
            $table->unsignedTinyInteger('MessageType')->default(1);
            $table->text('Body')->nullable();
            $table->json('Metadata')->nullable();
            $table->unsignedInteger('ReplyToMessageId')->nullable();
            $table->dateTime('EditedAt')->nullable();

            $table->boolean('IsDeleted')->default(false);
            $table->unsignedInteger('CreatedBy')->nullable()->default(0);
            $table->unsignedInteger('UpdatedBy')->nullable();
            $table->unsignedInteger('DeleteBy')->nullable();
            $table->dateTime('CreatedAt')->nullable();
            $table->dateTime('UpdatedAt')->nullable();
            $table->dateTime('DeleteAt')->nullable();

            $table->foreign('ConversationId')->references('Id')->on('ChatConversations')->cascadeOnDelete();
            $table->foreign('SenderPersonId')->references('Id')->on('Persons')->cascadeOnDelete();
            $table->foreign('ReplyToMessageId')->references('Id')->on('ChatMessages')->nullOnDelete();
            $table->foreign('CreatedBy')->references('Id')->on('Persons')->nullOnDelete();
            $table->foreign('UpdatedBy')->references('Id')->on('Persons')->nullOnDelete();
            $table->foreign('DeleteBy')->references('Id')->on('Persons')->nullOnDelete();

            $table->index(['ConversationId', 'Id']);
            $table->index(['ConversationId', 'CreatedAt']);
            $table->index('SenderPersonId');
        });

        // Break the circular dependency between conversations/participants and messages
        // by adding these foreign keys after all three tables exist.
        Schema::table('ChatConversations', function (Blueprint $table) {
            $table->foreign('LastMessageId')->references('Id')->on('ChatMessages')->nullOnDelete();
        });

        Schema::table('ChatConversationParticipants', function (Blueprint $table) {
            $table->foreign('LastReadMessageId')->references('Id')->on('ChatMessages')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ChatConversationParticipants', function (Blueprint $table) {
            $table->dropForeign(['LastReadMessageId']);
        });

        Schema::table('ChatConversations', function (Blueprint $table) {
            $table->dropForeign(['LastMessageId']);
        });

        Schema::dropIfExists('ChatMessages');
        Schema::dropIfExists('ChatConversationParticipants');
        Schema::dropIfExists('ChatConversations');
    }
};

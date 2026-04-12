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
        Schema::create('Persons', function (Blueprint $table) {
            $table->increments('Id');
            $table->string('Name', 500);
            $table->unsignedTinyInteger('Sex')->default(0);
            $table->string('Email', 500)->default('@email.com');
            $table->string('AvatarUrl', 500)->default('');
            $table->dateTime('DateOfBirth')->nullable();
            $table->string('PhoneNumber', 20)->default('');
            $table->string('Address', 500)->default('');
            $table->text('Password');
            $table->string('BankID')->default('');
            $table->string('BankAccountNumber')->default('');
            $table->string('BankName')->default('');
            $table->boolean('IsDeleted')->default(false);
            $table->integer('CreatedBy')->nullable()->default(0);
            $table->integer('UpdatedBy')->nullable();
            $table->integer('DeleteBy')->nullable();
            $table->dateTime('CreatedAt')->nullable();
            $table->dateTime('UpdatedAt')->nullable();
            $table->dateTime('DeleteAt')->nullable();
        });

        Schema::create('Roles', function (Blueprint $table) {
            $table->increments('Id');
            $table->string('RoleName', 200)->unique();
            $table->string('Description', 500)->default('');
            $table->boolean('IsDeleted')->default(false);
            $table->integer('CreatedBy')->nullable()->default(0);
            $table->integer('UpdatedBy')->nullable();
            $table->integer('DeleteBy')->nullable();
            $table->dateTime('CreatedAt')->nullable();
            $table->dateTime('UpdatedAt')->nullable();
            $table->dateTime('DeleteAt')->nullable();
        });

        Schema::create('Permissions', function (Blueprint $table) {
            $table->increments('Id');
            $table->string('PermissionCode', 100)->unique();
            $table->string('PermissionName', 200);
            $table->string('Module', 100);
            $table->boolean('IsDeleted')->default(false);
            $table->integer('CreatedBy')->nullable()->default(0);
            $table->integer('UpdatedBy')->nullable();
            $table->integer('DeleteBy')->nullable();
            $table->dateTime('CreatedAt')->nullable();
            $table->dateTime('UpdatedAt')->nullable();
            $table->dateTime('DeleteAt')->nullable();
        });

        Schema::create('RolePermissions', function (Blueprint $table) {
            $table->unsignedInteger('RoleId');
            $table->unsignedInteger('PermissionId');

            $table->primary(['RoleId', 'PermissionId']);

            $table->foreign('RoleId')->references('Id')->on('Roles')->cascadeOnDelete();
            $table->foreign('PermissionId')->references('Id')->on('Permissions')->cascadeOnDelete();
        });

        Schema::create('UserRoles', function (Blueprint $table) {
            $table->unsignedInteger('PersonId');
            $table->unsignedInteger('RoleId');

            $table->primary(['PersonId', 'RoleId']);

            $table->foreign('PersonId')->references('Id')->on('Persons')->cascadeOnDelete();
            $table->foreign('RoleId')->references('Id')->on('Roles')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('UserRoles');
        Schema::dropIfExists('RolePermissions');
        Schema::dropIfExists('Permissions');
        Schema::dropIfExists('Roles');
        Schema::dropIfExists('Persons');
    }
};

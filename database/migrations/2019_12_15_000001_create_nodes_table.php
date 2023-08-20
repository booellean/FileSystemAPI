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
        // Get every string combinations for CRUDX permissions, i.e. "11111", "10110", etc
        $allPermissions = \App\Libraries\Install::getAllPermissionStringCombinations();

        Schema::create('nodes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique(); // document path, ex. /root/fake.png
            $table->timestamps();
        });

        Schema::create('node_user_permissions', function (Blueprint $table) use ($allPermissions) {
			$table->id();
			$table->foreignId('node_id')->constrained('nodes');
			$table->foreignId('user_id')->constrained('users');
            $table->enum('permissions', $allPermissions);
			$table->timestamps();
		});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('node_user_permissions');
        Schema::dropIfExists('nodes');
    }
};

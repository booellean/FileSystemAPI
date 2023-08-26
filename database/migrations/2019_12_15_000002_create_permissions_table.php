<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get every string combinations for CRUDX permissions, i.e. "11111", "10110", etc
        $allPermissions = \App\Libraries\Install::getAllPermissionStringCombinations();

        Schema::create('permissions', function (Blueprint $table) use ($allPermissions) {
			$table->bigIncrements('id');
            $table->foreignId('user_id')->constrained('users');
            $table->morphs('permissions');
            $table->enum('crudx', $allPermissions);
			$table->timestamps();
		});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};

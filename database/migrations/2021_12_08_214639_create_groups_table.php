<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        // Get every string combinations for CRUDX permissions, i.e. "11111", "10110", etc
        $allPermissions = \App\Libraries\Install::getAllPermissionStringCombinations();

		Schema::create('groups', function (Blueprint $table) use ($allPermissions) {
			$table->bigIncrements('id');
			$table->string('name')->unique();
			$table->bigInteger('weight');
			$table->enum('permissions', $allPermissions);
			$table->timestamps();
		});

		Schema::create('user_groups', function (Blueprint $table) {
			$table->id();
			$table->foreignId('group_id')->constrained('groups');
			$table->foreignId('user_id')->constrained('users');
			$table->timestamps();
		});

        Schema::create('node_groups', function (Blueprint $table) {
			$table->id();
			$table->foreignId('group_id')->constrained('groups');
			$table->foreignId('node_id')->constrained('nodes');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('node_groups');
		Schema::dropIfExists('user_groups');
		Schema::dropIfExists('groups');
	}
}

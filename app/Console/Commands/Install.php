<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

use App\Models\Directory;
use App\Models\File;
use App\Models\Group;
use App\Models\User;

class Install extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'load:install';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Initial installation of app';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
        // Migrate the tables
        $this->comment('Building database...');
		$this->call('migrate:fresh');

        // Install Groups
        $this->comment('Loading default groups...');

		$defaultGroups = config('install.groups');
        $savedGroups = [];
        $savedUsers = [];

        $bar = $this->output->createProgressBar(count($defaultGroups));
		foreach ($defaultGroups as $name => $groupBP) {
			$group = new Group([
				'permissions' => $groupBP['permissions'],
				'weight' => $groupBP['weight']
            ]);
            $group->name = $name;

            $group->save();

            // To use with syncing groups to users, files, and directories later
            $savedGroups[$name] = $group;

			$bar->advance();
		}
		$bar->finish();
		$this->comment('');

		$this->info('Finished loading groups!');

        // Install Users
        $this->comment('Loading default users...');

        $defaultUsers = config('install.users');

        $bar = $this->output->createProgressBar(count($defaultUsers));
		foreach ($defaultUsers as $name => $userBP) {
			$user = new User([
				'name' => $name,
				'password' => $userBP['password']
            ]);

            $user->save();

            // To use with syncing permissions to files and directories later
            $savedUsers[$name] = $user;

            // Attach Groups to User
            $this->comment('Attaching groups to user...');
            foreach ($userBP['groups'] as $group_name) {
                if (isset($savedGroups[$group_name])) {
                    $group = $savedGroups[$group_name];
                    $user->groups()->attach($group->id);
                } else {
                    $this->error("Group $group_name was not found.");
                }
            }


			$bar->advance();
		}
		$bar->finish();
		$this->comment('');

		$this->info('Finished loading users!');

        // Remove old storage items
        $this->clearOutRootDirectory();

        // Install Nodes
        $this->comment('Loading default files and directories...');

        $rootNodeBP = config('install.root');

        // Create root directory first
        // $rootDirectory = new Directory();
        // $rootDirectory->name = '';
        // $rootDirectory->parent_id = 0;
        // $rootDirectory->save();

        $this->comment("Creating root directory and its children...");

        $this->recursiveFileAndDirectoryInstallation($savedGroups, $savedUsers, $rootNodeBP, '', 0);

        // $bar = $this->output->createProgressBar(count($rootNodeBP['nodes']));

        // foreach ($rootNodeBP['nodes'] as $node_name => $nodeBP) {
        //     $this->recursiveFileAndDirectoryInstallation($savedGroups, $savedUsers, $nodeBP, $node_name, $rootDirectory);
        //     $bar->advance();
        // }

        // $bar->finish();

		$this->comment('');

		$this->info('Finished loading files and directories!');

		return 1;
	}

    private function recursiveFileAndDirectoryInstallation($savedGroups, $savedUsers, $nodeBlueprint, $common_name, $parent_id) {
        $this->comment('');

        // Determine if it's a file or directory and create a node
        $is_file = isset($nodeBlueprint['extension']);
        $node = $is_file ? new File() : new Directory();
        $node->name = $common_name;
        $node->parent_id = $parent_id;
        if ($is_file) $node->extension = $nodeBlueprint['extension'];

        $this->comment("Creating $node->nodeType $common_name ...");

        // Save, which will automatically put in storage
        if ($node->save()) {
            if (isset($nodeBlueprint['groups'])) {
                // Attach Groups to Node...
                $this->comment("Attaching groups to $common_name...");

                foreach ($nodeBlueprint['groups'] as $group_name) {
                    if (isset($savedGroups[$group_name])) {
                        $group = $savedGroups[$group_name];
                        $node->groups()->attach($group->id);
                    } else {
                        $this->error("Group $group_name was not found.");
                    }
                }
            }

            if (isset($nodeBlueprint['user_permissions'])) {
                foreach ($nodeBlueprint['user_permissions'] as $user_name => $permission_string) {
                    // Create custom user permission for Node...
                    $this->comment("Creating custom permissions for $common_name...");

                    foreach ($nodeBlueprint['user_permissions'] as $user_name => $permission_string) {
                        if (isset($savedUsers[$user_name])) {
                            $user = $savedUsers[$user_name];

                            $node->user_permissions()->attach($user->id, ['crudx' => $permission_string]);

                            $this->comment("Custom permissions for $user_name attached to $common_name...");

                        } else {
                            $this->error("User $user_name was not found.");
                        }
                    }
                }
            }

            $this->comment( ucfirst($node->nodeType) . " $common_name was created!" );

            if (isset($nodeBlueprint['nodes']) && !$is_file) {
                $this->comment("Creating $common_name directory's children...");

                $bar = $this->output->createProgressBar(count($nodeBlueprint['nodes']));

                foreach ($nodeBlueprint['nodes'] as $node_name => $nodeBP) {
                    $node_id = $node->id;
                    $this->recursiveFileAndDirectoryInstallation($savedGroups, $savedUsers, $nodeBP, $node_name, $node_id);
                    $bar->advance();
                }

		        $bar->finish();
            }
        } else {
            // TODO: error handle
        }
    }

    private function clearOutRootDirectory()
    {
        // Let user known root directory is being cleared...
        $this->comment('Removing old test files...');

        $directories = Storage::disk('root')->directories();
        $files = Storage::disk('root')->files();

        if (count($directories) > 0) {
            $bar = $this->output->createProgressBar(count($directories));

            foreach ($directories as $directory) {
                Storage::disk('root')->deleteDirectory($directory);

                $bar->advance();
            }

            $bar->finish();
        }

        if (count($files) > 0) {
            $bar = $this->output->createProgressBar(count($files));

            foreach ($files as $file) {
                Storage::disk('root')->delete($file);

                $bar->advance();
            }

            $bar->finish();
        }

        $this->comment('');
        $this->info('Root directory is now empty!');
    }
}

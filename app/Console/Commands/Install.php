<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

use App\Models\Group;
use App\Models\Node;
use App\Models\NodeUserPermission;
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

        $rootNode = config('install.root');
        $path_name = '';

        $this->recursiveFileAndDirectoryInstallation($savedGroups, $savedUsers, $rootNode, $path_name);

		$this->comment('');

		$this->info('Finished loading files and directories!');

		return 1;
	}

    private function recursiveFileAndDirectoryInstallation($savedGroups, $savedUsers, $parentNode, $path_name, $common_name = 'root') {
        $this->comment('');

        // Determine if it's a file or directory and create in app
        $type = 'directory';
        if (isset($parentNode['extension'])) {

            $type = 'file';
            $path_name = $path_name.'.'.$parentNode['extension'];
            $common_name = $common_name.'.'.$parentNode['extension'];

            Storage::disk('root')->put($path_name, '');
        } else if($path_name != "") {
            Storage::disk('root')->makeDirectory($path_name);
        }

        $this->comment("Creating $common_name $type...");

        $node = new Node();
        $node->name = $path_name;

        if ($node->save()) {
            if (isset($parentNode['groups'])) {
                // Attach Groups to Node...
                $this->comment("Attaching groups to $common_name...");

                foreach ($parentNode['groups'] as $group_name) {
                    if (isset($savedGroups[$group_name])) {
                        $group = $savedGroups[$group_name];
                        $node->groups()->attach($group->id);
                    } else {
                        $this->error("Group $group_name was not found.");
                    }
                }
            }

            if (isset($parentNode['user_permissions'])) {
                foreach ($parentNode['user_permissions'] as $user_name => $permission_string) {
                    // Create custom user permission for Node...
                    $this->comment("Creating custom permissions for $common_name...");

                    foreach ($parentNode['user_permissions'] as $user_name => $permission_string) {
                        if (isset($savedUsers[$user_name])) {
                            $user = $savedUsers[$user_name];

                            $node_permissions = new NodeUserPermission([
                                'node_id' => $node->id,
                                'user_id' => $user->id,
                                'permissions' => $permission_string,
                            ]);

                            $node_permissions->save();

                            $this->comment("Custom permissions for $user_name attached to $common_name...");

                        } else {
                            $this->error("User $user_name was not found.");
                        }
                    }
                }
            }

            $this->comment("$common_name was created!");

            if (isset($parentNode['nodes'])) {
                $this->comment("Creating $common_name directory's children...");

                $bar = $this->output->createProgressBar(count($parentNode['nodes']));

                foreach ($parentNode['nodes'] as $node_name => $nodeBP) {
                    $new_path_name = $path_name . '/' . $node_name;
                    $this->recursiveFileAndDirectoryInstallation($savedGroups, $savedUsers, $nodeBP, $new_path_name, $node_name);
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

        if (count($directories) > 0) {
            $bar = $this->output->createProgressBar(count($directories));

            foreach ($directories as $directory) {
                Storage::disk('root')->deleteDirectory($directory);

                $bar->advance();
            }

            $bar->finish();
        }

        $this->comment('');
        $this->info('Root directory is now empty!');
    }
}

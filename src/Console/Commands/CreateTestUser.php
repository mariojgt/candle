<?php

namespace Mariojgt\Candle\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class CreateTestUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:create-test-user {--email=test@example.com} {--password=password} {--name=Test User}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a test user for Candle (only runs in test environment)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Check if we're in a test environment
        if (!app()->environment('testing', 'local')) {
            $this->error('This command can only be run in testing or local environments!');
            return Command::FAILURE;
        }

        $email = $this->option('email');
        $password = $this->option('password');
        $name = $this->option('name');

        // Check if user already exists
        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            $this->info("User with email {$email} already exists!");
            return Command::SUCCESS;
        }

        // Create the user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->info("Test user created with the following details:");
        $this->table(
            ['Name', 'Email', 'Password'],
            [[$user->name, $user->email, $password]]
        );

        return Command::SUCCESS;
    }
}

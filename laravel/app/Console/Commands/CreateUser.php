<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create 
                            {--email= : The email address of the user}
                            {--password= : The password for the user}
                            {--name= : The name of the user (optional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user account';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get email from option or ask
        $email = $this->option('email') ?: $this->ask('Email address');

        // Validate email
        $validator = Validator::make(['email' => $email], [
            'email' => 'required|email|unique:users,email',
        ]);

        if ($validator->fails()) {
            $this->error('Invalid email: ' . $validator->errors()->first('email'));
            return Command::FAILURE;
        }

        // Get password from option or ask (hidden)
        $password = $this->option('password') ?: $this->secret('Password');

        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters long');
            return Command::FAILURE;
        }

        // Get name from option or ask
        $name = $this->option('name') ?: $this->ask('Name (optional)', $email);

        // Create the user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->info('User created successfully!');
        $this->table(
            ['ID', 'Name', 'Email', 'Created'],
            [[$user->id, $user->name, $user->email, $user->created_at->format('Y-m-d H:i:s')]]
        );

        return Command::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ListApiTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'token:list 
                            {--email= : Filter by user email}
                            {--all : Show all tokens from all users}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List API tokens for a user or all users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('all')) {
            $users = User::with('apiTokens')->get();
            
            if ($users->isEmpty()) {
                $this->info('No users found');
                return Command::SUCCESS;
            }

            foreach ($users as $user) {
                $this->showUserTokens($user);
            }
        } else {
            // Get email from option or ask
            $email = $this->option('email') ?: $this->ask('User email address');

            // Find the user
            $user = User::where('email', $email)->with('apiTokens')->first();

            if (!$user) {
                $this->error("User with email '{$email}' not found");
                return Command::FAILURE;
            }

            $this->showUserTokens($user);
        }

        return Command::SUCCESS;
    }

    /**
     * Display tokens for a specific user
     */
    private function showUserTokens(User $user)
    {
        $this->info("Tokens for: {$user->email}");
        
        if ($user->apiTokens->isEmpty()) {
            $this->line('  No tokens found');
            $this->newLine();
            return;
        }

        $tokens = $user->apiTokens->map(function ($token) {
            return [
                'id' => $token->id,
                'name' => $token->name,
                'abilities' => implode(', ', $token->abilities ?? []),
                'last_used' => $token->last_used_at?->diffForHumans() ?? 'Never',
                'created' => $token->created_at->format('Y-m-d H:i'),
            ];
        });

        $this->table(
            ['ID', 'Name', 'Abilities', 'Last Used', 'Created'],
            $tokens
        );
        
        $this->newLine();
    }
}

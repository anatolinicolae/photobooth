<?php

namespace App\Console\Commands;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Console\Command;

class RevokeApiToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'token:revoke 
                            {--id= : The ID of the token to revoke}
                            {--email= : The email of the user (to show their tokens)}
                            {--all : Revoke all tokens for the user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Revoke (delete) an API token';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('all')) {
            return $this->revokeAllTokens();
        }

        // Get token ID from option or show selection
        $tokenId = $this->option('id');

        if (!$tokenId) {
            $email = $this->option('email') ?: $this->ask('User email address');
            
            $user = User::where('email', $email)->with('apiTokens')->first();

            if (!$user) {
                $this->error("User with email '{$email}' not found");
                return Command::FAILURE;
            }

            if ($user->apiTokens->isEmpty()) {
                $this->info('No tokens found for this user');
                return Command::SUCCESS;
            }

            // Show available tokens
            $choices = $user->apiTokens->mapWithKeys(function ($token) {
                return [$token->id => "ID {$token->id}: {$token->name} (" . implode(', ', $token->abilities ?? []) . ")"];
            })->toArray();

            $tokenId = $this->choice('Select token to revoke', $choices);
            $tokenId = (int) $tokenId; // Extract ID from the choice
        }

        // Find and delete the token
        $token = ApiToken::find($tokenId);

        if (!$token) {
            $this->error("Token with ID {$tokenId} not found");
            return Command::FAILURE;
        }

        $tokenName = $token->name;
        $userEmail = $token->user->email;

        if ($this->confirm("Are you sure you want to revoke token '{$tokenName}' for user '{$userEmail}'?", true)) {
            $token->delete();
            $this->info("Token '{$tokenName}' has been revoked successfully");
        } else {
            $this->info('Operation cancelled');
        }

        return Command::SUCCESS;
    }

    /**
     * Revoke all tokens for a user
     */
    private function revokeAllTokens()
    {
        $email = $this->option('email') ?: $this->ask('User email address');
        
        $user = User::where('email', $email)->with('apiTokens')->first();

        if (!$user) {
            $this->error("User with email '{$email}' not found");
            return Command::FAILURE;
        }

        $count = $user->apiTokens->count();

        if ($count === 0) {
            $this->info('No tokens found for this user');
            return Command::SUCCESS;
        }

        if ($this->confirm("Are you sure you want to revoke all {$count} token(s) for user '{$email}'?", false)) {
            $user->apiTokens()->delete();
            $this->info("All {$count} token(s) have been revoked successfully");
        } else {
            $this->info('Operation cancelled');
        }

        return Command::SUCCESS;
    }
}

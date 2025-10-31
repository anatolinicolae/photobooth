<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class GenerateApiToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'token:generate 
                            {--email= : The email of the user}
                            {--name= : A descriptive name for the token}
                            {--abilities=* : Comma-separated list of abilities (upload,delete)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new API token for a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get email from option or ask
        $email = $this->option('email') ?: $this->ask('User email address');

        // Find the user
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email '{$email}' not found");
            return Command::FAILURE;
        }

        // Get token name
        $tokenName = $this->option('name') ?: $this->ask('Token name', 'API Token');

        // Get abilities
        $abilities = $this->option('abilities');
        
        if (empty($abilities)) {
            $defaultAbilities = $this->choice(
                'Select abilities (comma-separated)',
                ['upload', 'delete', 'upload,delete'],
                2
            );
            $abilities = explode(',', $defaultAbilities);
        } else if (is_array($abilities) && count($abilities) === 1) {
            // If passed as --abilities=upload,delete it comes as single string
            $abilities = explode(',', $abilities[0]);
        }

        $abilities = array_map('trim', $abilities);

        // Validate abilities
        $validAbilities = ['upload', 'delete'];
        foreach ($abilities as $ability) {
            if (!in_array($ability, $validAbilities)) {
                $this->error("Invalid ability '{$ability}'. Valid abilities: " . implode(', ', $validAbilities));
                return Command::FAILURE;
            }
        }

        // Generate the token
        $result = $user->createToken($tokenName, $abilities);
        $token = $result['token'];
        $plainTextToken = $result['plainTextToken'];

        $this->info('API token generated successfully!');
        $this->newLine();
        $this->line('<fg=yellow>Token:</>');
        $this->line($plainTextToken);
        $this->newLine();
        $this->warn('⚠️  Make sure to copy this token now. You won\'t be able to see it again!');
        $this->newLine();
        
        $this->table(
            ['User', 'Token Name', 'Abilities', 'Created'],
            [[$user->email, $tokenName, implode(', ', $abilities), $token->created_at->format('Y-m-d H:i:s')]]
        );

        return Command::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Create (or promote) an administrator account.
 *
 * This is the only way an account comes into existence: there is no public
 * registration, and no password is ever written into source code, a seeder or
 * an environment file. The password is either typed privately at the prompt
 * or supplied per run, never stored.
 */
class MakeAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'portfolio:make-admin
        {email : The administrator e-mail address}
        {name? : The administrator name, defaults to the part before the @}
        {--password= : Supply the password on the command line instead of typing it privately}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create or update an administrator account for the dashboard';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));
        $name = trim((string) ($this->argument('name') ?: explode('@', $email)[0]));

        Validator::make(
            ['email' => $email, 'name' => $name],
            ['email' => ['required', 'email', 'max:190'], 'name' => ['required', 'string', 'max:80']],
        )->validate();

        $password = $this->resolvePassword($email);

        if ($password === null) {
            $this->error('No password was supplied, so nothing was changed.');

            return self::FAILURE;
        }

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'is_admin' => true,
                'email_verified_at' => now(),
            ],
        );

        $this->info("Administrator ready: {$user->email} (id {$user->id}).");
        $this->line('Sign in at '.route('login'));

        return self::SUCCESS;
    }

    /**
     * Read a password privately at the prompt, or take the supplied one.
     */
    private function resolvePassword(string $email): ?string
    {
        if ($this->option('password') !== null) {
            $password = (string) $this->option('password');

            if (strlen($password) < 8) {
                $this->error('The password must be at least 8 characters.');

                return null;
            }

            return $password;
        }

        if (! $this->input->isInteractive()) {
            $this->error("Running non-interactively: pass --password=... to set the password for {$email}.");

            return null;
        }

        $password = $this->secret('Password (min 8 characters)');

        if (strlen((string) $password) < 8) {
            $this->error('The password must be at least 8 characters.');

            return null;
        }

        if ($password !== $this->secret('Repeat the password')) {
            $this->error('The passwords did not match, so nothing was changed.');

            return null;
        }

        return (string) $password;
    }
}

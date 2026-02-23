<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ResetDeviceToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'device:reset {email : The email of the user to reset}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset the authorized device for a user, allowing them to login on a new device.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $user = \App\Models\User::where('email', $email)->first();

        if (!$user) {
            $this->error("User not found: $email");
            return 1;
        }

        $user->device_token = null;
        $user->save();

        $this->info("Device lock reset successfully for user: $email");
        $this->info("They can now login on a new device (which will become the new trusted device).");

        return 0;
    }
}

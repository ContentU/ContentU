<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

#[Signature('ped:test-mail {email}')]
#[Description('Invia una email di prova, per verificare la configurazione mail in produzione dalla Shell di Render')]
class SendTestMail extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');

        try {
            Mail::raw('Test invio PED', function ($message) use ($email) {
                $message->to($email)->subject('Test invio PED');
            });
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('OK');

        return self::SUCCESS;
    }
}

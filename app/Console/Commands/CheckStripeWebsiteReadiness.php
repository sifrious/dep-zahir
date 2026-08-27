<?php

namespace App\Console\Commands;

use App\Compliance\StripeWebsiteReadiness;
use Illuminate\Console\Command;

class CheckStripeWebsiteReadiness extends Command
{
    protected $signature = 'accounts:stripe-readiness';

    protected $description = 'Check whether the public site contains the information required for Stripe review';

    public function handle(StripeWebsiteReadiness $readiness): int
    {
        $missing = $readiness->missingRequirements();

        if ($missing === []) {
            $this->info('The Accounts public site is ready for Stripe website review.');

            return self::SUCCESS;
        }

        $this->error('The Accounts public site is not ready for Stripe website review.');

        foreach ($missing as $requirement) {
            $this->line("- {$requirement}");
        }

        return self::FAILURE;
    }
}

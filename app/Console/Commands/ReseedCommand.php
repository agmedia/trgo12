<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'shop:reseed', description: 'Drop all tables, migrate fresh, then seed base data')]
class ReseedCommand extends Command
{
    protected $signature = 'shop:reseed {--customers= : Number of customers to seed (default 100)}';
    protected $description = 'Drop all tables, migrate fresh, then seed base data';
    
    public function handle(): int
    {
        // Allow overriding the number of customers for the UserSeeder
        if ($this->option('customers') !== null) {
            config(['seeder.customers' => (int) $this->option('customers')]);
        }
        
        $this->info('Running migrate:fresh --seed ...');
        $this->call('migrate:fresh', ['--seed' => true]);
        $this->info('Done.');
        
        return self::SUCCESS;
    }
}

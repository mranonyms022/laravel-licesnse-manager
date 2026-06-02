<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:list-key-pairs')]
#[Description('Command description')]
class ListKeyPairs extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
    }
}

<?php

namespace Anchu\Env\Commands;

use Anchu\Env\Env;
use Illuminate\Console\Command;

/**
 * Class EncodeEnv
 * @package Anchu\Env\Commands
 *
 * how to use:
 * php artisan env:encode
 *
 */
class EncodeEnv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'env:encode';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Encode the .env.* files in env/ dir';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Env::encode();
        return Command::SUCCESS;
    }
}

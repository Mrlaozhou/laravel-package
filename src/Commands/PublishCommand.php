<?php
namespace Mrlaozhou\Package\Commands;

use Illuminate\Console\Command;
use Mrlaozhou\Package\Providers\LaravelServiceProvider;

class PublishCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package-builder:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish laravel package config.';


    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->call('vendor:publish', [
            '--provider' => LaravelServiceProvider::class,
            '--force' => '',
            '--tag' => ['config'],
        ]);
    }
}
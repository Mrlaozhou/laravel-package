<?php
namespace Mrlaozhou\Package\Commands;

use Illuminate\Console\Command;

class BuilderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package-builder {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build a new laravel package .';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $packageName                    =   $this->argument( 'name' );

        dump( $packageName );
    }
}
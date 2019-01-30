<?php
namespace Mrlaozhou\Package\Providers;

use Mrlaozhou\Package\Commands\BuilderCommand;
use Mrlaozhou\Package\Commands\PublishCommand;
use Mrlaozhou\Package\ServiceProvider;

class LaravelServiceProvider extends ServiceProvider
{
    public function boot()
    {
        //  注册命令
        if( $this->app->runningInConsole() ) {
            $this->commands( [
                BuilderCommand::class,
                PublishCommand::class
            ] );
        }
    }

    public function register()
    {
        $this->mergeConfigFrom( __DIR__ . '/../../config/package.php', 'package' );
    }

    protected function publishConfig()
    {
        $this->publishes( [
            __DIR__ . '/../config/package.php'  =>  config_path( 'package.php' )
        ], 'config' );
    }
}
<?php
namespace Mrlaozhou\Package;

use Illuminate\Support\Facades\Storage;
use \Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function boot()
    {
        $this->publishConfig();
        //  注册命令
        if( $this->app->runningInConsole() ) {
            $this->commands( [
                Commands\BuilderCommand::class,
                Commands\PublishCommand::class
            ] );
        }
    }

    public function register()
    {
        $this->mergeConfigFrom( __DIR__ . '/../config/package.php', 'package' );
    }

    /**
     * 发布配置文件
     */
    protected function publishConfig()
    {
        $this->publishes( [
            __DIR__ . '/../config/package.php'  =>  config_path( 'package.php' )
        ], 'config' );
    }
}
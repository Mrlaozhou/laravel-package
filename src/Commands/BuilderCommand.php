<?php
namespace Mrlaozhou\Package\Commands;

use Illuminate\Support\Facades\Validator;
use \Mrlaozhou\Package\Command;
use Mrlaozhou\Package\Package;
use Symfony\Component\Console\Question\Question;

class BuilderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package-builder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build a new laravel package .';

    /**
     * @var Package
     */
    protected $package;

    /**
     * Execute the console command.
     *  (black, red, green, yellow, blue, magenta, cyan, white, default)
     * @return void
     */
    public function handle()
    {
        //  提示
        $this->alert( '<fg=blue>Mrlaozhou/laravel-package</fg=blue>' );
        //  扩展包名称
        $this->package          =   new Package( $this->getPackageName() );
        //  命名空间
        $this->package->setPackageNamespace( $this->getNamespace() );
        //  作者
//        $this->package->setPackageAuthor( $this->getAuthor() );
        //  主页
        $this->package->setPackageHomepage( $this->getHomepage() );
        //  描述
        $this->package->setPackageDescription( $this->getDesc() );
        //  确认
        $this->table(
            ['配置项', '配置值'],
            [
                ['名称', $this->package->getPackageName()],
                ['命名空间', $this->package->getPackageNamespace()],
                ['作者', "{$this->package->getPackageAuthor()} <{$this->package->getPackageAuthorEmail()}>"],
                ['主页', $this->package->getPackageHomepage()],
                ['描述', $this->package->getPackageDescription()],
            ]
        );
        if( $this->confirm( '<fg=yellow>确定要创建扩展包吗?</fg=yellow>' ) ) {
            //  创建package
            $this->package->build( $this );
        }
    }

    /**
     * @return mixed
     */
    protected function getPackageName ()
    {
        $question               =   new Question('扩展包名称 (example: <fg=yellow>mrlaozhou/laravel-package</fg=yellow>): ');
        $question->setValidator(function ($value) {
            if (trim($value) == '') {
                throw new \Exception('扩展包名称不能为空');
            }
            if (!preg_match('/[a-z0-9\-_]+\/[a-z0-9\-_]+/', $value)) {
                throw new \Exception('名称无效, 格式: mrlaozhou/laravel-package');
            }
            return $value;
        });
        $question->setMaxAttempts(3);
        return $this->expectAnswerMe( $question );
    }

    /**
     * @return mixed
     */
    protected function getNamespace()
    {
        $forecastNamespace      =   $this->package->getPackageNamespace();
        $question               =   new Question("扩展命名空间 (default: <fg=yellow>{$forecastNamespace}</fg=yellow>): ");
        $question->setValidator(function ($value) use ($forecastNamespace){
            if (trim($value) == '') {
                return $value;
            }
            if (!preg_match('/^[A-Z][a-z]+(\\\[A-Z][a-zA-z]+)?$/', $value)) {
                throw new \Exception("命名空间无效, 格式: {$forecastNamespace}");
            }
            return $value;
        });
        return $this->expectAnswerMe( $question, $forecastNamespace );
    }

    /**
     * @return mixed
     */
    protected function getAuthor()
    {
        $forecastAuthor      =   $this->package->getPackageAuthor();
        $question               =   new Question("作者 (default: <fg=yellow>{$forecastAuthor}</fg=yellow>): ");
        return $this->expectAnswerMe( $question, $forecastAuthor );
    }

    /**
     * @return mixed
     */
    protected function getDesc()
    {
        $forecastDesc      =   $this->package->getPackageDescription();
        $question               =   new Question("扩展描述 (example: <fg=yellow>{$forecastDesc}</fg=yellow>): ");
        return $this->expectAnswerMe( $question, $forecastDesc );
    }

    /**
     * @return mixed
     */
    protected function getHomepage()
    {
        $question               =   new Question('扩展包主页 (example: <fg=yellow>https://github.com/Mrlaozhou/laravel-package.git</fg=yellow>): ');
        $question->setValidator(function ($value) {
            $validate           =   Validator::make(['homepage'=>$value], ['homepage'      =>  'bail|nullable|url']);
            if( $validate->fails() ) {
                throw new \Exception($validate->errors()->first());
            }else{
                return $value;
            }
        });
        $question->setMaxAttempts(3);
        return $this->expectAnswerMe( $question );
    }
}
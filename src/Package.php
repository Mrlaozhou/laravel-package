<?php
namespace Mrlaozhou\Package;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Filesystem\Filesystem;

class Package
{

    /**
     * @var string
     */
    protected $PACKAGE_NAME;

    /**
     * @var string
     */
    protected $PACKAGE_NAMESPACE;

    /**
     * @var string
     */
    protected $PACKAGE_AUTHOR           =   'mrlaozhou';

    /**
     * @var string
     */
    protected $PACKAGE_DESCRIPTION      =   'Package built by mrlaozhou/laravel-package .';

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $filesystem;


    public function __construct(string $packageName=null)
    {
        if( $packageName )
            $this->setPackageName( $packageName );
        $this->filesystem           =   new Filesystem();
    }

    public function build(Command $command)
    {
        //
        $this->hasPackageDirectory() || $this->filesystem->appendToFile( base_path('.gitignore'), "\r\n/" . config('package.package_path') );
        //  创建默认目录
        $this->makeBasicDirectories();
        //  创建扩展目录
        $this->makeExtendDirectories();
        //  迁移文件
        $this->migrateFiles();

        $command->info("扩展包 [{$this->getPackageName()}] 创建成功");
    }

    protected function makeBasicDirectories()
    {
        $this->filesystem->mkdir( $this->packageDirectory( 'src/' ), 0755 );
        $this->filesystem->mkdir( $this->packageDirectory( 'tests/' ), 0755 );
        $this->filesystem->mkdir( $this->packageDirectory( 'config/' ), 0755 );
        $this->filesystem->touch( $this->packageDirectory( 'config/' . $this->getPackageExactName() . '.php' ) );
    }

    protected function makeExtendDirectories()
    {
        collect( config('package.directories', []) )
            ->filter()
            ->unique()
            ->map(function($ex){
                $this->filesystem->mkdir( $this->packageDirectory( $ex ), 0755 );
            });
    }

    protected function migrateFiles()
    {
        //  迁移主文件
        File::put( $this->packageDirectory($this->getPackageExactName(false)), $this->getStub('dummy.php') );
        //  服务支持

    }

    protected function getStub($file)
    {
        $stubPath               =   __DIR__ . '/stubs/';
        if( File::exists( $stubPath . $file ) ) {
            return File::get( $stubPath . $file );
        }
        return '';
    }

    /**
     * 设置扩展包名称
     * @param string $packageName
     *
     * @return string
     */
    public function setPackageName(string $packageName)
    {
        $this->PACKAGE_NAME         =   $packageName;
        $this->setPackageNamespace( $packageName );
        $this->setPackageAuthor( explode( '/', $packageName )[0] );
        return $this->getPackageName();
    }

    /**
     * 获取扩展包确切名称
     * @param bool $snake
     *
     * @return string
     */
    public function getPackageExactName($snake = true)
    {
        $exactName                  =   collect(explode('\\', $this->getPackageNamespace()))->last();
        return $snake ? Str::lower( Str::snake( $exactName, '-' ) ) : $exactName;
    }

    /**
     * 获取扩展包名称
     * @return string
     */
    public function getPackageName()
    {
        return $this->PACKAGE_NAME;
    }

    /**
     * @param $namespace
     *
     * @return string
     */
    public function setPackageNamespace($namespace)
    {
        if( $namespace ) {
            $this->PACKAGE_NAMESPACE        =   collect( explode( '/', $namespace ) )
                ->map(function ($same){
                    return Str::ucfirst( Str::camel($same) );
                })->implode('\\');
        }
        return $this->getPackageNamespace();
    }

    /**
     * @return string
     */
    public function getPackageNamespace()
    {
        return $this->PACKAGE_NAMESPACE;
    }

    /**
     * @param $author
     *
     * @return string
     */
    public function setPackageAuthor(string $author=null)
    {
        if( $author ) {
            $this->PACKAGE_AUTHOR           =   $author;
        }
        return $this->getPackageAuthor();
    }

    /**
     * @return string
     */
    public function getPackageAuthor()
    {
        return $this->PACKAGE_AUTHOR;
    }

    /**
     * @param $description
     *
     * @return string
     */
    public function setPackageDescription($description)
    {
        if( $description ) {
            $this->PACKAGE_DESCRIPTION      =   $description;
        }
        return $this->getPackageDescription();
    }

    /**
     * @return string
     */
    public function getPackageDescription()
    {
        return $this->PACKAGE_DESCRIPTION;
    }

    /**
     * @param $path
     *
     * @return string
     */
    protected function packageDirectory($path='')
    {
        $packageBasicPath           =   base_path(config('package.package_path')) . '/' . $this->getPackageExactName();
        if( $path )
            return $packageBasicPath . '/' . $path;
        return $packageBasicPath;
    }

    /**
     * @return bool
     */
    protected function hasPackageDirectory()
    {
        return File::isDirectory( base_path(config('package.package_path')) );
    }
    
}
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
    protected $PACKAGE_AUTHOR;

    /**
     * @var string
     */
    protected $PACKAGE_AUTHOR_EMAIL;

    /**
     * @var string
     */
    protected $PACKAGE_HOMEPAGE         =   'https://github.com/Mrlaozhou/laravel-package.git';

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
        //  创建composer.json
        $this->initComposer();

        $command->info("扩展包 [{$this->getPackageName()}] 创建成功");
    }

    protected function makeBasicDirectories()
    {
        $this->filesystem->mkdir( $this->packageDirectory( 'src/' ), 0755 );
        $this->filesystem->mkdir( $this->packageDirectory( 'tests/' ), 0755 );
        $this->filesystem->touch( $this->packageDirectory( 'tests/.gitkeep' ) );
        $this->filesystem->mkdir( $this->packageDirectory( 'config/' ), 0755 );
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
        //  主文件
        File::put(
            $this->packageDirectory('/src/' . $this->getPackageExactName(false)) . '.php',
            $this->replaceStubDummy( $this->getStub('DummyClassName.stub') )
        );
        //  配置文件
        File::put(
            $this->packageDirectory('/config/' . $this->getPackageExactName(true)) . '.php',
            $this->replaceStubDummy( $this->getStub('config.stub') )
        );
        //  服务支持
        File::put(
            $this->packageDirectory('/src/ServiceProvider.php'),
            $this->replaceStubDummy( $this->getStub('ServiceProvider.stub') )
        );
        //  git 支持
        if( config('package.git') ) {
            $this->filesystem->copy( __DIR__ . '/../stubs/gitattributes', $this->packageDirectory('.gitattributes') );
            $this->filesystem->copy( __DIR__ . '/../stubs/gitignore', $this->packageDirectory('.gitignore') );
        }
        //  readme
        if( config('package.readme') ) {
            $this->filesystem->copy( __DIR__ . '/../stubs/README.md', $this->packageDirectory('README.md') );
        }
    }

    protected function initComposer()
    {
        $author = sprintf('%s <%s>', $this->getPackageAuthor(), $this->getPackageAuthorEmail());
        exec(sprintf(
            'composer init --no-interaction --name "%s" --author "%s" --description "%s" --working-dir %s --homepage "%s"',
            $this->getPackageName(),
            $author,
            $this->getPackageDescription(),
            $this->packageDirectory(),
            $this->getPackageHomepage()
        ));
    }

    /**
     * @param $stubFile
     *
     * @return string
     */
    protected function getStub($stubFile)
    {
        $stubPath               =   __DIR__ . '/../stubs/';
        if( File::exists( $stubPath . $stubFile ) ) {
            return File::get( $stubPath . $stubFile );
        }
        return '';
    }

    /**
     * @param $stub
     *
     * @return string
     */
    protected function replaceStubDummy(string $stub)
    {
        return str_replace(
            ['DummyClassName', 'DummyNamespace'],
            [$this->getPackageExactName(false), $this->getPackageNamespace()],
            $stub
        );
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
     * @param string|null $homepage
     *
     * @return string
     */
    public function setPackageHomepage(string $homepage=null)
    {
        if( $homepage ) {
            $this->PACKAGE_HOMEPAGE         =   $homepage;
        }
        return $this->getPackageHomepage();
    }

    /**
     * @return string
     */
    public function getPackageHomepage()
    {
        return $this->PACKAGE_HOMEPAGE;
    }

    /**
     * @return string
     */
    public function getPackageAuthor()
    {
        return config('package.author', '');
    }

    /**
     * @return string
     */
    public function getPackageAuthorEmail()
    {
        return config('package.email', '');
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
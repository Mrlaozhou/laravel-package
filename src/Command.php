<?php
namespace Mrlaozhou\Package;

use Illuminate\Support\Facades\Storage;
use \Illuminate\Console\Command as BaseCommand;
use Symfony\Component\Console\Question\Question;

class Command extends BaseCommand
{
    protected function createPackageDirectoryByName( $name )
    {
        return Storage::makeDirectory( config('package.package_path') . $name );
    }

    /**
     * @param \Symfony\Component\Console\Question\Question $question
     * @param null                                         $default
     *
     * @return mixed
     */
    protected function expectAnswerMe(Question $question, $default = null)
    {
        return $this->getHelper('question')->ask( $this->input, $this->output, $question, $default );
    }
}
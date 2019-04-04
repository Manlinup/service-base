<?php
/**
 * Created by PhpStorm.
 * User: xiaobin.shi
 * Date: 19/4/1
 * Time: 下午3:47
 */
namespace Sak\Core\Generators;

/**
 * Class BaseControllerGenerator
 * @package Sak\Core\Generators
 */
class BaseControllerGenerator extends BaseGenerator
{
    /**
     * Get stub name.
     *
     * @var string
     */
    protected $stub = 'controller/base_controller';

    /**
     * Get root namespace.
     *
     * @return string
     */
    public function getRootNamespace()
    {
        return parent::getRootNamespace() . parent::getConfigGeneratorClassPath($this->getPathConfigNode());
    }

    /**
     * Get generator path config node.
     *
     * @return string
     */
    public function getPathConfigNode()
    {
        return 'base_controller';
    }

    /**
     * Get destination path for generated file.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->getBasePath() . '/' . parent::getConfigGeneratorClassPath($this->getPathConfigNode(), true) . '/' . 'BaseController.php';
    }

    /**
     * Get base path of destination file.
     *
     * @return string
     */
    public function getBasePath()
    {
        return config('sak.generator.basePath', app_path());
    }

    /**
     * Get array replacements.
     *
     * @return array
     */
    public function getReplacements()
    {
        return array_merge(parent::getReplacements(), [

        ]);
    }
}

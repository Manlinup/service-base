<?php
/**
 * Created by PhpStorm.
 * User: xiaobin.shi
 * Date: 19/4/1
 * Time: 下午3:47
 */
namespace Sak\Core\Generators;

/**
 * Class ControllerGenerator
 * @package Sak\Core\Generators
 */
class ControllerGenerator extends BaseGenerator
{
    /**
     * Get stub name.
     *
     * @var string
     */
    protected $stub = 'controller/controller';

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
        return 'controllers';
    }

    /**
     * Get destination path for generated file.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->getBasePath() . '/' . parent::getConfigGeneratorClassPath($this->getPathConfigNode(), true) . '/' . $this->getControllerName() . 'Controller.php';
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
        $baseController = config('sak.generator.paths.base_controller', 'Http/Controllers/Api/V1') . '/BaseController';
        return array_merge(parent::getReplacements(), [
            'controller_class' => $this->getControllerName(),
            'base_controller'  => parent::getRootNamespace() . str_replace('/', '\\', $baseController),
            'class_service'    => str_replace('/', '\\', config('sak.generator.paths.services', 'Services'))
        ]);
    }

    /**
     * @return string
     */
    public function getControllerName()
    {
        return str_plural($this->getName());
    }
}

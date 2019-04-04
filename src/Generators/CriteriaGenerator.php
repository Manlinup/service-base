<?php
/**
 * Created by PhpStorm.
 * User: xiaobin.shi
 * Date: 19/4/1
 * Time: 下午3:47
 */
namespace Sak\Core\Generators;

/**
 * Class CriteriaGenerator
 * @package Sak\Core\Generators
 */
class CriteriaGenerator extends BaseGenerator
{

    /**
     * Get stub name.
     *
     * @var string
     */
    protected $stub = 'criteria/criteria';

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
     * @return string
     */
    public function getPathConfigNode()
    {
        return 'criteria';
    }

    /**
     * Get destination path for generated file.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->getBasePath() . '/' . parent::getConfigGeneratorClassPath($this->getPathConfigNode(), true) . '/' . $this->getName() . 'Criteria.php';
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
}

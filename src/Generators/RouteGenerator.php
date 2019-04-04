<?php
/**
 * Created by PhpStorm.
 * User: xiaobin.shi
 * Date: 19/4/1
 * Time: 下午3:47
 */
namespace Sak\Core\Generators;

/**
 * Class RouteGenerator
 * @package Sak\Core\Generators
 */
class RouteGenerator extends BaseGenerator
{

    /**
     * Get stub name.
     *
     * @var string
     */
    protected $stub = 'route/route';


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
        return 'base_routes';
    }

    /**
     * Get destination path for generated file.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->getBasePath() . '/../' . parent::getConfigGeneratorClassPath($this->getPathConfigNode(), true) . '/' . 'api.php';
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
        $namespace = config('sak.controllers', 'Http\Controllers\Api\V1');
        $module    = trim($this->getName());

        return array_merge(parent::getReplacements(), [
            'version'              => config('api.version', 'v1'),
            'controller_namespace' => $namespace,
            'appname'              => $this->getAppNamespace(),
            'controller'           => str_plural(studly_case($module)),
            'route'                => str_plural(snake_case($module)),
        ]);
    }

    /**
     * @return null|string|string[]
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function generateRoute()
    {
        $stub      = $this->combineRoutes();
        $apiRoutes = $this->filesystem->get(base_path('routes/').'api.php');
        $newRoutes = $apiRoutes . $stub;

        return $newRoutes;
    }

    /**
     * @return string
     */
    protected function combineRoutes()
    {
        $stub = $this->getStub();

        return $stub;
    }
    
    /**
     * Run the generator.
     *
     * @return int
     * @throws \Exception
     */
    public function run()
    {
        $this->setUp();
        $path = $this->getPath();
        if (!$this->filesystem->isDirectory($dir = dirname($path))) {
            $this->filesystem->makeDirectory($dir, 0777, true, true);
        }

        return $this->filesystem->put($path, $this->generateRoute());
    }
}

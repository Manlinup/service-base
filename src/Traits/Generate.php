<?php
/**
 * Created by PhpStorm.
 * User: xiaobin.shi
 * Date: 19/4/1
 * Time: 下午3:47
 */
namespace Sak\Core\Traits;

use Sak\Core\Generators\BaseControllerGenerator;
use Sak\Core\Generators\ControllerGenerator;
use Sak\Core\Generators\CriteriaGenerator;
use Sak\Core\Generators\ModelGenerator;
use Sak\Core\Generators\RepositoryGenerator;
use Sak\Core\Generators\RepositoryInterfaceGenerator;
use Sak\Core\Generators\CreateRequestGenerator;
use Sak\Core\Generators\ServiceGenerator;
use Sak\Core\Generators\ServiceInterfaceGenerator;
use Sak\Core\Generators\TransformerGenerator;
use Sak\Core\Generators\UpdateRequestGenerator;

/**
 * Trait Generate
 * @package Sak\Core\Traits
 */
trait Generate
{
    public function combine($module, $del = false)
    {
        //生成Model
        $modelGenerator = new ModelGenerator([
            'name'     => $module,
            'fillable' => $this->option('fillable'),
            'force'    => $this->option('force'),
        ]);
        $this->generators->push($modelGenerator);

        //生成repository的统一接口，只生成一次
        $repoName           = config('sak.generator.paths.base_repository_interface', 'Repositories/Contracts') . '/RepositoryInterface.php';
        $repoInterGenerator = new RepositoryInterfaceGenerator([
            'name' => 'Repository',
        ]);
        if (!file_exists(app_path($repoName))) {
            $this->generators->push($repoInterGenerator);
        } else {
            if ($del) {
                $this->generators->push($repoInterGenerator);
            }
        }

        //生成 repository
        $repositoryGenerator = new RepositoryGenerator([
            'name'  => $module,
            'force' => $this->option('force'),
        ]);
        $this->generators->push($repositoryGenerator);

        //生成service 的统一接口，只生成一次
        $serviceName = config('sak.generator.paths.service_interface', 'Services/Contracts') . '/ServiceInterface.php';
        $serviceInterGenerator = new ServiceInterfaceGenerator([
            'name' => 'Service',
        ]);
        if (!file_exists(app_path($serviceName))) {
            $this->generators->push($serviceInterGenerator);
        } else {
            if ($del) {
                $this->generators->push($serviceInterGenerator);
            }
        }

        //生成service
        $serviceGenerator = new ServiceGenerator([
            'name'  => $module,
            'force' => $this->option('force'),
        ]);
        $this->generators->push($serviceGenerator);

        //生成transformer
        $transformerGenerator = new TransformerGenerator([
            'name' => $module,
        ]);
        $this->generators->push($transformerGenerator);

        //生成request
        $createRequestGenerator = new CreateRequestGenerator([
            'name'  => 'Create' . $module,
            'force' => $this->option('force'),
        ]);
        $updateRequestGenerator = new UpdateRequestGenerator([
            'name'  => 'Update' . $module,
            'force' => $this->option('force'),
        ]);
        $this->generators->push($createRequestGenerator);
        $this->generators->push($updateRequestGenerator);

        //生成criteria
        $criteriaGenerator = new CriteriaGenerator([
            'name'  => $module,
            'force' => $this->option('force'),
        ]);
        $this->generators->push($criteriaGenerator);


        //生成BaseController,只生成一次
        $baseControllerName = config('sak.generator.paths.base_controller', 'Http/Controllers/Api/V1') . '/BaseController.php';
        if (!file_exists(app_path($baseControllerName))) {
            $baseControllerGenerator = new BaseControllerGenerator([
                'name' => 'Base',
            ]);
            $this->generators->push($baseControllerGenerator);
        }
        //生成controller
        $controllerGenerator = new ControllerGenerator([
            'name'  => $module,
            'force' => $this->option('force'),
        ]);
        $this->generators->push($controllerGenerator);

        return $this->generators;
    }
}

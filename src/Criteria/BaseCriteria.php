<?php
/**
 * Created by PhpStorm.
 * User: xiaobin.shi
 * Date: 19/4/1
 * Time: 下午3:47
 */
namespace Sak\Core\Criteria;

use Prettus\Repository\Contracts\RepositoryInterface;
use Prettus\Repository\Contracts\CriteriaInterface;

/**
 * Class BaseCriteria
 * @package Sak\Core\Criteria
 */
abstract class BaseCriteria implements CriteriaInterface
{
    public function apply($model, RepositoryInterface $repository)
    {
        return $model;
    }

    /**
     * hook for inject virtual field search
     *
     * @param $model
     * @param $repository
     * @param $case
     * @return mixed
     */
    public function processVirtualFields($model, $repository, $case)
    {
        return $model;
    }

    /**
     * hook for inject virtual field order
     *
     * @param $model
     * @param $repository
     * @param $field
     * @param $direction
     * @return mixed
     */
    public function processOrderByVirtualFields($model, $repository, $field, $direction)
    {
        return $model;
    }
}

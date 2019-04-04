<?php
/**
 * Created by PhpStorm.
 * User: xiaobin.shi
 * Date: 19/4/1
 * Time: 下午3:47
 */
namespace Sak\Core\Transformers\Adapter;

use Dingo\Api\Transformer\Adapter\Fractal as BaseAdapter;
use League\Fractal\Manager as FractalManager;
use Sak\Core\Serializer\SakSerializer;

/**
 * Class SakFractal
 * @package Sak\Core\Transformers\Adapter
 */
class Fractal extends BaseAdapter
{
    /**
     * SakFractal constructor.
     * @param FractalManager $fractal
     * @param string $includeKey
     * @param string $includeSeparator
     * @param bool $eagerLoading
     */
    public function __construct(
        FractalManager $fractal,
        $includeKey = 'include',
        $includeSeparator = ',',
        $eagerLoading = true
    ) {
        $fractal->setSerializer(new SakSerializer());
        parent::__construct($fractal, $includeKey, $includeSeparator, $eagerLoading);
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: xiaobin.shi
 * Date: 19/4/1
 * Time: 下午3:47
 */
namespace Sak\Core\Transformers;

use Illuminate\Console\DetectsApplicationNamespace;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\MessageBag;

use Prettus\Validator\Exceptions as RepositoryExceptions;
use League\Fractal\TransformerAbstract;

class BaseTransformer extends TransformerAbstract
{

    use DetectsApplicationNamespace;

    protected $hidden = [];

    public function transform($model)
    {
        $result      = [];
        $originModel = $model;
        if ($model instanceof Model) {
            $model = new Collection([$model]);
        }
        if ($model instanceof Collection) {
            foreach ($model as $item) {
                $data = $this->setData($item);
                if (!$this->defaultIncludes) {
                    $data = $this->parseRelations($item, $data);
                }
                $result[] = $this->filter($data);
            }
        }
        if ($originModel instanceof Model) {
            $result = current($result);
        }
        return $result;
    }

    protected function humpToLine($str)
    {
        $str = preg_replace_callback('/([A-Z])/', function ($matches) {
            return '_' . strtolower($matches[0]);
        }, $str);
        return $str;
    }

    protected function getAttributes(Model $item)
    {
        $attributes   = $item->getAttributes();
        $hiddenFields = $item->getHidden();
        if (count($hiddenFields) > 0) {
            $attributes = array_diff_key($attributes, array_flip($hiddenFields));
        }
        return $attributes;
    }

    protected function getHiddenFields()
    {
        return $this->hidden;
    }

    protected function setData(Model $item)
    {
        $data = [];

        $hiddenFields = array_merge($item->getHidden(), $this->getHiddenFields());
        foreach ($this->getAttributes($item) as $attr => $value) {
            if (!in_array($attr, $hiddenFields)) {
                $data[$this->humpToLine($attr)] = $item->$attr;
            }
        }
        return $data;
    }

    protected function parseRelations(Model $item, array $data)
    {
        try {
            foreach ($item->getRelations() as $relation => $model) {
                if ($model instanceof Pivot) {
                    continue;
                }

                $result = [];

                if ($model instanceof Model || $model instanceof Collection) {
                    $relatedModelName = class_basename($item->$relation()->getRelated());
                    if (empty($relatedModelName)) {
                        continue;
                    }
                    //通关model的relation获取
                    $name        = studly_case($relatedModelName);
                    $transformer = $this->getAppNamespace() . 'Transformers\\' . $name . 'Transformer';
                    if (!class_exists($transformer)) {
                        $baseNameSpace = explode('\\', substr(get_class($item), 0, -1 - strlen(class_basename(get_class($item)))));
                        $transformer   = $this->getAppNamespace() . 'Transformers\\' . end($baseNameSpace) . '\\' . $name . 'Transformer';
                    }
                    $result = app($transformer)->transform($model);
                }
                $data[$this->humpToLine($relation)] = $result;
            }
        } catch (\Exception $e) {
            $msg = new MessageBag(['Failed to parse relations.']);
            throw new RepositoryExceptions\ValidatorException($msg);
        }
        return $data;
    }

    protected function filter(array $data)
    {
        $config    = 'repository.criteria.params.filter';
        $filterStr = Request::get(config($config, 'filter'), null);
        $filters   = preg_split('/\s*,\s*/', $filterStr);

        foreach ($filters as $filter) {
            list($relation, $field) = array_pad(explode('.', $filter), 2, null);
            if ($filter) {
                Arr::forget($data, implode('.', array_filter([$relation, $field])));
            }
        }
        return $data;
    }
}

<?php

namespace Sak\Core\Serializer;

use League\Fractal\Pagination\CursorInterface;
use League\Fractal\Pagination\PaginatorInterface;
use League\Fractal\Serializer\ArraySerializer;
use Illuminate\Support\Facades\Request;

class SakSerializer extends ArraySerializer
{
    /**
     * @param string $resourceKey
     * @param array $data
     * @return array
     */
    public function collection($resourceKey, array $data)
    {
        if ($resourceKey === 'fold') {
            return $data;
        } else {
            return [$resourceKey ?: 'data' => $data];
        }
    }

    /**
     * @param string $resourceKey
     * @param array $data
     * @return array
     */
    public function item($resourceKey, array $data)
    {
        return $data;
    }

    /**
     * Serialize null resource.
     *
     * @return array
     */
    public function null()
    {
        return [];
    }

    /**
     * Serialize the meta.
     *
     * @param array $meta
     *
     * @return array
     */
    public function meta(array $meta)
    {
        if (empty($meta)) {
            return [];
        }
        return current($meta);
    }

    /**
     * Serialize the paginator.
     *
     * @param PaginatorInterface $paginator
     *
     * @return array
     */
    public function paginator(PaginatorInterface $paginator)
    {
        $pagination = [
            'start' => (int)Request::get('start', config('sak.pagination.start', 0)),
            'rows'  => (int)Request::get('rows', config('sak.pagination.rows', 20)),
            'total' => (int)$paginator->getTotal(),
        ];
        return [$pagination];
    }

    /**
     * Serialize the cursor.
     *
     * @param CursorInterface $cursor
     *
     * @return array
     */
    public function cursor(CursorInterface $cursor)
    {
        $cursor = [
            'current' => $cursor->getCurrent(),
            'prev'    => $cursor->getPrev(),
            'next'    => $cursor->getNext(),
            'count'   => (int)$cursor->getCount(),
        ];

        return ['cursor' => $cursor];
    }
}

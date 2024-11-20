<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Modify data for recipe search previews as they can be paginated.
 *
 * @package App\Http\Resources
 */
abstract class PaginatedJsonResource extends JsonResource
{
    /**
     * @var array|null
     */
    protected ?array $paginatedData = null;

    /**
     * Create a new resource instance.
     *
     * @param mixed $resource
     * @return void
     */
    public function __construct($resource)
    {
        if ($resource instanceof LengthAwarePaginator) {
            $this->paginatedData = [
                'current_page'   => $resource->currentPage(),
                'data'           => [],
                'first_page_url' => $resource->url(1),
                'from'           => $resource->firstItem(),
                'last_page'      => $resource->lastPage(),
                'last_page_url'  => $resource->url($resource->lastPage()),
                'links'          => $resource->linkCollection()->toArray(),
                'next_page_url'  => $resource->nextPageUrl(),
                'path'           => $resource->path(),
                'per_page'       => $resource->perPage(),
                'prev_page_url'  => $resource->previousPageUrl(),
                'to'             => $resource->lastItem(),
                'total'          => $resource->total(),
            ];

            $resource = $resource->getCollection();
        }
        parent::__construct($resource);
    }
}

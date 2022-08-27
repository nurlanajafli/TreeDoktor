<?php


namespace application\core\Extenders;


class LengthAwarePaginator extends \Illuminate\Pagination\LengthAwarePaginator
{
    public function __construct($items, $total, $perPage, $currentPage = null, array $options = [])
    {
        parent::__construct($items, $total, $perPage, $currentPage, $options);
    }

    /**
     * Get the URL for a given page number.
     *
     * @param int $page
     * @return string
     */
    public function url($page)
    {
        if ($page <= 0) {
            $page = 1;
        }

        // If we have any extra query string key / value pairs that need to be added
        // onto the URL, we will put them in query string form and then attach it
        // to the URL. This allows for extra information like sortings storage.
        //$parameters = [$this->pageName => $page];
        $CI = &get_instance();
        $segments = app('request')->segments();
        array_pop($segments);
        $segments[] = $page;

        $query = app('request')->getQueryString();

        return base_url(implode('/', $segments))
            . ($query !== null ? '?' . $query : "")
            . $this->buildFragment();
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'current_page' => $this->currentPage(),
            'data' => $this->items->toArray(),
            'first_page_url' => $this->url(1),
            'from' => $this->firstItem(),
            'last_page' => $this->lastPage(),
            'last_page_url' => $this->url($this->lastPage()),
            'next_page_url' => $this->nextPageUrl(),
            'path' => $this->path(),
            'per_page' => $this->perPage(),
            'prev_page_url' => $this->previousPageUrl(),
            'prev_page' => $this->onFirstPage() ? null : $this->currentPage() - 1,
            'next_page' => $this->hasMorePages() ? $this->currentPage() + 1 : null,
            'to' => $this->lastItem(),
            'total' => $this->total(),
            'elements' => $this->elements(),
            'on_first_page' => $this->onFirstPage(),
            'has_more_pages' => $this->hasMorePages()
        ];
    }
}
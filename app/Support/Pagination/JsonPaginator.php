<?php

namespace MotionArray\Support\Pagination;

use Illuminate\Pagination\LengthAwarePaginator;

class JsonPaginator extends LengthAwarePaginator
{
    public function toArray(){
        return [
            'current_page' => $this->currentPage(),
            'from' => $this->firstItem(),
            'last_page' => $this->lastPage(),
            'per_page' => $this->perPage(),
            'to' => $this->lastItem(),
            'total' => $this->total(),
        ];
    }
}

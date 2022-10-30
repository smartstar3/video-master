<?php

namespace MotionArray\Http\Requests\Site;

use Illuminate\Foundation\Http\FormRequest;

class BrowseSellerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [];
    }

    public function page(): int
    {
        $page = $this->query->getInt('page', 1);
        if ($page < 1) {
            $page = 1;
        }
        return $page;
    }

    public function perPage(int $default): int
    {
        $perPage = $this->query->getInt('show', $default);
        $validPerPage = [
            24,
            48,
            72,
            96
        ];

        if (!in_array($perPage, $validPerPage)) {
            $perPage = 24;
        }
        return $perPage;
    }

    public function filter(): ?string
    {
        $filter = $this->query->get('filter');
        $validFilters = [
            'downloads',
            'downloads-6months',
            'downloads-alltime',
        ];

        if (!in_array($filter, $validFilters)) {
            return null;
        }
        return $filter;
    }
}

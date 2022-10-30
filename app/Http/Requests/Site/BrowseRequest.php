<?php

namespace MotionArray\Http\Requests\Site;

use Illuminate\Foundation\Http\FormRequest;

class BrowseRequest extends FormRequest
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

    public function getPreviousUrl(): ?string
    {
        $fullUrl = url()->full();
        $page = $this->page();

        if ($page > 1) {
            return str_replace('page=' . $page, 'page=' . ($page - 1), $fullUrl);
        }

        return null;
    }

    public function getNextUrl(): ?string
    {
        $fullUrl = url()->full();
        $page = $this->page();

        if ($page > 1) {
            $url = str_replace('page=' . $page, 'page=' . ($page + 1), $fullUrl);
        } else {
            $url = $fullUrl;

            $query = parse_url($url, PHP_URL_QUERY);

            if ($query) {
                $url .= '&page=2';
            } else {
                $url .= '?page=2';
            }
        }

        return $url;
    }
}

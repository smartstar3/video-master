<?php

namespace MotionArray\Repositories;

use Illuminate\Database\Eloquent\Model;

class EloquentBaseRepository
{
    protected $urlRegex = '/(?i)\b((?:[a-z][\w-]+:(?:\/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'\".,<>?«»“”‘’]))/';

    protected $model;

    public function all()
    {
        return $this->model->all();
    }

    /**
     * @param $id
     * @return Model|null
     */
    public function findById($id, $includeTrashed = false)
    {
        $query = $this->model;

        if ($includeTrashed) {
            $query = $query->withTrashed();
        }

        return $query->find((int) $id);
    }

    /**
     * @param $slug
     * @return mixed
     */
    public function findBySlug($slug)
    {
        return $this->model->where('slug', '=', $slug)->first();
    }

    /**
     * Turn URL's into Anchors
     *
     * @param $string
     *
     * @return mixed
     */
    public function parseLinks($string)
    {
        return preg_replace($this->urlRegex, '<a href="$0" target="_blank">$0</a>', $string);
    }

    /**
     * Return value of key if it exists or return default
     *
     * @param $array
     * @param $key
     * @param null $default
     *
     * @return null
     */
    public function valueOrDefault($array, $key, $default = null)
    {
        if (array_key_exists($key, $array)) {
            if (is_null($array[$key]) || $array[$key] == '') {
                return $array[$key] = null;
            } elseif (is_numeric($array[$key]) && $array[$key] == 0) {
                return $array[$key] = 0;
            } elseif (!empty($array[$key])) {
                return $array[$key];
            }
        }

        return $default;
    }


    /**
     * New line to Paragraph
     *
     * @param $string
     *
     * @return string
     */
    function nl2p($string)
    {
        $paragraphs = '';

        foreach (explode("\n", $string) as $line) {
            if (trim($line)) {
                $paragraphs .= '<p>' . $line . '</p>';
            }
        }

        return $paragraphs;
    }


    /**
     * Format HTML
     *
     * @param $string
     *
     * @return mixed
     */
    public function formatHtml($string)
    {
        $string = strip_tags($string);

        return $this->nl2p($this->parseLinks($string));
    }

}

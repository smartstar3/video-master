<?php

namespace MotionArray\Support\Database;

use JesusRugama\Rememberable\Query\Builder as QueryBuilder;

class Builder extends QueryBuilder
{
    public static $disableCache = false;

    /**
     * Execute the query as a "select" statement.
     *
     * @param  array $columns
     * @return array|static[]
     */
    public function get($columns = ['*'])
    {
        return parent::get($columns);
    }

    /**
     * Run the query as a "select" statement against the connection.
     *
     * @return array
     */
    protected function runSelect()
    {
        // No query cache for console commands, runs out of memory.
        if (self::$disableCache or app()->runningInConsole()) {
            return parent::runSelect();
        }

        if (is_null($this->cacheMinutes)) {
            $this->remember(1)->cacheDriver('array');
        }

        return $this->getCached($this->columns);
    }

    /**
     * Get the Closure callback used when caching queries.
     *
     * @param  array $columns
     * @return \Closure
     */
    protected function getCacheCallback($columns)
    {
        return function () use ($columns) {
            return parent::runSelect();
        };
    }
}
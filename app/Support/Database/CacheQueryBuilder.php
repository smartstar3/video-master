<?php

namespace MotionArray\Support\Database;

trait CacheQueryBuilder
{
    /**
     * Get a new query builder instance for the connection.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function newBaseQueryBuilder()
    {
        $connection = $this->getConnection();

        return new Builder(
            $connection, $connection->getQueryGrammar(), $connection->getPostProcessor()
        );
    }

    /**
     * Reload a fresh model instance from the database.
     *
     * @param  array|string $with
     * @return $this|null
     */
    public function fresh($with = [])
    {
        $oldValue = Builder::$disableCache;

        Builder::$disableCache = true;

        $response = parent::fresh($with);

        Builder::$disableCache = $oldValue;

        return $response;
    }
}
<?php

namespace MotionArray\Models\StaticData;

use App\Services\Support\StaticDBData\Exceptions\StaticDBDataInvalidModelException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use MotionArray\Models\StaticData\Exceptions\StaticDBDataNotFoundException;

abstract class StaticDBData
{
    protected $modelClass;

    protected static $cachedData = [];

    protected $data = [];

    public function data(): array
    {
        $key = static::class;

        $cached = array_get(static::$cachedData, $key);
        if (!$cached) {
            static::$cachedData[$key] = $this->prepareData();
        }

        return static::$cachedData[$key];
    }

    public function dataCollection(): Collection
    {
        return collect($this->data());
    }

    public function find($idOrSlug): ?array
    {
        $id = $this->toId($idOrSlug);
        return $this->data()[$id];
    }

    public function findOrFail($idOrSlug): array
    {
        $id = $this->toIdOrFail($idOrSlug);
        return $this->data()[$id];
    }

    public function idToSlugOrFail(int $id): string
    {
        $slug = $this->idToSlug($id);

        if (!$slug) {
            throw new StaticDBDataNotFoundException(static::class, $id, 'id');
        }

        return $slug;
    }

    public function slugToIdOrFail(string $slug): int
    {
        $id = $this->slugToId($slug);

        if (!$id) {
            throw new StaticDBDataNotFoundException(static::class, $slug, 'slug');
        }

        return $id;
    }

    /**
     * @param int|string|Model $value
     * @return int
     * @throws StaticDBDataNotFoundException
     * @throws StaticDBDataInvalidModelException
     */
    public function toIdOrFail($value): int
    {
        $id = $this->toId($value);

        if (!$id || !is_int($id)) {
            if ($this->isModel($value)) {
                throw new StaticDBDataInvalidModelException(static::class, $value);
            }
            throw new StaticDBDataNotFoundException(static::class, $value);
        }

        return $id;
    }

    public function idToSlug(int $id): ?string
    {
        return array_get($this->data(), $id . '.slug');
    }

    public function slugToId(string $slug): ?int
    {
        $value = $this->bySlug($slug);
        return array_get($value, 'id');
    }

    protected function bySlug(string $slug): ?array
    {
        return collect($this->data())
            ->firstWhere('slug', '=', $slug);
    }

    /**
     * @param string|int|Model $value
     * @return int|null
     */
    public function toId($value)
    {
        $valueId = $value;

        if (is_string($value)) {
            if (is_numeric($value)) {
                $valueId = (int)$valueId;
            } else {
                $valueId = $this->slugToId($value);
            }
        }

        if ($this->isModel($value)) {
            $valueId = $value->id;
        }

        if (!$this->idExists($valueId)) {
            $valueId = false;
        }
        return $valueId;
    }

    public function idExists($id): bool
    {
        if (is_string($id) || is_int($id)) {
            return array_key_exists($id, $this->data());
        }
        return false;
    }

    protected function convertToModel($value)
    {
        $id = $this->toId($value);

        $model = new $this->modelClass;

        $attributes = $this->data()[$id];
        $model->forceFill($attributes);

        return $model;
    }

    protected function isModel($value)
    {
        return $value instanceof $this->modelClass;
    }

    protected function prepareData(): array
    {
        return collect($this->data)
            ->keyBy('id')
            ->toArray();
    }
}

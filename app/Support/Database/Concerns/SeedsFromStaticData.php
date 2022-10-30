<?php

namespace MotionArray\Support\Database\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use MotionArray\Models\StaticData\StaticDBData;
use SebastianBergmann\Comparator\ArrayComparator;
use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Comparator\Factory;

trait SeedsFromStaticData
{
    /** @var StaticDBData */
    protected $staticData;

    /** @var Model */
    protected $model;

    public function run()
    {
        if ($this->shouldCompare()) {
            $this->runAndCompare(function () {
                $this->runRaw();
            });
        } else {
            $this->runRaw();
        }
    }

    protected function runRaw()
    {
        $data = $this->prepareStaticData();
        $this->seedData($data);
        $this->deleteNonMatchingIds($data);
    }

    protected function runAndCompare(Closure $callback)
    {
        if ($this->shouldCompare()) {
            echo '  (comparing data before and after)' . PHP_EOL;
        }
        $from = $this->getDataSnapshot();
        $callback();
        $to = $this->getDataSnapshot();

        $d = new ArrayComparator();
        $d->setFactory(new Factory);

        try {
            $d->assertEquals($from, $to);
        } catch (ComparisonFailure $e) {
            echo 'seeder: ' . static::class;
            echo $e->toString();
            throw $e;
        }
    }

    protected function getDataSnapshot()
    {
        $query = $this->model->query()
            ->orderBy('id');

        if ($this->modelHasSoftDelete()) {
            $query->withTrashed();
        }

        return $query->get()
            ->map(function ($row) {
                return array_except($row, $this->excludeFromDataSnapshot());
            })
            ->keyBy('id')
            ->toArray();
    }

    protected function excludeFromDataSnapshot()
    {
        return ['created_at', 'updated_at'];
    }

    protected function seedData(array $data)
    {
        foreach ($data as $item) {
            $query = $this->model->query();

            if ($this->modelHasSoftDelete()) {
                $query->withTrashed();
            }
            $this->updateOrCreate($query, $item);

        }
    }

    protected function updateOrCreate($query, array $item)
    {
        foreach ($item as $key => $value) {
            if (!$this->model->isFillable($key)) {
                $modelClass = get_class($this->model);
                throw new \Exception("'{$key}' attribute is not fillable in model: {$modelClass}");
            }
        }
        $class = get_class($this->model);

        if (method_exists($class, 'withoutValidation')) {
            $class::withoutValidation(function () use ($query, $item) {
                $where = $this->seedDataWhere($item);
                $query->updateOrCreate($where, $item);
            });
        } else {
            $where = $this->seedDataWhere($item);
            $query->updateOrCreate($where, $item);
        }
    }

    protected function seedDataWhere(array $item): array
    {
        return array_only($item, 'id');
    }

    protected function deleteNonMatchingIds(array $data)
    {
        $ids = collect($data)
            ->pluck('id')
            ->toArray();

        $this->model->query()
            ->whereNotIn('id', $ids)
            ->delete();
    }

    protected function shouldCompare(): bool
    {
        static $shouldCompare;

        if ($shouldCompare === null) {
            $shouldCompare = _env('DB_SEED_COMPARE_BEFORE_AND_AFTER', false);
        }

        return $shouldCompare;
    }

    protected function prepareStaticData(): array
    {
        return $this->staticData->data();
    }

    protected function modelHasSoftDelete()
    {
        $traits = class_uses_recursive($this->model);
        return in_array(SoftDeletes::class, $traits);
    }
}

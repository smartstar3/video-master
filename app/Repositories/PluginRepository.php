<?php namespace MotionArray\Repositories;

use MotionArray\Models\CategoryGroup;
use MotionArray\Models\Plugin;
use MotionArray\Models\PluginCategory;
use MotionArray\Repositories\EloquentBaseRepository;

class PluginRepository extends EloquentBaseRepository
{
    public function __construct(Plugin $plugin)
    {
        $this->model = $plugin;
    }

    public function search($searchQuery, CategoryGroup $categoryGroup = null, PluginCategory $pluginCategory = null)
    {
        $query = $this->model->query();

        if ($searchQuery) {
            $words = explode(' ', trim($searchQuery));

            $query->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $q->where('name', 'like', '%' . $word . '%');
                    $q->orWhere('description', 'like', '%' . $word . '%');
                }
            });
        }

        if ($pluginCategory) {
            $query->whereHas('category', function ($q) use ($pluginCategory) {
                $q->where('id', '=', $pluginCategory->id);
            });
        } elseif ($categoryGroup) {
            $query->whereHas('category', function ($q) use ($categoryGroup) {
                $q->where('category_group_id', '=', $categoryGroup->id);
            });
        }

        $query->orderBy('created_at', 'DESC');

        return $query->get();
    }

    public function getRelatedPlugins(Plugin $plugin)
    {
        $relatedPlugins = $this->search('', null, $plugin->category);

        if ($relatedPlugins->count() < 2) {
            $relatedPlugins = $this->all();
        }

        $relatedPlugins = $relatedPlugins->filter(function ($value) use ($plugin) {
            return $value->id != $plugin->id;
        });

        return $relatedPlugins;
    }
}

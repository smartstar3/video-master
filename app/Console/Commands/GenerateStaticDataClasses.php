<?php

namespace MotionArray\Console\Commands;

use Illuminate\Console\Command;
use MotionArray\Models\AccessServiceCategory;
use MotionArray\Models\AutoDescription;
use MotionArray\Models\Bpm;
use MotionArray\Models\Category;
use MotionArray\Models\CategoryType;
use MotionArray\Models\Collection;
use MotionArray\Models\CustomGallery;
use MotionArray\Models\EncodingStatus;
use MotionArray\Models\EventCode;
use MotionArray\Models\Format;
use MotionArray\Models\Fps;
use MotionArray\Models\Plan;
use MotionArray\Models\ProductChangeOption;
use MotionArray\Models\ProductLevel;
use MotionArray\Models\ProductPlugin;
use MotionArray\Models\ProductStatus;
use MotionArray\Models\Resolution;
use MotionArray\Models\Role;
use MotionArray\Models\SampleRate;
use MotionArray\Models\SubCategory;
use MotionArray\Models\SubmissionStatus;

class GenerateStaticDataClasses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'motionarray:generate-lookup-data-classes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Use database to generate static data classes';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $modelClasses = [
//            Category::class,
//            CategoryGroup::class,
//            RequestStatus::class,
//            Version::class,
//            PluginCategory::class,
//            CustomGallery::class
//            Collection::class,
        ];

        foreach ($modelClasses as $modelClass) {

            $this->info('making: ' . $modelClass);
            $table = (new $modelClass)->getTable();

            $except = ['created_at', 'updated_at', 'deleted_at'];
            $rows = $this->getRows($table, $except);
            $rows = $this->prepareRows($rows);

            $name = class_basename($modelClass);
            $className = str_plural($name);

            $this->makeFile($className, $modelClass, $rows);
        }

        $this->makeSubCategories();

//        $this->makeProductChangeOptions();
//        $this->makeCollection();
//        $this->makeAutoDescriptions();
//        $this->makeCategoryType();

//        $this->makePlans();
//        $this->makeBpms();
//        $this->makeRoles();
//        $this->makeFpss();
//        $this->makeProductLevels();
//        $this->makeFormats();
//        $this->makeProductPlugins();
//        $this->makeSampleRates();
//        $this->makeProductStatuses();
//        $this->makeEventCodes();
//        $this->makeEncodingStatuses();
//        $this->makeAccessServiceCategories();
//        $this->makeSubmissionStatuses();
//        $this->makeResolutions();
    }


    protected function makeSubCategories()
    {
        $modelClass = SubCategory::class;

        $this->info('making: ' . $modelClass);
        $table = (new $modelClass)->getTable();

        $slugColumn = 'slug';
        $except = ['created_at', 'updated_at'];
        $rows = \DB::table($table)
            ->orderBy('category_id')
            ->orderBy('id')
            ->get()
            ->map(function ($row) use ($except, $slugColumn) {
                $row = (array)$row;
                $row = array_except($row, $except);

                // make id and slug columns first
                $row = array_merge(
                    [
                        'id' => $row['id'],
                        $slugColumn => $row[$slugColumn]
                    ],
                    $row
                );

                return $row;
            })
            ->toArray();

        $rows = $this->prepareRows($rows);


        $rows = collect($rows)
            ->map(function ($row) {

                $category = Category::find($row['item']['category_id']);

                $prefix = $this->slugToConst($category->slug);

                $row['const']['slug'] = $prefix . '_' . $row['const']['slug'];
                $row['const']['id'] = $prefix . '_' . $row['const']['id'];

                $row['item']['category_id'] = 'Categories::' . $prefix . '_ID';

                return $row;
            })
            ->toArray();

        $name = class_basename($modelClass);
        $className = str_plural($name);

        $this->makeFile($className, $modelClass, $rows);
    }


    protected function makeProductChangeOptions()
    {
        $modelClass = ProductChangeOption::class;

        $this->info('making: ' . $modelClass);
        $table = (new $modelClass)->getTable();

        $except = ['created_at', 'updated_at'];
        $rows = $this->getRows($table, $except);
        $rows = $this->prepareRows($rows);

        $rows = collect($rows)
            ->map(function ($row) {
                $const = $row['const']['slug'];
                $row['const']['slug'] = $const . '_CHANGED';
                $row['const']['id'] = $const . '_CHANGED_ID';
                return $row;
            })
            ->toArray();

        $name = class_basename($modelClass);
        $className = str_plural($name);

        $this->makeFile($className, $modelClass, $rows);
    }

    protected function makeCollection()
    {
        $modelClass = Collection::class;

        $this->info('making: ' . $modelClass);
        $table = (new $modelClass)->getTable();

        $except = ['created_at', 'updated_at'];
        $rows = $this->getRows($table, $except);
        $rows = $this->prepareRows($rows);

        $rows = collect($rows)
            ->map(function ($row) {

                $name = $row['item']['title'];
                $const = mb_strtoupper(str_replace(' ', '_', $name));
                $row['const']['slug'] = $const;
                $row['const']['id'] = $const . '_ID';
                return $row;
            })
            ->toArray();

        $name = class_basename($modelClass);
        $className = str_plural($name);

        $this->makeFile($className, $modelClass, $rows);
    }

    protected function makeFpss()
    {
        $modelClass = Fps::class;

        $this->info('making: ' . $modelClass);
        $table = (new $modelClass)->getTable();

        $except = ['created_at', 'updated_at', 'deleted_at'];
        $rows = $this->getRows($table, $except);
        $rows = $this->prepareRows($rows);

        $rows = collect($rows)
            ->map(function ($row) {
                $row['const']['slug'] = 'FPS_' . $row['const']['slug'];
                $row['const']['id'] = 'FPS_' . $row['const']['id'];
                return $row;
            })
            ->toArray();

        $name = class_basename($modelClass);
        $className = str_plural($name);

        $this->makeFile($className, $modelClass, $rows);
    }

    protected function makeBpms()
    {
        $modelClass = Bpm::class;
        $slugColumn = 'name';

        $this->info('making: ' . $modelClass);
        $table = (new $modelClass)->getTable();

        $except = ['created_at', 'updated_at', 'deleted_at'];
        $rows = $this->getRows($table, $except, $slugColumn);
        $rows = $this->prepareRows($rows, $slugColumn);

        $rows = collect($rows)
            ->map(function ($row) {
                $row['const']['name'] = 'BPM_' . $row['const']['name'];
                $row['const']['id'] = 'BPM_' . $row['const']['id'];
                return $row;
            })
            ->toArray();

        $name = class_basename($modelClass);
        $className = str_plural($name);

        $this->makeFile($className, $modelClass, $rows);
    }

    protected function makeAutoDescriptions()
    {
        $modelClass = AutoDescription::class;
        $slugColumn = 'name';

        $this->make($modelClass, $slugColumn);
    }

    protected function makeCategoryType()
    {
        $modelClass = CategoryType::class;
        $slugColumn = 'name';

        $this->make($modelClass, $slugColumn);
    }

    protected function makePlans()
    {
        $modelClass = Plan::class;
        $slugColumn = 'billing_id';

        $this->make($modelClass, $slugColumn);
    }

    protected function makeRoles()
    {
        $modelClass = Role::class;
        $slugColumn = 'name';

        $this->make($modelClass, $slugColumn);
    }

    protected function makeProductLevels()
    {
        $modelClass = ProductLevel::class;
        $slugColumn = 'label';

        $this->make($modelClass, $slugColumn);
    }

    protected function makeFormats()
    {
        $modelClass = Format::class;
        $slugColumn = 'name';

        $this->make($modelClass, $slugColumn);
    }

    protected function makeProductPlugins()
    {
        $modelClass = ProductPlugin::class;
        $slugColumn = 'name';

        $this->make($modelClass, $slugColumn);
    }

    protected function makeSampleRates()
    {
        $modelClass = SampleRate::class;
        $slugColumn = 'name';

        $this->make($modelClass, $slugColumn);
    }

    protected function makeProductStatuses()
    {
        $modelClass = ProductStatus::class;
        $slugColumn = 'status';

        $this->make($modelClass, $slugColumn);
    }

    protected function makeEventCodes()
    {
        $modelClass = EventCode::class;
        $slugColumn = 'event';

        $this->make($modelClass, $slugColumn);
    }

    protected function makeEncodingStatuses()
    {
        $modelClass = EncodingStatus::class;
        $slugColumn = 'status';

        $this->make($modelClass, $slugColumn);
    }

    protected function makeAccessServiceCategories()
    {
        $modelClass = AccessServiceCategory::class;
        $slugColumn = 'name';

        $this->make($modelClass, $slugColumn);
    }

    protected function makeSubmissionStatuses()
    {
        $modelClass = SubmissionStatus::class;
        $slugColumn = 'status';

        $this->make($modelClass, $slugColumn);
    }

    protected function makeResolutions(): void
    {
        $modelClass = Resolution::class;

        $this->info('making: ' . $modelClass);
        $table = (new $modelClass)->getTable();

        $except = ['created_at', 'updated_at', 'deleted_at'];
        $rows = $this->getRows($table, $except);
        $rows = $this->prepareRows($rows);

        $rows = collect($rows)
            ->map(function ($row) {
                $row['const']['slug'] = 'RES_' . $row['const']['slug'];
                $row['const']['id'] = 'RES_' . $row['const']['id'];
                return $row;
            })
            ->toArray();

        $name = class_basename($modelClass);
        $className = str_plural($name);

        $this->makeFile($className, $modelClass, $rows);
    }

    protected function make($modelClass, $slugColumn = 'slug', $except = ['created_at', 'updated_at', 'deleted_at'])
    {
        $this->info('making: ' . $modelClass);
        $table = (new $modelClass)->getTable();

        $rows = $this->getRows($table, $except, $slugColumn);
        $rows = $this->prepareRows($rows, $slugColumn);

        $name = class_basename($modelClass);
        $className = str_plural($name);

        $this->makeFile($className, $modelClass, $rows);
    }

    protected function getRows($table, array $except = [], $slugColumn = 'slug'): array
    {
        $rows = \DB::table($table)
            ->get()
            ->map(function ($row) use ($except, $slugColumn) {
                $row = (array)$row;
                $row = array_except($row, $except);

                // make id and slug columns first
                $row = array_merge(
                    [
                        'id' => $row['id'],
                        $slugColumn => $row[$slugColumn]
                    ],
                    $row
                );

                return $row;
            })
            ->toArray();

        return $rows;
    }

    function render($file, $args)
    {
        ob_start();
        extract($args);
        include $file;
        return ob_get_clean();
    }

    function slugToConst($slug)
    {
        $slug = str_replace(['-', ' '], '_', $slug);
        return mb_strtoupper($slug);
    }

    protected function makeFile($className, $modelClass, array $rows): void
    {
        $args = [
            'className' => $className,
            'modelClass' => $modelClass,
            'rows' => $rows,
        ];

        $view = $this->render(__DIR__ . '/Stubs/StaticDBData.stub.php', $args);

        $view = '<?php' . PHP_EOL . PHP_EOL . $view;
        $file = app_path('Models/StaticData/' . $className . '.php');
        $this->comment('writing file: ' . $file);
        file_put_contents($file, $view);
    }

    protected function prepareRows(array $rows, $slugColumn = 'slug'): array
    {
        $rows = collect($rows)
            ->map(function (array $row) use ($slugColumn) {
                $const = $this->slugToConst($row[$slugColumn]);

                return [
                    'const' => [
                        $slugColumn => $const,
                        'id' => $const . '_ID',
                    ],
                    'item' => $row
                ];
            })
            ->toArray();
        return $rows;
    }
}

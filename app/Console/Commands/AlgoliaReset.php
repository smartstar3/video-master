<?php

namespace MotionArray\Console\Commands;

use Illuminate\Console\Command;
use MotionArray\Facades\Algolia;
use Artisan;

class AlgoliaReset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'motionarray:algolia-reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleans and reindex Algolia index';

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
        // Clear Algolia Index
        $index = Algolia::initIndex(config('algolia.index'));

        $index->clearIndex();

        // Reindex
        Artisan::call('motionarray:algolia-push-search-data');
    }
}

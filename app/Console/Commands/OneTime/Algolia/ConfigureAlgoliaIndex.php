<?php

namespace MotionArray\Console\Commands\OneTime\Algolia;

use AlgoliaSearch\Client;
use Illuminate\Console\Command;
use MotionArray\Services\Algolia\AlgoliaConfigBuilder;

class ConfigureAlgoliaIndex extends Command
{
    protected $signature = 'ma:configure-algolia-index                       
         {--config-env= : The environment config to use "prod" or "dev")}
         {--api-key= : Algolia api key}
         {--app-id= : Algolia app id to create the index in}
         {--index= : Name of the existing primary index to configure}';

    protected $description = 'Sets configuration settings of an existing algolia primary index and creates/updates replica indexes with correct configuration settings.';

    public function handle()
    {
        $primaryIndex = $this->option('index');
        $apiKey = $this->option('api-key');
        $appId = $this->option('app-id');

        $env = $this->option('config-env');
        if (!$this->validEnv($env)) {
            $this->error('config-env must be "prod" or "dev"');
            return;
        }

        /** @var AlgoliaConfigBuilder $builder */
        $builder = app(AlgoliaConfigBuilder::class);

        if ($env === 'dev') {
            $config = $builder->devSettings($primaryIndex);
        } else {
            $config = $builder->prodSettings($primaryIndex);
        }

        $kickAssReplicaIndex = $primaryIndex . '_by_kickass';
        $downloadsReplicaIndex = $primaryIndex . '_by_downloads';

        $primaryIndexSettings = $config[$primaryIndex];
        $kickAssSettings = $config[$kickAssReplicaIndex];
        $downloadsSettings = $config[$downloadsReplicaIndex];

        $client = new Client($appId, $apiKey);

        $this->info('configuring primary index: ' . $primaryIndex);
        $client->initIndex($primaryIndex)
            ->setSettings($primaryIndexSettings);

        $this->info('configuring replica index: ' . $downloadsReplicaIndex);
        $client->initIndex($downloadsReplicaIndex)
            ->setSettings($downloadsSettings);

        $this->info('configuring replica index: ' . $kickAssReplicaIndex);
        $client->initIndex($kickAssReplicaIndex)
            ->setSettings($kickAssSettings);
    }

    protected function validEnv($env)
    {
        $validEnvs = ['prod', 'dev'];
        return in_array($env, $validEnvs);
    }
}

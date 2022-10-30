<?php

namespace MotionArray\Console\Commands\OneTime;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use MotionArray\Models\StaticData\Plans;
use MotionArray\Models\User;
use Schema;

class SyncDbSchema extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync-db-schema';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        // make sure to create prod db schema file here
        // uncomment this to populate prod schema for testing
//        $prodDbSchemaImport = base_path('../prod-schema.sql');
//        $sql = file_get_contents($prodDbSchemaImport);
//        DB::unprepared($sql);

        foreach ($this->timestampTables() as $table => $columns) {
            Schema::table($table, function (Blueprint $table) use ($columns) {

                foreach ($columns as $column) {
                    $this->updateTimestampColumn($table->getTable(), $column);
                }
            });
        }

        Schema::table('downloads', function (Blueprint $table) {
            $table->dropIndex('active');
            $table->index('active');

            $table->dropIndex('weight');
            $table->index('weight');

            $table->dropIndex('first_downloaded_at');
            $table->index('first_downloaded_at');
        });

        // cannot do this because portfolio_pages has an enum column
        // change medium text to text manually
        //        Schema::table('portfolio_pages', function (Blueprint $table) {
        //            $table->text('settings')->nullable()->change();
        //            $table->text('unpublished_settings')->nullable()->change();
        //        });

        Schema::table('preview_uploads', function (Blueprint $table) {
            $table->dropIndex('version');

            $table->dropIndex('uploadable_id');
            $table->index('uploadable_id');

            $table->dropIndex('uploadable_type');
            $table->index('uploadable_type');

            $table->dropIndex('version_2');
            $table->index(['version', 'uploadable_id', 'uploadable_type']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('product_status_id');

            $table->dropIndex('free');
            $table->index('free');

            $table->dropIndex('deleted_at');
            $table->index('deleted_at');
        });

        Schema::table('preview_files', function (Blueprint $table) {
            $table->dropIndex('format');
            $table->index('format');
        });

        Schema::table('sub_categories', function (Blueprint $table) {
            $table->dropColumn('weight');
        });

        Schema::table('user_ips', function (Blueprint $table) {
            $table->dropIndex('ip');
            $table->index('ip');
        });

        User::query()->where('company_name', '')->update(['company_name' => null]);


        Schema::table('users', function (Blueprint $table) {

            $table->string('remember_token')->nullable()->change();

            $table->dropColumn('discount_code');
            $table->dropColumn('deactivate_downloads_at');
            $table->dropColumn('is_legacy_user');
            $table->dropColumn('migrate_to_id');
            $table->dropColumn('is_migrated');

            // already has unique index
            $table->dropIndex('email');

            $table->dropIndex('company_name');
            $table->unique('company_name');

            $table->dropIndex('deleted_at');
            $table->index('deleted_at');

            $table->dropIndex('session_id');
            $table->index('session_id');

            $table->dropIndex('firstname');
            $table->index('firstname');

            $table->dropIndex('lastname');
            $table->index('lastname');

            $table->dropIndex('stripe_id');
            $table->index('stripe_id');

            $table->dropIndex('payoneer_id');
            $table->index('payoneer_id');

            $table->dropIndex('payoneer_confirmed');
            $table->index('payoneer_confirmed');

            $table->dropIndex('portfolio_trial_ends_at');
            $table->index('portfolio_trial_ends_at');

            $table->dropIndex('content_removal_warning_status');
            $table->index('content_removal_warning_status');

        });

        Schema::table('tags', function (Blueprint $table) {
            // already has unique index
            $table->dropIndex('name');
        });

        Schema::table('outputs', function (Blueprint $table) {
            $table->string('uploadable_type')->after('preview_upload_id');
        });

//        \DB::unprepared('
//drop table if exists credits_held;
//drop table if exists credits_lost;
//drop table if exists credit_sale;
//drop table if exists sales;
//drop table if exists credits;
//drop table if exists download_period_remaining_payments;
//drop table if exists download_periods;
//drop table if exists users_subscriptions;
//        drop table if exists user_payments;
//');
    }

    protected function tables(): array
    {
        $tables = DB::select('SHOW TABLES');
        $out = [];
        foreach ($tables as $table) {
            $out[] = $table->{'Tables_in_' . DB::getDatabaseName()};
        }
        return $out;
    }

    protected function updateTimestampColumn($table, $column)
    {
        \DB::statement(DB::raw("ALTER TABLE {$table} CHANGE {$column} {$column} TIMESTAMP NULL DEFAULT NULL"));
    }

    protected function timestampTables()
    {
        //        $tables = $this->tables();
        //
        //        $timestampTables = collect($tables)
        //            ->mapWithKeys(function ($table) {
        //                $columns = DB::getSchemaBuilder()->getColumnListing($table);
        //
        //                $timestampColumns = [];
        //
        //                if (in_array('created_at', $columns)) {
        //                    $timestampColumns[] = 'created_at';
        //                }
        //
        //                if (in_array('updated_at', $columns)) {
        //                    $timestampColumns[] = 'updated_at';
        //                }
        //                return [$table => $timestampColumns];
        //            })
        //            ->filter()
        //            ->toArray();
        //
        return [
            'access_service_categories' => [
                'created_at',
                'updated_at',
            ],
            'access_services' => [
                'created_at',
                'updated_at',
            ],
            'auto_descriptions' => [
                'created_at',
                'updated_at',
            ],
            'billing_actions' => [
                'actionable_at',
                'created_at',
                'updated_at',
            ],
            'books' => [
                'created_at',
                'updated_at',
            ],
            'bpms' => [
                'created_at',
                'updated_at',
            ],
            'categories' => [
                'created_at',
                'updated_at',
            ],
            'category_groups' => [
                'created_at',
                'updated_at',
            ],
            'category_types' => [
                'created_at',
                'updated_at',
            ],
            'category_versions' => [
                'created_at',
                'updated_at',
            ],
            'collection_book' => [
                'created_at',
                'updated_at',
            ],
            'collection_product' => [
                'created_at',
                'updated_at',
            ],
            'collections' => [
                'created_at',
                'updated_at',
            ],
            'compressions' => [
                'created_at',
                'updated_at',
            ],
            'confirmation_tokens' => [
                'created_at',
                'updated_at',
            ],
            'custom_galleries' => [
                'created_at',
                'updated_at',
            ],
            'debug_logs' => [
                'created_at',
                'updated_at',
            ],
            'documents' => [
                'created_at',
                'updated_at',
            ],
            'downloads' => [
                'created_at',
                'updated_at',
                'first_downloaded_at',
            ],
            'encoding_statuses' => [
                'created_at',
                'updated_at',
            ],
            'event_codes' => [
                'created_at',
                'updated_at',
            ],
            'ffmpeg_slugs' => [
                'created_at',
                'updated_at',
            ],
            'files' => [
                'created_at',
                'updated_at',
            ],
            'formats' => [
                'created_at',
                'updated_at',
            ],
            'fpss' => [
                'created_at',
                'updated_at',
            ],
            'jobs' => [
                'created_at',
            ],
            'oauth_access_tokens' => [
                'created_at',
                'updated_at',
            ],
            'oauth_clients' => [
                'created_at',
                'updated_at',
            ],
            'oauth_personal_access_clients' => [
                'created_at',
                'updated_at',
            ],
            'outputs' => [
                'created_at',
                'updated_at',
            ],
            'password_reminders' => [
                'created_at',
            ],
            'payout_totals' => [
                'created_at',
                'updated_at',
            ],
            'plans' => [
                'created_at',
                'updated_at',
            ],
            'plugin_categories' => [
                'created_at',
                'updated_at',
            ],
            'plugins' => [
                'created_at',
                'updated_at',
            ],
            'portfolio_pages' => [
                'created_at',
                'updated_at',
            ],
            'portfolio_themes' => [
                'created_at',
                'updated_at',
            ],
            'portfolio_uploads' => [
                'created_at',
                'updated_at',
            ],
            'portfolios' => [
                'created_at',
                'updated_at',
            ],
            'preview_files' => [
                'created_at',
                'updated_at',
            ],
            'preview_uploads' => [
                'created_at',
                'updated_at',
            ],
            'product_change_options' => [
                'created_at',
                'updated_at',
            ],
            'product_changes' => [
                'created_at',
                'updated_at',
            ],
            'product_downloads_count' => [
                'created_at',
                'updated_at',
            ],
            'product_impressions' => [
                'created_at',
                'updated_at',
            ],
            'product_levels' => [
                'created_at',
                'updated_at',
            ],
            'product_plugins' => [
                'created_at',
                'updated_at',
            ],
            'product_search_exclusions' => [
                'created_at',
                'updated_at',
            ],
            'product_statuses' => [
                'created_at',
                'updated_at',
            ],
            'products' => [
                'created_at',
                'updated_at',
            ],
            'project_author_notifications' => [
                'created_at',
                'updated_at',
            ],
            'project_comment_authors' => [
                'created_at',
                'updated_at',
            ],
            'project_comments' => [
                'created_at',
                'updated_at',
            ],
            'project_invitations' => [
                'created_at',
                'updated_at',
            ],
            'project_review_settings' => [
                'created_at',
                'updated_at',
            ],
            'projects' => [
                'created_at',
                'updated_at',
            ],
            'request_notes' => [
                'created_at',
                'updated_at',
            ],
            'request_products' => [
                'created_at',
                'updated_at',
            ],
            'request_statuses' => [
                'created_at',
                'updated_at',
            ],
            'request_upvotes' => [
                'created_at',
                'updated_at',
            ],
            'requests' => [
                'created_at',
                'updated_at',
            ],
            'resolutions' => [
                'created_at',
                'updated_at',
            ],
            'reviews' => [
                'created_at',
                'updated_at',
            ],
            'roles' => [
                'created_at',
                'updated_at',
            ],
            'sample_rates' => [
                'created_at',
                'updated_at',
            ],
            'seller_followers' => [
                'created_at',
                'updated_at',
            ],
            'seller_payouts' => [
                'created_at',
                'updated_at',
            ],
            'seller_reviews' => [
                'created_at',
                'updated_at',
            ],
            'settings' => [
                'created_at',
                'updated_at',
            ],
            'sub_categories' => [
                'created_at',
                'updated_at',
            ],
            'submission_notes' => [
                'created_at',
                'updated_at',
            ],
            'submission_reviewers' => [
                'created_at',
                'updated_at',
            ],
            'submission_statuses' => [
                'created_at',
                'updated_at',
            ],
            'submissions' => [
                'created_at',
                'updated_at',
            ],
            't_product_earnings_by_month' => [
                'created_at',
                'updated_at',
            ],
            'tags' => [
                'created_at',
                'updated_at',
            ],
            'user_access_service' => [
                'created_at',
                'updated_at',
            ],
            'user_downgrades' => [
                'created_at',
                'updated_at',
            ],
            'user_history' => [
                'created_at',
            ],
            'user_ips' => [
                'created_at',
                'updated_at',
            ],
            'user_role' => [
                'created_at',
                'updated_at',
            ],
            'user_sites' => [
                'created_at',
                'updated_at',
            ],
            'user_tokens' => [
                'created_at',
                'updated_at',
            ],
            'users' => [
                'created_at',
                'updated_at',
            ],
            'versions' => [
                'created_at',
                'updated_at',
            ],
            'youtube_access_tokens' => [
                'created_at',
                'updated_at',
            ],
        ];
    }
}

<?php namespace MotionArray\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use MotionArray\Mailers\UserMailer;
use MotionArray\Models\User;
use Carbon\Carbon;
use App;

class UpdateStripeCustomers extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'motionarray:update-stripe-customers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the email addresses and names associated with Stripe records.';

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
        $users = User::where('stripe_id', '!=', 'NULL')->get();

        $i = 0;
        foreach ($users as $user) {
            try {
                if ($user->subscription()) {
                    $stripe_customer = $user->subscription()->getStripeCustomer();

                    $fullName = $user->firstname . ' ' . $user->lastname;

                    if ($stripe_customer->email != $user->email || $stripe_customer->description != $fullName) {
                        $stripe_customer->email = $user->email;
                        $stripe_customer->description = $fullName;
                        $stripe_customer->save();

                        $this->info('Updated Stripe record for: ' . $user->stripe_id);

                        $i++;
                    }
                }
            } catch (Stripe_InvalidRequestError $e) {
                $this->info('Could not update record for user: ' . $user->email . ': ' . $e->getMessage());
            } catch (Stripe_Error $e) {
                $this->info('Could not update record for user: ' . $user->email . ': ' . $e->getMessage());
            } catch (Exception $e) {
                $this->info('Could not update record for user: ' . $user->email . ': ' . $e->getMessage());
            }
        }

        $this->info($i . ' customers\' Stripe records updated.');
    }
}

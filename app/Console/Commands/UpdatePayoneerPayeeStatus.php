<?php namespace MotionArray\Console\Commands;

use Illuminate\Console\Command;
use MotionArray\Models\User;
use MotionArray\Billing\Payoneer;

class UpdatePayoneerPayeeStatus extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'motionarray:update-payoneer-payee-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates payoneer payee status, for missed notifications';

    protected $payoneer;

    public function __construct(Payoneer $payoneer)
    {
        $this->payoneer = $payoneer;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $users = User::whereNotNull('payoneer_id')
            ->where('payoneer_id', '!=', '')
            ->where('payoneer_confirmed', '=', 0)
            ->get();

        $this->info('Updating ' . $users->count() . ' payees statuses');

        foreach ($users as $i => $user) {
            $payee = $this->payoneer->getPayee($user->payoneer_id);

            $this->info('Checking #' . ($i + 1) . '. ' . $user->email);

            if (isset($payee->Payee) && isset($payee->Payee->PayeeStatus) && $payee->Payee->PayeeStatus == 'Active') {
                User::where(['id' => $user->id])->update(['payoneer_confirmed' => true]);

                $this->info('Payoneed Confirmed!!');
            }
        }
    }
}

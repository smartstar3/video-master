<?php

namespace MotionArray\Http\Controllers\Webhooks;

use Illuminate\Http\Request;
use MotionArray\Http\Controllers\Site\BaseController;
use MotionArray\Repositories\UserRepository;
use Response;

class PayoneerController extends BaseController
{

    /**
     * User Repo
     */
    private $userRepo;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepo = $userRepository;
    }

    /**
     * Handle IPCN notifications from Payoneer
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleWebhook(Request $request)
    {
        $payoneer_id = $request->get('payeeid') ?: $request->get('apuid');

        // Find the user.
        $user = $this->userRepo->findByPayoneerId($payoneer_id);

        // Approved Payee Applications
        if ($user && $request->get('APPROVED')) {
            $data = [
                'payout_method' => 'payoneer',
                'payoneer_id' => $payoneer_id,
                'payoneer_confirmed' => true,
            ];

            // Update the user.
            $this->userRepo->update($user->id, $data);
        }

        // Approved Payee Applications
        if ($user && $request->get('DECLINE')) {
            // Remove Payoneer
            $data = [
                'payoneer_id' => null,
                'payoneer_confirmed' => false,
            ];

            if ($user->payout_method === 'payoneer') {
                $data['payout_method'] = null;
            }

            // Update the user.
            $this->userRepo->update($user->id, $data);
        }

        if ($user) {
            // Send a successful response to Payoneer.
            return Response::json("IPCN Handled", 200);
        }

        return Response::json("User not found", 404);
    }
}

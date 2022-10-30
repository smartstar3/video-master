<?php

namespace MotionArray\Mailers;

use Illuminate\Support\Facades\URL;
use MotionArray\Models\Product;
use MotionArray\Models\Request;
use MotionArray\Models\RequestNote;
use MotionArray\Models\User;
use MotionArray\Repositories\UserRepository;

class RequestMailer extends Mailer
{
    protected $user;

    public function __construct(UserRepository $user)
    {
        $this->from = [
            'email' => "content@motionarray.com", 'name' => "Motion Array"
        ];

        $this->user = $user;
    }

    public function requestReceived(Request $request)
    {
        /*
         * Email variables
         */
        $view = 'admin.emails.request.received';
        $data = [
            'request' => $request
        ];
        $subject = 'Your request has been received!';

        /*
         * Send mail
         */

        return $this->sendTo($request->user->email, $subject, $view, $data);
    }

    public function requestApproved(Request $request, RequestNote $note)
    {
        /*
         * Email variables
         */
        $view = 'admin.emails.request.approved';

        $url = URL::to('requests?status=my-requests');

        $data = [
            'request' => $request,
            'feedback' => $note->body,
            'url' => $url,
        ];

        $subject = 'Your request was approved!';

        /*
         * Send mail
         */

        return $this->sendTo($request->user->email, $subject, $view, $data);
    }

    public function requestRejected(Request $request, $feedback)
    {
        /*
         * Email variables
         */

        $view = 'admin.emails.request.rejected';
        $data = [
            'request' => $request,
            'feedback' => $feedback
        ];
        $subject = 'Your request was rejected';

        /*
         * Send mail
         */

        return $this->sendTo($request->user->email, $subject, $view, $data);
    }

    public function requestProduct(Request $request, Product $product)
    {

        $productUrl = URL::to('/' . $product->category->slug . '/' . $product->slug);

        $data = [
            'request' => $request,
            'url' => $productUrl
        ];

        // Upvotes emails
        $subject = 'A request you up-voted has been fulfilled';

        if ($request->upvotes->count()) {
            foreach ($request->upvotes as $upvote) {
                if ($upvote->user->id != $product->seller->id) {
                    $view = 'admin.emails.request.product-upvoted';

                    $this->sendTo($upvote->user->email, $subject, $view, $data);
                }
            }
        }

        // Owner Email
        $subject = 'Your request has been fulfilled';

        $view = 'admin.emails.request.product';

        return $this->sendTo($request->user->email, $subject, $view, $data);
    }

    public function newApprovedRequest(Request $request)
    {
        $users = $this->user->getSellers();

        $url = URL::to('requests');

        $view = 'admin.emails.request.producers-notification';

        $data = [
            'request' => $request,
            'url' => $url
        ];

        $subject = 'A new request has been added';

        foreach ($users as $user) {
            if ($user->id != $request->user->id) {
                $data['user'] = $user;

                $this->sendTo($user->email, $subject, $view, $data);
            }
        }

        return $users;
    }
}

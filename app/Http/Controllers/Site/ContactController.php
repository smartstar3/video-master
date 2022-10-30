<?php

namespace MotionArray\Http\Controllers\Site;

use Illuminate\Http\Request;
use MotionArray\Facades\Recaptcha;
use MotionArray\Mailers\FeedbackMailer;
use MotionArray\Repositories\PageRepository;
use MotionArray\Services\Intercom\IntercomService;

class ContactController extends BaseController
{
    public function index(PageRepository $page)
    {
        $entry = $page->getPageByURI('contact');

        if (!$entry) {
            return app()->abort(404);
        }

        return view('site.pages.contact', compact('entry'));
    }

    public function store(Request $request, IntercomService $intercom, FeedbackMailer $feedbackMailer)
    {
        $recaptchaResponse = Recaptcha::verify($request->input('g-recaptcha-response'), $request->ip());

        if (!$recaptchaResponse->isSuccess()) {
            return response()->json([
                'error' => 'Error with recaptcha, please contact us if this persists.'
            ]);
        }

        $inputs = $request->all();

        $validator = \Validator::make($inputs, [
            'email' => 'email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid email address, please use a valid email address.'
            ]);
        }

        $from = array_only($inputs, ['email', 'name']);
        $email = $request->email;
        $subject = trim($request->subject);
        $message = trim($request->message);

        // maintaining existing logic although it is strange
        $intercomEnabled = app(IntercomService::class)->enabled();
        $response = false;
        if ($email && $intercomEnabled) {
            $response = $intercom->sendContactMessage($subject, $message, $email);
        }

        if (!$response) {
            $response = $feedbackMailer->contactMessage($inputs);
        }

        return response()->json($response);
    }
}

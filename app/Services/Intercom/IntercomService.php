<?php namespace MotionArray\Services\Intercom;

use Intercom\IntercomClient;
use MotionArray\Models\User;
use MotionArray\Repositories\UserRepository;

class IntercomService
{
    /**
     * @var IntercomClient
     */
    protected $intercomClient;

    /**
     * @var UserRepository
     */
    protected $user;

    protected $appId;

    protected $sessionName = 'intercom.lastUpdate';

    protected $enabled;

    public function __construct(UserRepository $user)
    {
        $token = config('services.intercom.token');

        $this->enabled = config('services.intercom.enabled');
        $this->appId = config('services.intercom.appId');
        $this->intercomClient = new IntercomClient($token, null);
        $this->user = $user;
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }

    public function get($endpoint, $query)
    {
        return $this->intercomClient->get($endpoint, $query);
    }

    public function getUsers($page = 1, $per_page = 50, $more_options = [])
    {
        $options = [
            'page' => $page,
            'per_page' => $per_page
        ];

        $options = array_merge($options, $more_options);

        return $this->intercomClient->users->getUsers($options);
    }

    /**
     * Used to list a big number of users,
     * It keeps scrolling on a user search using a search id provided by intercom
     * (scroll_param)
     *
     * @param array $options
     * @return mixed
     */
    public function scrollUsers(Array $options = [])
    {
        return $this->scroll('users', $options);
    }

    private function scroll($endpoint, Array $options = [])
    {
        $endpoint = str_replace('/', '', $endpoint);

        $endpoint .= '/scroll';

        $response = $this->get($endpoint, $options);

        return $response;
    }

    public function createEvent($event)
    {
        return $this->intercomClient->events->create($event);
    }

    public function createUser(Array $user)
    {
        return $this->intercomClient->users->create($user);
    }

    public function getUser($email)
    {
        $intercomUser = null;

        try {
            $user = $this->user->findByEmail($email);

            if ($user && $user->intercom_id) {
                $intercom_id = $user->intercom_id;

                $response = $this->intercomClient->get('users/' . $intercom_id, []);
            } else {
                $response = $this->intercomClient->users->getUsers(['email' => $email]);
            }

            if (isset($response->email)) {
                $intercomUser = $response;
            } elseif (isset($response->users) and isset($response->users[0])) {
                $intercomUser = $response->users[0];
            }
        } catch (\Exception $e) {
        }

        return $intercomUser;
    }

    /**
     * Sends contact message to intercom
     *
     * @param $subject
     * @param $body
     * @param array $from
     * @param array $to
     * @return mixed
     */
    protected function sendInAppMessage($subject, $body, Array $from, Array $to = [])
    {
        if ($subject) {
            $body = 'Subject: ' . $subject . "\n\n" . $body;
        }

        $messageParams = [
            "body" => $body,
            "from" => $from
        ];

        if (count($to)) {
            $messageParams["to"] = $to;
        }

        return $this->intercomClient->messages->create($messageParams);
    }

    /**
     * Sends contact message to intercom if the user is registered (on Intercom)
     *
     * @param $subject
     * @param $body
     * @param array $from
     * @return bool|mixed|void
     */
    public function sendContactMessage($subject, $body, $email)
    {
        $fromObject = $this->getUser($email);

        if (!$fromObject) {
            return false;
        }

        return $this->sendInAppMessage($subject, $body, [
            'type' => $fromObject->type,
            'id' => $fromObject->id,
        ]);
    }

    public function deleteUser($userId)
    {
        $filter = ['id' => $userId];

        return $this->intercomClient->delete('users', $filter);
    }

    public function getJsSettings(User $user = null)
    {
        if (!$user) {
            return [];
        }

        $userData = $this->user->getIntercomData($user);

        unset($userData['custom_attributes']);

        $userData['user_hash'] = hash_hmac('sha256', $userData['user_id'], config('services.intercom.key'));

        return array_merge(['app_id' => $this->appId], $userData);
    }
}

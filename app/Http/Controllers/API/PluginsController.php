<?php namespace MotionArray\Http\Controllers\API;

use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use MotionArray\Models\User;
use MotionArray\Repositories\PluginRepository;
use MotionArray\Repositories\PluginTokenRepository;
use MotionArray\Repositories\UserRepository;
use Request;
use Response;

class PluginsController extends BaseController
{
    protected $user;

    protected $plugin;

    protected $pluginToken;

    public function __construct(UserRepository $userRepository, PluginRepository $pluginRepository, PluginTokenRepository $pluginTokenRepository)
    {
        $this->user = $userRepository;

        $this->plugin = $pluginRepository;

        $this->pluginToken = $pluginTokenRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Response::json([
            'success' => true,
            'items' => $this->plugin->all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $data = Request::all();

        $plugin = $this->plugin->update($id, $data);

        if ($plugin) {
            return Response::json([
                'success' => true,
                'item' => $plugin
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function auth()
    {
        $email = Request::get('email');

        $password = Request::get('password');

        $pluginsVersion = Request::get('plugins_version', '1.0');

        $user = User::whereEmail($email)->first();

        // Check auth
        if ($user && Hash::check($password, $user->password)) {
            if ($this->pluginToken->canAccessPlugins($user)) {
                // Generate token
                $token = $this->pluginToken->generateToken($user, $pluginsVersion);

                return Response::json(['success' => true, 'token' => $token]);
            }
        }

        return Response::json(['success' => false]);
    }

    public function check()
    {
        $token = Request::get('token');

        $ttl = Carbon::now()->addDay();

        $valid = $this->pluginToken->validateTokenAuth($token);

        if ($valid) {
            return Response::json(['success' => $valid, 'sessionExpires' => $ttl->format('D M d Y H:i:s O')]);
        } else {
            return Response::json(['success' => false]);
        }
    }
}

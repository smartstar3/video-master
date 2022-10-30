<?php namespace MotionArray\Repositories;

use Illuminate\Support\Facades\Cache;
use MotionArray\Models\User;
use Illuminate\Support\Facades\Hash;
use MotionArray\Models\UserToken;

class PluginTokenRepository
{
    public function generateToken(User $user, $pluginsVersion)
    {
        $token = Hash::make($user->id);

        $user->tokens()->delete();

        $user->tokens()->create([
            'token' => $token,
            'type' => 'plugin',
            'plugins_version' => $pluginsVersion,
        ]);

        if ($pluginsVersion == '1.0') {
            $this->cacheOldTokens();
        }

        return $token;
    }

    public function canAccessPlugins(User $user)
    {
        if ($user->isSubscriptionActive() && !$user->plan->isFree()) {
            return true;
        }

        // Submissions approved except music
        $submissionsApproved = $user->submissions()
            ->where('submission_status_id', '=', 3)
            ->whereHas('product', function ($q) {
                $q->whereNotIn('category_id', ['4, 7']);
            })
            ->count();

        return $user->isAdmin() || $submissionsApproved;
    }

    public function getCachedToken($token)
    {
        $tokens = Cache::get('old_plugin_tokens');

        if (!$tokens) {
            $this->cacheOldTokens();
        }

        if (is_array($tokens)) {
            $token = array_first($tokens, function ($record) use ($token) {
                return $record['token'] == $token;
            });

            return $token;
        }
    }

    /**
     * Improve response speed for plugins v1.0
     * which make a request every 3 seconds
     */
    public function cacheOldTokens()
    {
        $tokens = UserToken::select('user_id', 'token', 'plugins_version')->where('plugins_version', '=', '1.0')->get();

        $tokens = $tokens->toArray();

        // Include canAccessPlugins
        $tokens = array_map(function ($token) {
            $user = (new UserToken($token))->user;

            $token['canAccessPlugins'] = $user && $this->canAccessPlugins($user);

            return $token;
        }, $tokens);

        Cache::put('old_plugin_tokens', $tokens, 1440);

        return $tokens;
    }

    public function validateTokenAuth($tokenCode)
    {
        $token = $this->getCachedToken($tokenCode);

        if ($token && isset($token['canAccessPlugins'])) {
            return $token['canAccessPlugins'];
        } else {
            $token = UserToken::where('token', '=', $tokenCode)->first();
        }

        $validUser = false;

        if ($token) {
            $user = $token->user;

            if ($user) {
                $validUser = $this->canAccessPlugins($user);
            }
        }

        // Check subscription is valid
        return $validUser;
    }

    public function getPluginUsers()
    {
        return User::where('plan_id', '!=', 5)->whereHas('tokens')->get();
    }

    public function getUsageCount()
    {
        return UserToken::whereHas('user', function ($q) {
            $q->where('plan_id', '!=', 5);
        })->get();
    }
}

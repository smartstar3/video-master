<?php

namespace MotionArray\Events\UserEvent;

use MotionArray\Models\User;

class UserEventMessageHelper
{
    public function arrayToString(array $array)
    {
        $parts = [];
        foreach ($array as $key => $val) {
            $parts[] = "{$key}: '{$val}'";
        }
        return '(' . implode(', ', $parts) . ')';
    }

    public function userDescription(User $user)
    {
        $userDetails = $this->arrayToString([
            'email' => $user->email,
            'id' => $user->id,
        ]);

        $userName = $user->full_name;

        return "'{$userName}' {$userDetails}";
    }

    public function userArrayDescription(array $user)
    {
        $userDetails = $this->arrayToString([
            'email' => $user['email'],
            'id' => $user['id'],
        ]);
        $userName = $user['firstname'] . ' ' . $user['lastname'];

        return "'{$userName}' {$userDetails}";
    }

    /**
     * generate description for the user triggering the user event
     */
    public function triggeredByDescription($userId, array $triggeredBy)
    {
        $triggeredByDescription = 'Unknown';
        if ($triggeredBy) {
            $isSelf = $userId == $triggeredBy['id'];
            if ($isSelf) {
                $triggeredByDescription = 'Self';
            } else {
                $triggeredByDescription = $this->userArrayDescription($triggeredBy);
            }
        }
        return $triggeredByDescription;
    }
}

<?php

namespace MotionArray\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource class to get user info, including potentially sensitive info.
 * To get the user info without sensitive data included use User resource.
 */
class UserPrivate extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $userRepo = app('MotionArray\Repositories\UserRepository');
        return [
            'id' => $this->id,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'plan' => [
                'id' => $this->plan->id,
                'name' => $this->plan->name
            ],
            'total_disk_space' => [
                'value' => $this->plan->diskSpaceInKb() * 1024,
                'unit' => 'B'
            ],
            'disk_usage' => [
                'value' => $userRepo->getDiskUsage($this->resource),
                'unit' => 'B'
            ]
        ];
    }
}

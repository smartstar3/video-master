<?php

namespace MotionArray\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserManagerResource extends JsonResource
{
    public static $wrap = null;
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'company_name' => $this->company_name,
            'payout_method' => $this->payout_method,
            'paypal_email' => $this->paypal_email,
            'stripe_id' => $this->stripe_id,
            'email' => $this->email,
            'role' => $this->whenLoaded('roles', function(){ 
                return $this->roles->first()->name; 
            }),
            'plan' => $this->whenLoaded('plan') ? $this->plan : null,
        ];
    }
}

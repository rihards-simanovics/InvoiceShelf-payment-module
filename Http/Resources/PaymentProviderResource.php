<?php

namespace Modules\Payments\Http\Resources;

use Crater\Http\Resources\CompanyResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentProviderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'company_id' => $this->company_id,
            'type' => $this->type,
            'driver' => $this->driver,
            'active' => $this->active,
            'use_test_env' => $this->use_test_env,
            'settings' => $this->settings,
            'company' => $this->when($this->company()->exists(), function () {
                return new CompanyResource($this->company);
            }),
        ];
    }
}

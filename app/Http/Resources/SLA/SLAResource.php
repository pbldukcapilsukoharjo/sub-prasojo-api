<?php

declare(strict_types=1);

namespace App\Http\Resources\SLA;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SLAResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(
        Request $request
    ): array {

        return [
            'list' => SLADetailResource::collection(collect($this['list'])),
            'meta' => $this['meta'],
        ];
    }
}
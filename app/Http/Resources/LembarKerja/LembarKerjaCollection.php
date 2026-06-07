<?php

declare(strict_types=1);

namespace App\Http\Resources\LembarKerja;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

final class LembarKerjaCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return LembarKerjaListResource::collection(
            $this->collection
        )->toArray($request);
    }
}
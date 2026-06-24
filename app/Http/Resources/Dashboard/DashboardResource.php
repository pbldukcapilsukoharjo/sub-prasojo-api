<?php

declare(strict_types=1);

namespace App\Http\Resources\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class DashboardResource extends JsonResource
{
    public function toArray(
        Request $request
    ): array {

        return [
            'success' => true,
            'code' => 200,
            'message' => 'Berhasil mendapatkan data terbaru',
            'data' => $this->resource,
        ];
    }
}
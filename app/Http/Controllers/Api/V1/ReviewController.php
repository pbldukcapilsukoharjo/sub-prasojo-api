<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Review\IndexReviewRequest;
use App\Http\Requests\Review\ShowReviewRequest;
use App\Http\Requests\Review\StoreReviewRequest;
use App\Http\Requests\Review\UpdateReviewRequest;
use App\Http\Resources\Review\ReviewCollection;
use App\Http\Resources\Review\ReviewResource;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;

final class ReviewController extends Controller
{
    public function __construct(
        private ReviewService $service
    ) {}

    public function index(
        IndexReviewRequest $request
    ): JsonResponse {
        $result = $this->service->index(
            $request->validated()
        );

        // Cek apakah service mengembalikan array dengan 'rekap' atau langsung collection
        if (isset($result['rekap']) && isset($result['list'])) {
            // Format dari code kedua (dengan rekap)
            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengambil data ulasan',
                'data' => [
                    'rekap' => $result['rekap'],
                    'list' => ReviewResource::collection($result['list']),
                ],
            ]);
        } else {
            // Format dari code pertama (tanpa rekap)
            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengambil data ulasan',
                'data' => new ReviewCollection($result),
            ]);
        }
    }

    public function show(
        ShowReviewRequest $request,
        int $review_id
    ): JsonResponse {
        // Gunakan method show dari service (code kedua) jika ada
        if (method_exists($this->service, 'show')) {
            $data = $this->service->show($review_id);
        } else {
            // Fallback ke getDetail (code pertama)
            $data = $this->service->getDetail($review_id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil detail ulasan',
            'data' => new ReviewResource($data),
        ]);
    }

    public function store(
        StoreReviewRequest $request
    ): JsonResponse {
        // Gunakan method store dari service (code kedua) jika ada
        if (method_exists($this->service, 'store')) {
            $data = $this->service->store($request->validated());
        } else {
            // Fallback ke create (code pertama)
            $data = $this->service->create($request->validated());
        }

        return response()->json([
            'success' => true,
            'message' => 'Ulasan berhasil disimpan',
            'data' => new ReviewResource($data),
        ], 201);
    }

    public function update(
        UpdateReviewRequest $request,
        int $review_id
    ): JsonResponse {
        // Gunakan method update dari service (kedua code sama)
        $data = $this->service->update(
            $review_id,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Ulasan berhasil diperbarui',
            'data' => new ReviewResource($data),
        ]);
    }

    public function destroy(
        int $review_id
    ): JsonResponse {
        $this->service->delete($review_id);

        return response()->json([
            'success' => true,
            'message' => 'Ulasan berhasil dihapus',
        ]);
    }
}
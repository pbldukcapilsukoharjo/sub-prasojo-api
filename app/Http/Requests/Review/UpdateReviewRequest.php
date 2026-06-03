<?php

declare(strict_types=1);

namespace App\Http\Requests\Review;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'review_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review_content' => ['nullable', 'string'],
        ];
    }
}
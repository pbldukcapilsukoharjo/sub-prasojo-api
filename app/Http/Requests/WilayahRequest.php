<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class WilayahRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'search'        => 'nullable|string|max:100',
            'q'             => 'nullable|string|max:100',
            'id_kecamatan'  => 'nullable|string',
            'id_layanan'    => 'nullable|string',
            'layanan_kode'  => 'nullable|string',
            'periode_bulan' => 'nullable|integer|min:1|max:12',
            'start_date'    => 'nullable|date_format:d-m-Y',
            'end_date'      => 'nullable|date_format:d-m-Y|after_or_equal:start_date',
            'per_page'      => 'nullable|integer|min:1',
            'page'          => 'nullable|integer|min:1',
            'sort_by'       => 'nullable|string',
            'sort_dir'      => 'nullable|in:asc,desc,ASC,DESC',
            'reporter'      => 'nullable|string',
            'pelapor'       => 'nullable|string',
            'id_pelapor'    => 'nullable|string',
        ];
    }

    /**
     * Prepare data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('q') && !$this->has('search')) {
            $this->merge([
                'search' => $this->input('q'),
            ]);
        }
    }
}

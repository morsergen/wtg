<?php

namespace App\Http\Requests;

use App\Data\Properties\PropertySearchData;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class IndexPropertyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'city' => ['sometimes', 'string', 'max:100'],
            'check_in' => ['required', 'date_format:Y-m-d'],
            'check_out' => ['required', 'date_format:Y-m-d', 'after:check_in'],
            'guests' => ['required', 'integer', 'min:1', 'max:65535'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    public function toData(): PropertySearchData
    {
        /** @var array{
         *     check_in: string,
         *     check_out: string,
         *     guests: int,
         *     city?: string,
         *     page?: int
         * } $data
         */
        $data = $this->validated();

        return PropertySearchData::fromArray($data);
    }
}

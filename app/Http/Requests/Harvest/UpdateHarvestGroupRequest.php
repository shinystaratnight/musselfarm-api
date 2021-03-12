<?php

namespace App\Http\Requests\Harvest;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class UpdateHarvestGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // "name" => 'required|numeric|exists:seasons,id',
            'planned_date' => 'nullable|numeric',
            'planned_date_harvest' => 'nullable|numeric',
            'seed_id' => 'nullable|numeric|exists:farm_utils,id',
            "density" => 'required|integer',
            "drop" => 'required|integer',
            "floats" => 'required|integer',
            "spacing" => 'required|integer',
            "submersion" => 'required|integer',
            // 'harvest_start_date' => 'nullable|numeric'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();

        throw new HttpResponseException(
            response()->json(['status' => 'Error',
                'message' => array_shift($errors)], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}

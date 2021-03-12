<?php

namespace App\Http\Requests\Harvest;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CreateHarvestGroupRequest extends FormRequest
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
            "line_id" => 'required|numeric|exists:lines,id',
            "name" => 'required|numeric|exists:seasons,id',
            "planned_date" => 'required|numeric',
            "planned_date_harvest" => 'required|numeric',
            "seed_id" => 'required|numeric|exists:farm_utils,id',
            "density" => 'required|integer',
            "drop" => 'required|integer',
            "floats" => 'required|integer',
            "spacing" => 'required|integer',
            "submersion" => 'required|integer',
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

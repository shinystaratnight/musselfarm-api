<?php

namespace App\Http\Requests\Farm;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class FarmRequest extends FormRequest
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
            'user_id' => 'required|numeric|exists:users,id',
            'name' => 'required|string',
            'long' => ["required", "numeric", "regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/"],
            'lat' => ["required", "numeric", "regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/"],
            'area' => 'required|numeric|min:1',
            'farm_number' => 'required|string|max:16',
            'owner' => 'array|nullable'
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

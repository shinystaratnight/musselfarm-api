<?php

namespace App\Http\Requests\Assessment;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CreateAssessmentRequest extends FormRequest
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
            'harvest_group_id' => 'required|numeric|exists:harvest_groups,id',
            'color' => 'nullable',
            'condition_min' => 'nullable|numeric',
            'condition_max' => 'nullable|numeric',
            'condition_average' => 'nullable|numeric',
            'blues' => 'nullable|numeric',
            'tones' => 'nullable|numeric|between:0.000,999999.999',
            'planned_date_harvest' => 'nullable|numeric',
            'comment' => 'nullable|max:1000',
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

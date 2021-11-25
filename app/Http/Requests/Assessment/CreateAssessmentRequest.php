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
            'account_id' => 'required|exists:accounts,id',
            'harvest_group_id' => 'required|numeric|exists:harvest_groups,id',
            'color' => 'nullable',
            'condition_min' => 'nullable|numeric',
            'condition_max' => 'nullable|numeric',
            'condition_avg' => 'nullable|numeric',
            'blues' => 'nullable|numeric',
            'condition_score' => 'numeric',
            'tones' => 'nullable|numeric|between:0.000,999999.999',
            'date_assessment' => 'required|nullable|numeric',
            'planned_date_harvest' => 'nullable|numeric',
            'comment' => 'nullable|max:1000',
            'images' => 'array',
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

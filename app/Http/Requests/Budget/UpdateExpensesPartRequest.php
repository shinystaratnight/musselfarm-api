<?php

namespace App\Http\Requests\Budget;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class UpdateExpensesPartRequest extends FormRequest
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
            'farm_id' => 'required|numeric|exists:farms,id',
            'line_id' => 'required|numeric|exists:lines,id',
            'budget_id' => 'nullable|numeric|exists:line_budgets,id',
            'expenses_id' => 'required|numeric|exists:expenses,id',
            'data_row' => 'required|string|in:price_actual,price_budget',
            'value' => 'required|numeric|min:0|max:9999999.99',
            'comment' => 'nullable|string|max:255'
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

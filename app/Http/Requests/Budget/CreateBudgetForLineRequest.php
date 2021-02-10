<?php

namespace App\Http\Requests\Budget;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CreateBudgetForLineRequest extends FormRequest
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
            "planned_harvest_tones" => 'nullable|numeric|between:0,999999999.99',
            "budgeted_harvest_income" => 'nullable|numeric|between:0,999999999.99',
            "length_budget" => 'nullable|numeric|between:0,999999999',
            "length_actual" => 'nullable|numeric|between:0,999999999',
            "planned_harvest_tones_actual" => 'nullable|numeric|between:0,999999999.99',
            "budgeted_harvest_income_actual" => 'nullable|numeric|between:0,999999999.99',
            'expenses.*.line_budget_id' => 'nullable|numeric|exists:line_budgets,id',
            'expenses.*.type' => 'required|in:s,m',
            'expenses.*.expenses_name' => 'required|string|max:100',
            'expenses.*.price_budget' => 'required|numeric|between:0.00,999999999.99',
            'expenses.*.price_actual' => 'required|numeric|between:0.00,999999999.99'
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

<?php

namespace App\Http\Requests\Budget;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ExpensesRequest extends FormRequest
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
            'expenses.*.line_budget_id' => 'required|numeric|exists:line_budgets,id',
            'expenses.*.type' => 'required|in:s,m',
            'expenses.*.expenses_name' => 'required|string|max:100',
            'expenses.*.price_budget' => 'required|numeric',
            'expenses.*.price_actual' => 'required|numeric'
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

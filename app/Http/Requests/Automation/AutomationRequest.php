<?php

namespace App\Http\Requests\Automation;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class AutomationRequest extends FormRequest
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
            'account_id' => 'required|exists:account,id',
            'condition' => 'required|string|in:Seeding,Harvesting,Assessment',
            'action' => 'required|string|in:Created,Completed,Upcoming',
            'time' => 'required|numeric|in:-7,-6,-5,-4,-3,-2,-1,0,1,2,3,4,5,6,7',
            'title' => 'required|string',
            'unit' => 'required|string|in:hour,day,week,month',
            'charger_id' => 'numeric',
            'description' => 'required|string',
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

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContractDeployRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'deployer_address' => 'required|string|max:255',
            'constructor_params' => 'nullable|array',
            'constructor_params.*' => 'nullable|string',
            'constructor_params_json' => 'nullable|string',
        ];
    }
}

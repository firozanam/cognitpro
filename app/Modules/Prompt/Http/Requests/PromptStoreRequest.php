<?php

namespace App\Modules\Prompt\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PromptStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'content' => ['required', 'string', 'max:50000'],
            'category_id' => ['required', 'exists:categories,id'],
            'ai_model' => ['required', 'string', 'max:100'],
            'pricing_model' => ['required', 'in:fixed,pay_what_you_want,free'],
            'price' => ['required_if:pricing_model,fixed', 'numeric', 'min:0', 'max:9999.99'],
            'min_price' => ['required_if:pricing_model,pay_what_you_want', 'numeric', 'min:0', 'max:9999.99'],
            'tags' => ['nullable', 'array', 'max:10'],
            'tags.*' => ['string', 'max:100'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The prompt title is required.',
            'description.required' => 'Please provide a description for your prompt.',
            'content.required' => 'The prompt content is required.',
            'category_id.required' => 'Please select a category.',
            'category_id.exists' => 'The selected category is invalid.',
            'ai_model.required' => 'Please specify the AI model this prompt is designed for.',
            'pricing_model.required' => 'Please select a pricing model.',
            'price.required_if' => 'Please set a price for your prompt.',
            'min_price.required_if' => 'Please set a minimum price for pay-what-you-want pricing.',
        ];
    }
}

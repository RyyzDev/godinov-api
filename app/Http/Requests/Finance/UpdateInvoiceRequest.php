<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
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
            'project_id' => 'sometimes|exists:projects,id',
            'client_email' => 'sometimes|email|max:255',
            'client_phone' => 'nullable|string|max:20',
            'invoice_date' => 'sometimes|date',
            'due_date' => 'sometimes|date|after_or_equal:invoice_date',
            'invoice_items' => 'sometimes|array|min:1',
            'invoice_items.*.description' => 'required_with:invoice_items|string|max:255',
            'invoice_items.*.rate' => 'required_with:invoice_items|numeric|min:0',
            'invoice_items.*.unit' => 'required_with:invoice_items|integer|min:1',
            'invoice_items.*.discount' => 'nullable|numeric|min:0',
            'payment_status' => 'sometimes|in:Unpaid,Partially,Paid,Overdue',
        ];
    }
}

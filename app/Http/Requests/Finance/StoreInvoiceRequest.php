<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
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
            'project_id' => 'required|exists:projects,id',
            'client_email' => 'required|email|max:255',
            'client_phone' => 'nullable|string|max:20',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'invoice_items' => 'required|array|min:1',
            'invoice_items.*.description' => 'required|string|max:255',
            'invoice_items.*.rate' => 'required|numeric|min:0',
            'invoice_items.*.unit' => 'required|integer|min:1',
            'invoice_items.*.discount' => 'nullable|numeric|min:0',
            'payment_status' => 'nullable|in:Unpaid,Partially,Paid,Overdue',
        ];
    }
}

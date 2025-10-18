<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $filterBy = $this->input('filterBy');

        return [
            'filter' => ['required', Rule::in(['item', 'in', 'out', 'damaged'])],
            'filterBy' => ['required', Rule::in(['date', 'month', 'year'])],
            'itemType' => ['required', Rule::in(['all', 'barang_mentah', 'barang_jadi'])],
            'dateFrom' => ['required_if:filterBy,date', 'nullable', 'date'],
            'dateUntil' => ['required_if:filterBy,date', 'nullable', 'date', 'after_or_equal:dateFrom'],
            'monthFrom' => ['required_if:filterBy,month', 'nullable', 'integer', 'min:1', 'max:12'],
            'monthUntil' => ['required_if:filterBy,month', 'nullable', 'integer', 'min:1', 'max:12', Rule::when($filterBy == 'month' && $this->input('monthFrom'), 'gte:monthFrom')],
            'selectYear' => ['required_if:filterBy,month,year', 'nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'filter.required' => 'Tipe data laporan wajib dipilih.',
            'filterBy.required' => 'Periode laporan wajib dipilih.',
            'dateFrom.required_if' => 'Tanggal mulai wajib diisi jika filter per tanggal.',
            'dateUntil.required_if' => 'Tanggal hingga wajib diisi jika filter per tanggal.',
            'dateUntil.after_or_equal' => 'Tanggal hingga harus setelah atau sama dengan tanggal mulai.',
            'monthFrom.required_if' => 'Bulan mulai wajib diisi jika filter per bulan.',
            'monthUntil.required_if' => 'Bulan hingga wajib diisi jika filter per bulan.',
            'monthUntil.gte' => 'Bulan hingga harus setelah atau sama dengan bulan mulai.',
            'selectYear.required_if' => 'Tahun wajib diisi jika filter per bulan atau tahun.',
        ];
    }
}
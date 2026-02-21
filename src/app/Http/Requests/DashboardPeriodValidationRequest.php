<?php

namespace App\Http\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class DashboardPeriodValidationRequest extends FormRequest
{
    private const MAX_RANGE_DAYS = 183;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['nullable', 'date_format:Y-m-d'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $today = CarbonImmutable::today('Asia/Tokyo')->toDateString();
        $date = $this->input('date');
        $from = $this->input('from');
        $to = $this->input('to');

        if ((!$from || !$to) && $date) {
            $from = $from ?: $date;
            $to = $to ?: $date;
        }

        if (!$from && !$to) {
            $from = $today;
            $to = $today;
        } elseif (!$from) {
            $from = $to;
        } elseif (!$to) {
            $to = $from;
        }

        $this->merge([
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function fromDate(): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat('Y-m-d', (string) $this->validated('from'), 'Asia/Tokyo');
    }

    public function toDate(): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat('Y-m-d', (string) $this->validated('to'), 'Asia/Tokyo');
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $from = $this->input('from');
            $to = $this->input('to');
            if (!is_string($from) || !is_string($to)) {
                return;
            }

            try {
                $fromDate = CarbonImmutable::createFromFormat('Y-m-d', $from, 'Asia/Tokyo');
                $toDate = CarbonImmutable::createFromFormat('Y-m-d', $to, 'Asia/Tokyo');
            } catch (\Throwable) {
                return;
            }

            if ($fromDate->gt($toDate)) {
                $validator->errors()->add('to', '終了日は開始日以降を指定してください。');

                return;
            }

            if ($fromDate->diffInDays($toDate) > (self::MAX_RANGE_DAYS - 1)) {
                $validator->errors()->add('to', sprintf('指定できる期間は最大%d日（約半年）です。', self::MAX_RANGE_DAYS));
            }
        });
    }
}

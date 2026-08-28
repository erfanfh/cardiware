<?php

namespace App\Http\Requests\Admin\Raffle;

use Illuminate\Foundation\Http\FormRequest;

class InvalidateDrawTicketRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['reason' => ['required','string','min:3','max:255']]; }
}

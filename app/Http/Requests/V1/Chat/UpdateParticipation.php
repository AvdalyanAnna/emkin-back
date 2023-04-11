<?php

namespace App\Http\Requests\V1\Chat;

use Illuminate\Foundation\Http\FormRequest;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class UpdateParticipation
 * @package App\Http\Requests\V1\Chat
 */
class UpdateParticipation extends FormRequest
{

    /**
     * @return bool
     */
    public function authorized(): bool {
        return true;
    }

    /**
     * @return string[]
     */
    #[ArrayShape(['settings' => "string"])] public function rules(): array {
        return [
            'settings' => 'array',
        ];
    }
}

<?php

namespace App\Http\Requests\V1\Chat;

use JetBrains\PhpStorm\ArrayShape;

/**
 * Class ClearConversation
 * @package App\Chat\Http\Requests
 */
class ClearConversation extends BaseRequest
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
    #[ArrayShape(['participant_id' => "string", 'participant_type' => "string"])] public function rules(): array {
        return [
            'participant_id'   => 'required',
            'participant_type' => 'required|string',
        ];
    }
}

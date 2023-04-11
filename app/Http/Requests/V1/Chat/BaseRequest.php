<?php

namespace App\Http\Requests\V1\Chat;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class BaseRequest
 * @property mixed participant_type
 * @property mixed participant_id
 * @package App\Http\Requests\V1\Chat
 */
class BaseRequest extends FormRequest
{
    /**
     * @return mixed
     */
    public function getParticipant(): mixed {
        return app($this->participant_type)->find($this->participant_id);
    }
}

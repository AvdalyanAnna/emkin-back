<?php

namespace App\Http\Requests\V1\Chat;

use App\Http\Requests\V1\FormRequest;
use App\Repositories\V1\Users\UserRepository;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class StoreConversation
 * @package App\Http\Requests\V1\Chat
 */
class StoreConversation extends FormRequest
{
    /**
     * @var array|\string[][]
     */
    protected array $rules = [
        'POST' => [
            'participant_id' => 'required|exists:users,id',
            'advertise_id' => 'required|exists:advertises,id',
            'data' => 'nullable|array',
        ]
    ];

    /**
     * @return bool
     */
    public function authorize(): bool {
        $conversation = \Chat::getInstance()->resetConversation(
            $this->user(),
            $this->get('participant_id'),
            $this->get('advertise_id'),
        );

        if ($conversation) {
            $this->request->set('conversation', $conversation);
            $this->rules[$this->getMethod()] = [];
        }

        return true;
    }

    /**
     * @return string[]
     */
    public function rules(): array {
        return $this->rules[$this->getMethod()];
    }

    /**
     * @return array
     */
    #[ArrayShape(['from' => "\App\Models\User|null", 'to' => "mixed"])] public function participants(): array {
        $userTo = app(UserRepository::class)->find($this->get('participant_id'));

        return [user(), $userTo];
    }
}

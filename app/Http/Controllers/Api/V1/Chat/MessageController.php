<?php

namespace App\Http\Controllers\Api\V1\Chat;

use App\Enums\Chat\ChatServiceNamesEnum;
use App\Events\Chat\MessageUnreadCounts;
use App\Exceptions\Chat\IncorrectServiceInstanceException;
use App\Http\Requests\V1\Chat\StoreMessage;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Chat\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
use App\Traits\ApiResponseAble;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class MessageController
 * @package App\Http\Controllers\Api\V1\Chat
 */
class MessageController extends Controller
{
    use ApiResponseAble;

    /**
     * @param Request $request
     * @param Conversation $conversation
     * @throws IncorrectServiceInstanceException
     * @return JsonResponse
     */
    public function messages(Request $request, Conversation $conversation): JsonResponse {
        $messages = \Chat::getInstance()
            ->conversation($conversation)
            ->serviceInstances(ChatServiceNamesEnum::CONVERSATIONS_SERVICE)
            ->setParticipant(user())
            ->setPaginationParams($request->getPaginationParams())
            ->getMessages();

        return $this->success($messages->toArray());
    }

    /**
     * @param StoreMessage $request
     * @param Conversation $conversation
     * @throws \Throwable
     * @throws \Throwable
     * @return MessageResource|JsonResponse
     */
    public function store(StoreMessage $request, Conversation $conversation): MessageResource|JsonResponse {
        try {
            \Chat::getInstance()
                ->conversation($conversation)
                ->message($request->get('message'))
                ->attachFiles($request->file('files', []))
                ->from(user())
                ->send();

            $message = \Chat::getInstance()->takeMessage();

            broadcast(MessageUnreadCounts::make($conversation, user()->id))->toOthers();

            return MessageResource::make($message->load('files'));
        } catch (\Exception $e) {
            \Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * @param Conversation $conversation
     * @param Message $message
     * @param string $action
     * @return JsonResponse
     */
    public function messageActions(Conversation $conversation, Message $message, string $action): JsonResponse {
        try {
            \Chat::getInstance()
                ->message($message)
                ->setParticipant(user())
                ->$action();

            return $this->success('ok', __('messages.SUCCESS_OPERATED'));
        } catch (\Exception $e) {
            \Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }
}

<?php

namespace App\Http\Controllers\Api\V1\Chat;

use App\Enums\Chat\ChatServiceNamesEnum;
use App\Events\Chat\MessageUnreadCounts;
use App\Exceptions\Chat\IncorrectServiceInstanceException;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Chat\ComplaintRequest;
use App\Http\Requests\V1\Chat\Conversations;
use App\Http\Requests\V1\Chat\StoreConversation;
use App\Http\Resources\V1\Admin\Support\ThemeResource;
use App\Http\Resources\V1\Chat\ConversationsResource;
use App\Http\Resources\V1\User\UserResource;
use App\Models\Conversation;
use App\Models\Media;
use App\Models\SupportTheme;
use App\Services\V1\Chat\ConversationService;
use App\Services\V1\Users\UserService;
use App\Traits\ApiResponseAble;
use Carbon\Carbon;
use Carbon\Traits\Creator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Class ConversationController
 * @package App\Http\Controllers\Api\V1\Chat
 */
class ConversationController extends Controller
{
    use ApiResponseAble;

    /**
     * ConversationController constructor.
     * @param ConversationService $conversationService
     */
    public function __construct(private ConversationService $conversationService) { }

    /**
     * @param StoreConversation $request
     * @throws \Throwable
     * @return ConversationsResource|JsonResponse
     */
    public function addConversations(StoreConversation $request): ConversationsResource|JsonResponse {
        try {
            $conversation = $request->get('conversation', false);

            if (!$conversation) {
                $conversation = \Chat::getInstance()
                    ->setStarter(user())
                    ->createConversation($request->participants(), [], $request->get('advertise_id'));
            }

            return new ConversationsResource(
                $conversation->fresh()->load(Conversation::$selectedRelationsConversation)
            );
        } catch (\Exception $e) {
            \Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @throws IncorrectServiceInstanceException
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function getConversations(Request $request): AnonymousResourceCollection|JsonResponse {
        $additionalData = ['conversation_type' => $type = $request->query('conversation_type', true)];

        try {
            $conversations = \Chat::getInstance()
                ->serviceInstances(ChatServiceNamesEnum::CONVERSATIONS_SERVICE)
                ->setPaginationParams($request->getPaginationParams())
                ->setParticipant(user())
                ->setFilters(['conversation_type' => $type, 'search' => $request->get('search')])
                ->get();

            $takeCounts = (bool)$request->query('take_counts', false);

            if ($takeCounts)
                $additionalData = array_merge($additionalData,
                    $this->conversationService->getConversationsCountsByTypesAndUnreadMessagesCount(user())
                );

            return ConversationsResource::collection($conversations)->additional($additionalData);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationResponse($e->errors());
        }
    }

    /**
     * @param Request $request
     * @throws IncorrectServiceInstanceException
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function getConversationsTickets(Request $request): AnonymousResourceCollection|JsonResponse {
        try {
            $theme = $request->get('theme_id');
            $conversations = \Chat::getInstance()
                ->serviceInstances(ChatServiceNamesEnum::CONVERSATIONS_SERVICE)
                ->setPaginationParams($request->getPaginationParams())
                ->setParticipant(user())
                ->setFilters([
                    'search' => $request->get('search'),
                    'conversation_type' => 'support',
                    'theme_id' => $theme,
                    'ticket_status' => $request->get('ticket_status'),
                ])
                ->get();

            return ConversationsResource::collection($conversations)->additional([
                'theme' => ThemeResource::make(SupportTheme::findOrFail($theme))
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationResponse($e->errors());
        }
    }

    /**
     * @param Request $request
     * @param UserService $userService
     * @return AnonymousResourceCollection|BinaryFileResponse
     */
    public function moderatorList(Request $request, UserService $userService): AnonymousResourceCollection|BinaryFileResponse {
        $collection = $userService->supportModerators()->where('id', '<>', user()->id);

        return UserResource::collection($collection->paginate($request->get('perPage')));
    }

    /**
     * @param Conversation $conversation
     * @param string $action (values = readAll or clear)
     * @throws IncorrectServiceInstanceException
     * @return JsonResponse
     */
    public function actionReadAllAndClear(Conversation $conversation, string $action): JsonResponse {
        \Chat::getInstance()
            ->conversation($conversation)
            ->serviceInstances(ChatServiceNamesEnum::CONVERSATIONS_SERVICE)
            ->setParticipant(user())
            ->$action();

        if ($action === 'readAll') {
            broadcast(MessageUnreadCounts::make($conversation, null, user()))->toOthers();
        }

       return $this->success('', __('messages.SUCCESS_OPERATED'));
    }

    /**
     * @param Request $request
     * @param Conversation $conversation
     * @throws IncorrectServiceInstanceException
     * @throws \Throwable
     * @return JsonResponse
     */
    public function changeParticipant(Request $request, Conversation $conversation): JsonResponse {
        $newModerator = $request->getParticipant();

        \Chat::getInstance()
            ->conversation($conversation)
            ->serviceInstances(ChatServiceNamesEnum::CONVERSATIONS_SERVICE)
            ->changeModerator(user(), $newModerator);

        return $this->success('', __('messages.SUCCESS_OPERATED'));
    }

    /**
     * @param Conversation $conversation
     * @throws IncorrectServiceInstanceException|\Throwable
     * @return JsonResponse
     */
    public function closeConversation(Conversation $conversation): JsonResponse {
        \Chat::getInstance()
            ->conversation($conversation)
            ->serviceInstances(ChatServiceNamesEnum::CONVERSATIONS_SERVICE)
            ->closeConversation(user());

        return $this->success('', __('messages.SUCCESS_OPERATED'));
    }

    /**
     * @param Conversations $request
     * @return JsonResponse
     */
    public function deleteConversations(Conversations $request): JsonResponse {
        try {
            \Chat::getInstance()->deleteConversations(user(), $request->get('conversations'));

            return $this->success('ok', __('messages.ITEM_DELETED'));
        } catch (\Exception $e) {
            \Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * @param ComplaintRequest $request
     * @param Conversation $conversation
     * @throws \Throwable
     * @return JsonResponse
     */
    public function addComplaint(ComplaintRequest $request, Conversation $conversation): JsonResponse {
        try {
            $request->request->set('user_id', user()->id);
            $request->request->set('conversation_id', $conversation->id);

            \Chat::getInstance()
                ->conversation($conversation)
                ->serviceInstances(ChatServiceNamesEnum::CONVERSATIONS_SERVICE)
                ->addComplaint($request->all());

            return $this->success('ok', __('messages.SUCCESS_OPERATED'));
        } catch (\Exception $e) {
            \Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @param Media $media
     * @return StreamedResponse|Response
     */
    public function download(Request $request, Media $media): StreamedResponse|Response {
        return $media->toResponse($request);
    }
}

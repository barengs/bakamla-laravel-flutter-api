<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetChatRequest;
use App\Http\Requests\StoreChatRequest;
use App\Models\Chat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use function Laravel\Prompts\error;

class ChatController extends Controller
{
    /**
     * Display a chats.
     *
     * @param GetChatRequest $request
     * 
     * @return JsonResponse
     */
    public function index(GetChatRequest $request): JsonResponse
    {
        $data = $request->validated();

        $isPrivate = 1;
        if ($request->has('is_private')) {
            $isPrivate = (int)$data['is_private'];
        }

        $chats = Chat::where('is_private', $isPrivate)
            ->hasParticipant(auth()->user()->id)
            ->whereHas('messages')
            ->with('lastMessage.user', 'participants.user')
            ->latest('updated_at')
            ->get();
        
        return $this->success($chats);
    }

    /**
     * store new chat
     *
     * @param StoreChatRequest $request
     * 
     * @return JsonResponse
     */
    public function store(StoreChatRequest $request): JsonResponse
    {
        $data = $this->prepareStoreData($request);

        if ($data['userId'] === $data['otherUserId']) {
            return $this->error('kamu tidak dapat membuat pesan untuk dirimu sendiri');
        }

        $previousChat = $this->getPreviousChat($data['otherUserId']);

        if ($previousChat === null) {
            $chat = Chat::create($data['data']);
            $chat->participants()->createMany([
                [
                    'user_id' => $data['userId'],
                ],
                [
                    'user_id' => $data['otherUserId'],
                ],
            ]);

            $chat->refresh()->load('lastMessage.user', 'participants.user');

            return $this->success($chat);
        }

        return $this->success($previousChat->load('lastMessage.user', 'participants.user'));
    }

    
    /**
     * check user and other user has Previous Chat
     *
     * @param int $otherUserId
     * 
     * @return mixed
     */
    private function getPreviousChat(int $otherUserId): mixed
    {
        $userId = auth()->user()->id;

        return Chat::where('is_private', 1)
            ->whereHas('participants', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->whereHas('participants', function($query) use ($otherUserId) {
                $query->where('user_id', $otherUserId);
            })
            ->first();
    }

    /**
     * prepares data for store a chat
     *
     * @param StoreChatRequest $request
     * 
     * @return array
     */
    private function prepareStoreData(StoreChatRequest $request): array
    {
        $data = $request->validated();

        $otherUserId = (int)$data['user_id'];
        unset($data['user_id']);
        $data['created_by'] = auth()->user()->id;

        return [
            'otherUserId' => $otherUserId,
            'userId' => auth()->user()->id,
            'data' => $data,
        ];
    }

    /**
     * show single chat
     *
     * @param Chat $chat
     * 
     * @return JsonResponse
     */
    public function show(Chat $chat): JsonResponse
    {
        $chat->load('lastMessage.user', 'participants.user');
        return $this->success($chat);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

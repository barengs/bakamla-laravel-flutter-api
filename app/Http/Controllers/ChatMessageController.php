<?php

namespace App\Http\Controllers;

use App\Events\NewMessageSent;
use App\Http\Requests\GetMessageRequest;
use App\Http\Requests\StoreMessageRequest;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

use function PHPUnit\Framework\isEmpty;

class ChatMessageController extends Controller
{
    /**
     * Get chat message
     *
     * @param GetMessageRequest $request
     * 
     * @return JsonResponse
     */
    public function index(GetMessageRequest $request): JsonResponse
    {
        $data = $request->validated();

        $chatId = $data['chat_id'];
        $currentPage = $data['page'];
        $pageSize = $data['page_size'] ?? 15;

        $messages = ChatMessage::where('chat_id', $chatId)
            ->with('user')
            ->latest('created_at')
            ->simplePaginate($pageSize, ['*'], 'page', $currentPage);
        
        return $this->success($messages->getCollection());
    }

    /**
     * create a chat message
     *
     * @param StoreMessageRequest $request
     * 
     * @return JsonResponse
     */
    public function store(StoreMessageRequest $request): JsonResponse
    {
        $data = $request->validated();

        $data['user_id'] = auth()->user()->id;
        $image = $request->file('images');
        $video = $request->file('videos');
        $doc = $request->file('documents');
        $audio = $request->file('audios');
        $loc = $request->input('locations');
        $contactName = $request->input('contact_name');
        if ($image !== null) {
            $this->validate($request, ['images' => 'max:10000|image:jpeg,jpg,png']);
            $date = Carbon::now()->format('dmYHis');
            $rand = Str::random(10);
            $filename = 'CDN-IMG-BM-' . $date . $rand . '.' . 'webp';
            $image->storeAs('public/image/',$filename);
            $data['images'] = $filename;
            if ($data['message'] == '') {
                $data['message'] = $filename;
            }
        }elseif($video !== null) {
            $request->validate(['videos' => 'max:50000|mimes:mp4,3gp,']);
            $date = Carbon::now()->format('dmYHis');
            $rand = Str::random(10);
            $filename = 'CDN-VID-BM-' . $date . $rand . '.' . 'webm';
            $video->storeAs('public/video/',$filename);
            $data['videos'] = $filename;
            if ($data['message'] == '') {
                $data['message'] = $filename;
            }
        } elseif($doc !== null){
            $ext = $doc->getClientOriginalExtension();
            $date = Carbon::now()->format('dmYHis');
            $rand = Str::random(10);
            $filename = 'CDN-DOC-BM-' . $date . $rand . '.' . $ext;
            $doc->storeAs('public/document/',$filename);
            $data['documents'] = $filename;
            if ($data['message'] == '') {
                $data['message'] = $filename;
            }
        }elseif($audio !== null){
            $ext = $audio->getClientOriginalExtension();
            $date = Carbon::now()->format('dmYHis');
            $rand = Str::random(10);
            $filename = 'CDN-VM-BM-' . $date . $rand . '.' . $ext;
            $audio->storeAs('public/audio/',$filename);
            $data['audios'] = $filename;
            if ($data['message'] == '') {
                $data['message'] = $filename;
            }

        }elseif(isset($loc) && $loc != 0){
            $data['longitude'] = $request->longitude;
            $data['latitude'] = $request->latitude;
            $data['delete_time'] = $request->delete_time;
            if ($data['message'] == '') {
                $data['message'] = $data['locations'];
            }
        }elseif($contactName != null){
            $data['contact_name'] = $contactName;
            $data['contact_number'] = $request->contact_number;
            if ($data['message'] == '') {
                $data['message'] = $data['contact_name'];
            }
        }
        else{
            $this->validate($request, ['message' => 'required|string']);
            $data['message'] = $request->message;
        }

        $chatMessage = ChatMessage::create($data);
        $chatMessage->load('user');

        // TODO send broadcast event to pusher and send notification to onesignal service
        $this->sendNotificationToOther($chatMessage);

        return $this->success($chatMessage, 'Pesan berhasil di kirim!');
    }

    /**
     * send notification to another user
     *
     * @param ChatMessage $chatMessage
     * 
     * @return void
     */
    private function sendNotificationToOther(ChatMessage $chatMessage): void
    {
        // $chatId = $chatMessage->chat_id;

        broadcast(new NewMessageSent($chatMessage))->toOthers();

        $user = auth()->user();
        $userId = $user->id;

        $chat = Chat::where('id', $chatMessage->chat_id)
            ->with(['participants' => function($query) use ($userId) {
                $query->where('user_id', '!=', $userId);
            },])
            ->first();

        
        if (count($chat->participants) > 0) {
            $otherUserId = $chat->participants[0]->user_id;

            $otherUser = User::where('id', $otherUserId)->first();
            // $foto = 'Foto';
            $msg = $chatMessage->message != $chatMessage->images ? $chatMessage->message : 'Foto';

            // $msg = $chatMessage->message != '' && $chatMessage->message == $chatMessage->images ? $chatMessage->message : 'Foto';
            $otherUser->sendNewMessageNotification([
                'messageData' => [
                    'senderName' => $user->username,
                    // 'message' => $chatMessage->message,
                    'message' => $msg,
                    'chatId' => $chatMessage->chat_id,
                ]
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ChatMessage $chatMessage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ChatMessage $chatMessage)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ChatMessage $chatMessage)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ChatMessage $chatMessage)
    {
        //
    }
}

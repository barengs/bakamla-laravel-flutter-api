<?php

namespace App\Http\Controllers;

use App\Http\Requests\EditAvatarRequest;
use App\Http\Requests\EditBioRequest;
use App\Http\Requests\EditPhonesRequest;
use App\Http\Requests\EditUsernameRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $auth = User::find(auth()->user()->id);
        return response()->json($auth, 200);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function updateUsername(EditUsernameRequest $request)
    {
        $data = $request->validated();

        $auth = User::where('id', auth()->user()->id)->first();

        if (!empty($data['username'])) {
            $auth->username = $data['username'];
            $auth->update();
        };
        
        return $this->success($auth);
    }

    public function updateBio(EditBioRequest $request) : JsonResponse
    {
        $data = $request->validated();

        $auth = User::where('id', auth()->user()->id)->first();
        if (!empty($data['bio'])) {
            $auth->bio = $data['bio'];
            $auth->update();
        };
        return $this->success($auth);
        
    }

    public function updateNumber(EditPhonesRequest $request)
    {
        $data = $request->validated();

        $auth = User::where('id', auth()->user()->id)->first();

        if (!empty($data['phones'])) {
            $auth->phones = $data['phones'];
            $auth->update();
        };
        
        return $this->success($auth);
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateAvatar(EditAvatarRequest $request)
    {
        $data = $request->validated();

        $auth = User::where('id', auth()->user()->id)->first();

        if (!empty($data['avatar'])) {
            if ($auth->avatar) {
                $oldAvatarPath = public_path('avatar/' . $auth->avatar);
                if (file_exists($oldAvatarPath)) {
                    File::delete(public_path('avatar/' . $auth->avatar));
                    unlink($oldAvatarPath);
                }
            }

            $filename = 'CDN-IMG-BM-AVATAR' . $auth->id . '.' . 'webp';
            $data['avatar']->storeAs('public/avatar/',$filename);
            $auth->avatar = $filename;
            $auth->update();
        }
        return $this->success($auth);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

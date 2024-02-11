<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $auth = User::where('id', '==', auth()->user()->id)->first();
        return response()->json($auth, 200);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function updateProfile(Request $request)
    {
       $auth = User::where('id', '==', auth()->user()->id)->first();
        $phones = $request->string('phones');
        $dt = [
            'phones' => $phones
        ];
        $auth->update($dt);
        // return $this->success($auth);
        return response()->json($auth, 200);

    }

    /**
     * Update the specified resource in storage.
     */
    public function updateAvatar(Request $request)
    {
        $auth = User::where('id', '==', auth()->user()->id)->first();
        $avatar = $request->file('avatar');
        if ($avatar !== null) {
            $this->validate($request, ['avatar' => 'max:10000|avatar:jpeg,jpg,png']);
            $filename = 'CDN-IMG-BM-AVATAR' . $auth->id . '.' . 'webp';
            $avatar->storeAs('public/avatar/',$filename);
            $avatar['avatar'] = $filename;
        }
        // return $this->success($auth);
        return response()->json($auth, 200);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

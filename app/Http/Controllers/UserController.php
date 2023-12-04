<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;

class UserController extends Controller
{
    /**
     * Get all user except yourself
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $users = User::where('id', '!=', auth()->user()->id)->get();
        return $this->success($users);
    }

    public function editProfile(Request $request) {
        $auth = Auth::user();
        $update = User::findorfail($auth->id);
        if ($auth) {
            $username = $request->username;
            $dt = [
                'username' => $username,
            ];
            $data = $update->update($dt);
        }
        return response()->json($data, 200);
    }
}

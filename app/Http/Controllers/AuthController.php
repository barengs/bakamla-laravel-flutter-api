<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * register user
     *
     * @param RegisterRequest $request
     * 
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        /** validasi email dan password */
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        /** jadikan email sebagai username */
        $data['username'] = strstr($data['email'], '@', true);
        
        $user = User::create($data);
        $token = $user->createToken(User::USER_TOKEN);

        return $this->success([
            'user' => $user,
            'token' => $token->plainTextToken,
        ], 'Pengguna berhasil di daftarkan!');
    }
    
    /**
     * user login
     *
     * @param LoginRequest $request
     * 
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $isValid = $this->isValidCredential($request);
        /** jika status tidak sukses */
        if (!$isValid['success']) {
            return $this->error($isValid['message'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = $isValid['user'];
        $token = $user->createToken(User::USER_TOKEN);

        return $this->success([
            'user' => $user,
            'token' => $token->plainTextToken,
        ], 'Login Berhasil!');
    }

    /**
     * isValidCredential
     *
     * @param LoginRequest $request
     * 
     * @return array
     */
    private function isValidCredential(LoginRequest $request): array
    {
        $data = $request->validated();

        $user = User::where('email', $data['email'])->first();
        if ($user === null) {
            return [
                'success' => false,
                'message' => 'invalid credential',
            ];
        }
        /** jika user ada */
        if (Hash::check($data['password'], $user->password)) {
            return [
                'success' => true,
                'user' => $user,
            ];
        }
        /** jika user tidak ditetmukan */
        return [
            'success' => false,
            'message' => 'password tidak sesuai!',
        ];
    }

    /**
     * user loginWithToken
     *
     * @return JsonResponse
     */
    public function loginWithToken(): JsonResponse
    {
        return $this->success(auth()->user(), 'Login berhasil!');
    }
    
    /**
     * user logout
     *
     * @param Request $request
     * 
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success(null, 'User berhasil keluar!');
    }
}

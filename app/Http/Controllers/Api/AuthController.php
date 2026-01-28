<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Users;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_users' => 'required|string|max:255',
            'pass_users' => 'required|string|min:8',
            'alamat' => 'string|max:255',
            'no_telp' => 'string|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $validated = $validator->validated();
        $rand_number = rand(000,999);

        $user = Users::create([
            'kode_unik' => 'CUST-' . $rand_number,
            'nama_users' => $validated['nama_users'],
            'pass_users' => Hash::make($validated['pass_users']),
            'alamat' => $validated['alamat'],
            'no_telp' => $validated['no_telp'],
            'role_id' => 'customer',
            'status' => 'active',
        ]);

        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'no_telp' => 'required',
            'pass_users' => 'required|string',
        ]);

        // Cek user berdasarkan username
        $user = Users::where('no_telp', $request->no_telp)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Username tidak ditemukan'
            ], 404);
        }

        // Cek password
        if (!Hash::check($request->pass_users, $user->pass_users)) {
            return response()->json([
                'success' => false,
                'message' => 'Password tidak sesuai'
            ], 401);
        }

        // cek status akun
        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak aktif'
            ], 403);
        }

        // Generate JWT
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'user' => $user,
            'token' => $token,
        ]);
    }


    public function logout()
    {
        try {
            auth()->logout();

            return response()->json([
                'message' => 'Successfully logged out'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to logout',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
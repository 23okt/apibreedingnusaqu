<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Investment;
use App\Models\Users;
use Illuminate\Support\Facades\Validator;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class InvestmentController extends Controller
{
    public function index()
    {
        $investment = Investment::with('users')->get();

        try {
            return response()->json([
                'success' => true,
                'message' => 'Data investasi berhasil di tampilkan',
                'data' => $investment
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th,
                'data' => $investment
            ], 500);
        }
    }

    public function store(Request $request)
    {
        
        $FILE_SIZE = 1024 * 5;
        $rand_number = rand(000, 999);

        try {
            $validator = Validator::make($request->all(), [
                'jumlah_inves' => 'required|int|min:1',
                'jumlah_inves_terbilang' => 'required|string|min:1',
                'metode_pembayaran' => 'required|string|min:1',
                'bukti_pembayaran' => "required|file|max:{$FILE_SIZE}|mimes:png,jpg,jpeg",
                'tanggal_investasi' => 'required|string',
                'status' => 'required|string',
                'description' => 'required|string|min:5',
                'users_id' => 'required|exists:users,kode_unik'
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Failed to add data investasi',
                    'error' => $validator->errors()
                ], 422);
            }
    
            $result = $validator->validated();
    
            $users = Users::where('kode_unik', $result['users_id'])->firstOrFail();
            $result['users_id'] = $users->id_users;
            
    
            if ($request->hasFile('bukti_pembayaran')) {
                try {
                    $file = $request->file('bukti_pembayaran')->getRealPath();
                    $cloudinary = new Cloudinary();
    
                    $uploadFile = Cloudinary::uploadApi()->upload($file, [
                        'folder' => 'nusaqu'
                    ]);
    
                    $result["bukti_pembayaran"] = $uploadFile['secure_url'];
                } catch (\Exception $e) {
                    return response()->json([
                        'message' => 'Failed to upload image to Cloudinary',
                        'error' => $e->getMessage()
                    ], 500);
                }
            }
    
            $investment = Investment::create(array_merge($result, [
                'kode_investasi' => 'IVS-' . $rand_number,
            ]));
    
            return response()->json([
                'success' => true,
                'message' => 'Data investasi berhasil ditambahkan',
                'data' => $investment
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data investasi gagal ditambahkan',
                'data' => $th->getMessage()
            ], 500);
        }
    }


    public function show($kode_investasi)
    {
        $investment = Investment::where('kode_investasi', $kode_investasi)->first();

        try {
            if (!$investment) {
                return response([
                    'message' => 'ID tidak ditemukan'
                ], 404);
            }
    
            return response([
                'success' => true,
                'message' => 'Data berhasil ditemukan.',
                'data' => $investment,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data gagal ditemukan.',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, string $kode_investasi)
    {
        $FILE_SIZE = 1024 * 5;
        $investment = Investment::where('kode_investasi', $kode_investasi)->first();

        try {
            if (!$investment) {
                return response()->json([
                    'message' => 'ID tidak ditemukan.'
                ], 400);
            }
    
            $validator = $request->validate([
                'jumlah_inves' => 'required|int|min:1',
                'jumlah_inves_terbilang' => 'required|string|min:1',
                'metode_pembayaran' => 'required|string|min:1',
                'bukti_pembayaran' => "nullable|file|max:{$FILE_SIZE}|mimes:png,jpg,jpeg",
                'tanggal_investasi' => 'required|string',
                'status' => 'required|string',
                'description' => 'required|string|min:5',
                'users_id' => 'required|exists:users,kode_unik'
            ]);

            $investCode = $validator['users_id'];
            $user = Users::where('kode_unik',$investCode)->firstOrFail();
            $validator['users_id'] = $user->id_users;

            if ($request->hasFile('bukti_pembayaran')) {
                if (!empty($investment->bukti_pembayaran)) {
                    try {
                        $publicId = $this->getPublicIdFromCloudinaryUrl($investment->bukti_pembayaran);
                        Cloudinary::uploadApi()->destroy($publicId);
                    } catch (\Throwable $th) {
                        return $th->getMessage();
                    }
                }
                try {
                    $file = $request->file('bukti_pembayaran')->getRealPath();
                    $cloudinary = new Cloudinary();

                    $uploadFile = Cloudinary::uploadApi()->upload($file, [
                        'folder' => 'nusaqu'
                    ]);

                    $validator["bukti_pembayaran"] = $uploadFile['secure_url'];
                } catch (\Throwable $th) {
                    return response()->json([
                        'message' => 'Failed to upload image to Cloudinary',
                        'error' => $e->getMessage()
                    ], 500);
                }
            }
    
            $investment->update($validator);
            return response()->json([
                'success' => true,
                'messsage' => 'Product has successfully updated',
                'data' =>  $investment
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data investasi gagal diubah.',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($kode_investasi)
    {
        $investment = Investment::where('kode_investasi',$kode_investasi)->first();

        try {
            if (!$investment) {
                return response([
                    'message' => 'ID tidak ditemukan'
                ], 404);
            }
    
            $investment->delete();
            return response([
                'success' => true,
                'message' => 'Berhasil menghapus data investment',
                'data' => $investment
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data investment',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function getTotalInvestment()
    {
        $total_investasi = Investment::sum('jumlah_inves');

        try {
            return response()->json([
                'success' => true,
                'message' => 'Data investasi berhasil di tampilkan',
                'data' => $total_investasi
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th,
                'data' => $total_investasi
            ], 500);
        }
    }

    public function getTotalInvestByUsers($users_id)
    {
        $investment = Investment::where('users_id', $users_id)
                                    ->sum('jumlah_inves');
        try {
            return response()->json([
                'success' => true,
                'message' => 'Data Total Invest berhasil diambil',
                'data' => $investment
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th,
            ], 500);
        }
    }

    /**
     * Mengambil public_id dari URL Cloudinary
     */
    private function getPublicIdFromCloudinaryUrl($url)
    {
        $parts = explode('/', parse_url($url, PHP_URL_PATH));
        $file = end($parts);
        return pathinfo($file, PATHINFO_FILENAME);
    }
}
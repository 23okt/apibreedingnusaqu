<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Users;
use App\Models\Goats;
use App\Models\ItemInvestasi;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class InvestorController extends Controller
{
    private $successMessage = 'Data mitra telah ditemukan';
    private $failedMessage = 'Data mitra gagal ditemukan';

    public function index(Request $request)
    {
        $users = Users::where('role', 'customer')->get();

        try {
            return response()->json([
                'success' => true,
                'message' => 'Data Users berhasil di tampilkan',
                'data' => $users
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data Users gagal di tampilkan',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nama_users' => 'required|string',
                'alamat' => 'required|string',
                'no_telp' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal Menambahkan Users.',
                    'error' => $validator->errors()
                ], 422);
            }

            $result = $validator->validated();
    
            $users = Users::create(array_merge($result,[
                'pass_users' => Hash::make('mitranusaqu2026'),
                'role' => 'customer',
                'status' => 'active',
                'kode_unik' => 'CUST-' . rand(100,999),
            ]));
    
            return response()->json([
                'success' => true,
                'message' => 'Berhasil Menambahkan Mitra',
                'data' => $users
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal Menambahkan Mitra.',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function show($kode_unik)
    {
        $users = Users::where('kode_unik', $kode_unik)->where('role', 'customer')->first();

        try {
            if ($users) {
                return response()->json([
                    'success' => true,
                    'message' => $this->successMessage,
                    'data' => $users
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $this->failedMessage,
                    'data' => null
                ], 404);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data mitra.',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $kode_unik)
    {
        try {
            $users = Users::where('kode_unik', $kode_unik)->where('role', 'customer')->first();

            if (!$users) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data mitra tidak ditemukan.',
                    'data' => null
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'nama_users' => 'sometimes|required|string',
                'pass_users' => 'sometimes|required|string',
                'alamat' => 'sometimes|required|string',
                'no_telp' => 'sometimes|required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi data gagal.',
                    'error' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            if (isset($data['pass_users'])) {
                $data['pass_users'] = Hash::make($data['pass_users']);
            }

            $users->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Data mitra berhasil diperbarui.',
                'data' => $users
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data mitra.',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($kode_unik)
    {
        try {
            $users = Users::where('kode_unik', $kode_unik)->where('role', 'customer')->first();

            if (!$users) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data mitra tidak ditemukan.',
                ], 404);
            }

            $users->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data mitra berhasil dihapus.',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data mitra.',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function products($id_users)
    {
        $products = Goats::whereHas('investments', function ($q) use ($id_users) {
            $q->where('users_id', $id_users);
        })
        ->with([
            'health',
            'breedingAsFemale',
            'investments' => function ($q) use ($id_users) {
                $q->where('users_id', $id_users)
                    ->select('investasi.id_investasi');
            }
        ])
        ->get()
        ->map(function ($product) {
            return [
                'id_product' => $product->id_product,
                'kode_product' => $product->kode_product,
                'jenis_product' => $product->jenis_product,
                'type_product' => $product->type_product,
                'nama_product' => $product->nama_product,
                'gender' => $product->gender,
                'bobot' => $product->bobot,
                'status' => $product->status,
                'photo1' => $product->photo1,
                'photo2' => $product->photo2,
                'photo3' => $product->photo3,

                'total_investasi' => $product->investments->sum(
                    fn ($inv) => $inv->pivot->jumlah_investasi
                ),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }


    public function AddProduct(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'investasi_id' => 'required|exists:investasi,id_investasi',
                'product_id' => 'required|exists:product,id_product',
                'jumlah_investasi' => 'required|integer',
            ]);

            if($validator->fails()){
                return response()->json([
                    'message' => 'Failed to add data investasi',
                    'error' => $validator->errors()
                ], 422);
            }

            $result = $validator->validated();
            $item = ItemInvestasi::create($result);
            return response()->json([
                'success' => true,
                'message' => 'Data Item Investasi berhasil di tambahkan',
                'data' => $item
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data Item Investasi gagal ditambahkan',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function getTotalMitra()
    {
        $total_mitra = Users::where('role', 'customer')->count();

        try {
            return response()->json([
                'success' => true,
                'message' => 'Data Users berhasil di tampilkan',
                'data' => $total_mitra
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data Users gagal di tampilkan',
                'data' => $th->getMessage()
            ], 500);
        }
    }
}
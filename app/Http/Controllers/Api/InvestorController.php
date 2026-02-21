<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Users;
use App\Models\Goats;
use Illuminate\Validation\Rule;
use App\Models\Investment;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ItemInvestasi;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class InvestorController extends Controller
{
    private $successMessage = 'Data mitra telah ditemukan';
    private $failedMessage = 'Data mitra gagal ditemukan';

    public function index(Request $request)
    {
        $users = Users::where('role', 'customer')->orderBy('created_at','desc')->get();

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
                'no_telp' => 'required|string|unique:users,no_telp',
            ]);

            if ($validator->fails()) {
                if ($validator->errors()->has('no_telp')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Nomor telepon sudah terdaftar',
                        'errors' => $validator->errors()->get('no_telp')
                    ], 422);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
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
            $users = Users::where('kode_unik', $kode_unik)
                ->where('role', 'customer')
                ->first();

            if (!$users) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data mitra tidak ditemukan.',
                    'data' => null
                ], 404);
            }

            // ===============================
            // RULE DASAR
            // ===============================
            $rules = [
                'nama_users' => 'sometimes|required|string',
                'pass_users' => 'sometimes|required|string',
                'alamat'     => 'sometimes|nullable|string',
            ];

            // ===============================
            // VALIDASI no_telp HANYA JIKA BERUBAH
            // ===============================
            if (
                $request->filled('no_telp') &&
                $request->no_telp !== $users->no_telp
            ) {
                $rules['no_telp'] = [
                    'string',
                    Rule::unique('users', 'no_telp')
                        ->ignore($users->id_users, 'id_users'),
                ];
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi data gagal.',
                    'error'   => $validator->errors()
                ], 422);
            }

            // ===============================
            // DATA FINAL
            // ===============================
            $data = $validator->validated();

            // HASH PASSWORD JIKA ADA
            if (isset($data['pass_users'])) {
                $data['pass_users'] = Hash::make($data['pass_users']);
            }

            $users->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Data mitra berhasil diperbarui.',
                'data'    => $users
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data mitra.',
                'data'    => $th->getMessage()
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


    public function AddProduct(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'investasi_id' => 'required|exists:investasi,id_investasi',
                'products' => 'required|array|min:1',
                'products.*.product_id' => 'required|exists:product,id_product',
                'products.*.jumlah_investasi' => 'required|integer|min:1',
            ], [
                'products.required' => 'Minimal harus ada 1 produk',
                'products.*.product_id.required' => 'Product ID harus diisi',
                'products.*.product_id.exists' => 'Product tidak ditemukan',
                'products.*.jumlah_investasi.required' => 'Jumlah investasi harus diisi',
                'products.*.jumlah_investasi.integer' => 'Jumlah investasi harus berupa angka',
            ]);

            if($validator->fails()){
                return response()->json([
                    'message' => 'Failed to add data investasi',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();
            $items = [];

            // Loop through products and create item investasi
            foreach ($validated['products'] as $product) {
                $item = ItemInvestasi::create([
                    'investasi_id' => $validated['investasi_id'],
                    'product_id' => $product['product_id'],
                    'jumlah_investasi' => $product['jumlah_investasi'],
                ]);
                $items[] = $item;
            }

            return response()->json([
                'success' => true,
                'message' => 'Data Item Investasi berhasil ditambahkan',
                'data' => $items,
                'total' => count($items)
            ], 201);
            
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data Item Investasi gagal ditambahkan',
                'error' => $th->getMessage()
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

    public function kuitansi($kode)
    {
        ini_set('memory_limit', '512M');

        $invest = Investment::with('users')
            ->where('kode_investasi', $kode)
            ->first();

        if (!$invest) {
            return response()->json(['error' => 'Data tidak ditemukan']);
        }

        return response()->json([
            'success' => true,
            'nama' => $invest->users->nama_users ?? 'null'
        ]);
    }
}
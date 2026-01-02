<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Goats;
use App\Models\Users;
use Illuminate\Support\Facades\Validator;

class TransaksiController extends Controller
{
    public function index()
    {
        $transaksi = Transaksi::get();

        try {
            return response()->json([
                'success' => true,
                'message' => 'Data transaksi berhasil di tampilkan.',
                'data' => $transaksi
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data transaksi berhasil di tampilkan.',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $rand_number = rand(100,999);
        $validator = Validator::make($request->all(),[
            'product_id' => 'required|exists:product,kode_product',
            'users_id' => 'required|exists:users,kode_unik',
            'harga_beli' => 'required|min:1',
            'harga_jual' => 'required|min:1',
            'bobot' => 'required|int|min:1',
        ]);

        try {
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Failed to add data transaksi.',
                    'error' => $validator->errors()
                ], 422);
            }
            $result = $validator->validated();
            $product = Goats::where('kode_product', $result['product_id'])->firstOrFail();
            $users = Users::where('kode_unik', $result['users_id'])->firstOrFail();
            $result['users_id'] = $users->id_users;
            $result['product_id'] = $product->id_product;

            $transaksi = Transaksi::create(array_merge($result, [
                'kode_transaksi' => 'TRA-' . $rand_number,
            ]));

            $goat = Goats::find($result['product_id']);
            if ($goat) {
                $goat->update([
                    'harga_jual' => $result['harga_jual'],
                    'harga_beli' => $result['harga_beli']
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Data transaksi berhasil ditambahkan.",
                'data' => $transaksi
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data transaksi gagal ditambahkan.',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function show($kode_transaksi)
    {
        $transaksi = Transaksi::where('kode_transaksi', $kode_transaksi)->first();

        try {
            if (!$transaksi) {
                return response([
                    'message' => 'ID tidak ditemukan.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data transaksi berhasil ditemukan',
                'data' => $transaksi
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data transaksi gagal ditemukan.',
                'data' => $th->getMessage()
            ], 500);
        }
    }
    
    public function update(Request $request, string $kode_transaksi)
    {
        $transaksi = Transaksi::where('kode_transaksi', $kode_transaksi)->first();

        if (!$transaksi) {
            return response()->json([
                'message' => 'ID tidak ditemukan.'
            ], 404);
        }

        try {
            $result = $request->validate([
                'product_id' => 'required|exists:product,kode_product',
                'users_id'   => 'required|exists:users,kode_unik',
                'harga_beli' => 'required|min:1',
                'harga_jual' => 'required|min:1',
                'bobot'      => 'required|int|min:1',
            ]);

            // Ambil relasi product dan user
            $product = Goats::where('kode_product', $result['product_id'])->firstOrFail();
            $user    = Users::where('kode_unik', $result['users_id'])->firstOrFail();

            // Ubah ke ID sebenarnya
            $result['product_id'] = $product->id_product;
            $result['users_id']   = $user->id_users;

            // Update tabel transaksi
            $transaksi->update($result);

            // Update harga di tabel goats
            $product->update([
                'harga_jual' => $result['harga_jual'],
                'harga_beli' => $result['harga_beli'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data transaksi berhasil diubah.',
                'data' => $transaksi
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data transaksi gagal diubah.',
                'error' => $th->getMessage()
            ], 500);
        }
    }


    public function destroy($kode_transaksi)
    {
        $transaksi = Transaksi::where('kode_transaksi', $kode_transaksi)->first();

        try {
            if (!$transaksi) {
                return response()->json([
                    'message' => 'ID tidak ditemukan.'
                ], 404);
            }

            $transaksi->delete();
            return response()->json([
                'success' => true,
                'message' => 'Berhasil menghapus data transaksi.',
                'data' => $transaksi
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data transaksi',
                'data' => $th->getMessage()
            ], 500);
        }
    }

}
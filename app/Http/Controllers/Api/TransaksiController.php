<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Goats;
use App\Models\Users;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

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
        $request->validate([
            'nama_pembeli' => 'required',
            'jumlah_nominal' => 'required|integer|min:1',
            'jumlah_nominal_terbilang' => 'required|string',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:product,kode_product',
            'products.*.harga_beli' => 'required|integer|min:1',
            'products.*.harga_jual' => 'required|integer|min:1',
            'products.*.bobot' => 'required|integer|min:1',
            'tanggal_transaksi' => 'required|date',
            'status_pembayaran' => 'required|string',
            'bukti_pembayaran' => 'required|image|mimes:jpeg,png,jpg,webp|max:5048'
        ]);

        try {

            DB::beginTransaction();

            $kode = 'TRA-' . rand(100,999);

            $buktiUrl = null;
            if ($request->hasFile('bukti_pembayaran')) {

                $file = $request->file('bukti_pembayaran')->getRealPath();

                $uploadFile = Cloudinary::uploadApi()->upload($file, [
                    'folder' => 'nusaqu/transaksi'
                ]);

                $buktiUrl = $uploadFile['secure_url'];
            }

            $transaksi = Transaksi::create([
                'kode_transaksi' => $kode,
                'nama_pembeli' => $request->nama_pembeli,
                'jumlah_nominal' => $request->jumlah_nominal,
                'jumlah_nominal_terbilang' => $request->jumlah_nominal_terbilang,
                'tanggal_transaksi' => $request->tanggal_transaksi,
                'bukti_pembayaran' => $buktiUrl,
                'status_pembayaran' => $request->status_pembayaran
            ]);

            foreach ($request->products as $item) {

                // 🔥 Pastikan hanya product Tersedia yang bisa dibeli
                $product = Goats::where('kode_product', $item['product_id'])
                    ->where('status', 'Hidup')
                    ->firstOrFail();

                $transaksi->itemTransaksi()->create([
                    'product_id' => $product->id_product,
                    'harga_beli' => $item['harga_beli'],
                    'harga_jual' => $item['harga_jual'],
                    'bobot' => $item['bobot'],
                ]);

                $product->update([
                    'harga_jual' => $item['harga_jual'],
                    'harga_beli' => $item['harga_beli'],
                    'bobot' => $item['bobot'],
                ]);

                // 🔥 Kalau status Terbayar → langsung jadi Terjual
                if ($request->status_pembayaran == 'Terbayar') {
                    $product->update([
                        'status' => 'Terjual'
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dibuat.',
                'data' => $transaksi->load('itemTransaksi')
            ], 201);

        } catch (\Throwable $th) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Transaksi gagal dibuat.',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function show($kode_transaksi)
    {
        try {
            $transaksi = Transaksi::with('itemTransaksi.product')
                ->where('kode_transaksi', $kode_transaksi)
                ->first();

            if (!$transaksi) {
                return response()->json([
                    'success' => false,
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
                'error' => $th->getMessage()
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

        $request->validate([
            'nama_pembeli' => 'required',
            'jumlah_nominal' => 'required|integer|min:1',
            'jumlah_nominal_terbilang' => 'required|string',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:product,kode_product',
            'products.*.harga_beli' => 'required|numeric|min:1',
            'products.*.harga_jual' => 'required|numeric|min:1',
            'products.*.bobot' => 'required|numeric|min:1',
            'tanggal_transaksi' => 'required|date',
            'status_pembayaran' => 'required|string',
            'bukti_pembayaran' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5048'
        ]);

        try {

            DB::beginTransaction();

            $buktiUrl = $transaksi->bukti_pembayaran;

            /*
            |--------------------------------------------------------------------------
            | 🔥 Kalau upload bukti baru
            |--------------------------------------------------------------------------
            */
            if ($request->hasFile('bukti_pembayaran')) {

                // hapus lama dari cloudinary
                if ($transaksi->bukti_pembayaran) {

                    $publicId = pathinfo(parse_url($transaksi->bukti_pembayaran, PHP_URL_PATH), PATHINFO_FILENAME);

                    Cloudinary::uploadApi()->destroy('nusaqu/transaksi/' . $publicId);
                }

                $file = $request->file('bukti_pembayaran')->getRealPath();

                $uploadFile = Cloudinary::uploadApi()->upload($file, [
                    'folder' => 'nusaqu/transaksi'
                ]);

                $buktiUrl = $uploadFile['secure_url'];
            }

            /*
            |--------------------------------------------------------------------------
            | Update transaksi utama
            |--------------------------------------------------------------------------
            */
            $transaksi->update([
                'nama_pembeli' => $request->nama_pembeli,
                'jumlah_nominal' => $request->jumlah_nominal,
                'jumlah_nominal_terbilang' => $request->jumlah_nominal_terbilang,
                'tanggal_transaksi' => $request->tanggal_transaksi,
                'status_pembayaran' => $request->status_pembayaran,
                'bukti_pembayaran' => $buktiUrl
            ]);

            /*
            |--------------------------------------------------------------------------
            | Kembalikan status product lama ke Hidup
            |--------------------------------------------------------------------------
            */
            foreach ($transaksi->itemTransaksi as $oldItem) {
                $oldItem->product->update([
                    'status' => 'Hidup'
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Hapus item lama
            |--------------------------------------------------------------------------
            */
            $transaksi->itemTransaksi()->delete();

            /*
            |--------------------------------------------------------------------------
            | Insert ulang item baru
            |--------------------------------------------------------------------------
            */
            foreach ($request->products as $item) {

                $product = Goats::where('kode_product', $item['product_id'])->firstOrFail();

                $transaksi->itemTransaksi()->create([
                    'product_id' => $product->id_product,
                    'harga_beli' => $item['harga_beli'],
                    'harga_jual' => $item['harga_jual'],
                    'bobot' => $item['bobot'],
                ]);

                $product->update([
                    'harga_jual' => $item['harga_jual'],
                    'harga_beli' => $item['harga_beli'],
                    'bobot' => $item['bobot'],
                ]);

                if ($request->status_pembayaran == 'Terbayar') {
                    $product->update([
                        'status' => 'Terjual'
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data transaksi berhasil diubah.',
                'data' => $transaksi->load('itemTransaksi.product')
            ], 200);

        } catch (\Throwable $th) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Data transaksi gagal diubah.',
                'error' => $th->getMessage()
            ], 500);
        }
    }


    public function destroy($kode_transaksi)
    {
        $transaksi = Transaksi::with('itemTransaksi.product')
            ->where('kode_transaksi', $kode_transaksi)
            ->first();

        if (!$transaksi) {
            return response()->json([
                'message' => 'ID tidak ditemukan.'
            ], 404);
        }

        DB::beginTransaction();

        try {

            foreach ($transaksi->itemTransaksi as $item) {

                // Kembalikan status product
                if ($item->product) {
                    $item->product->update([
                        'status' => 'Hidup'
                    ]);
                }

                // Hapus item transaksi
                $item->delete();
            }

            // Hapus transaksi
            $transaksi->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dihapus dan status product dikembalikan.'
            ], 200);

        } catch (\Throwable $th) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus transaksi',
                'error' => $th->getMessage()
            ], 500);
        }
    }

}
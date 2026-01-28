<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Penanganan;
use App\Models\HealthRecord;
use App\Models\Pharmacy;
use App\Models\ItemPenanganan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PenangananController extends Controller
{
    //

    public function index()
    {
        $penanganan = Penanganan::with(['kesehatan','items.obat'])->orderBy('created_at', 'desc')->get();

        try {
            return response()->json([
                'success' => true,
                'message' => 'Data penanganan berhasil di tampilkan',
                'data' => $penanganan
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data penanganan gagal di tampilkan.',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $rand_number = rand(100, 999);

        $validator = Validator::make($request->all(), [
            'kesehatan_id' => 'required|exists:kesehatan,id_kesehatan',
            'judul_penanganan' => 'required|string|max:255',
            'catatan_penanganan' => 'nullable|string',
            'tanggal_penanganan' => 'required|date',

            // Wajib item obat
            'obat_id' => 'required|string|exists:obat,id_obat',
            'jumlah_terpakai' => 'required|int|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Failed to add data penanganan',
                'error' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $result = $validator->validated();

            // // Cari kesehatan berdasarkan kode_kesehatan
            // $kesehatan = HealthRecord::where('kode_kesehatan', $result['kesehatan_id'])->firstOrFail();
            // $result['kesehatan_id'] = $kesehatan->id_kesehatan;
            
            // $obat = Pharmacy::where('kode_obat', $result['obat_id'])->firstOrFail();
            
            // Simpan data penanganan baru
            $penanganan = Penanganan::create([
                'kode_penanganan' => 'PEN-' . $rand_number,
                'kesehatan_id' => $result['kesehatan_id'],
                'judul_penanganan' => $result['judul_penanganan'],
                'catatan_penanganan' => $result['catatan_penanganan'] ?? null,
                'tanggal_penanganan' => $result['tanggal_penanganan'],
            ]);
            

            // Simpan item penanganan
                ItemPenanganan::create([
                    'penanganan_id' => $penanganan->id_penanganan,
                    'obat_id' => $result['obat_id'],
                    'jumlah_terpakai' => $result['jumlah_terpakai']
                ]);

                $obat = Pharmacy::where('id_obat', $result['obat_id'])->firstOrFail();

                // Update stok obat
                if ($obat->total_obat <= $result['jumlah_terpakai']) {
                    throw new \Exception("Stok obat {$obat->nama_obat} tidak cukup.");
                }

                $total_before_usage = $obat->total_obat;
                $packs_before_usage = intdiv($total_before_usage, $obat->isi_obat);

                // Kurangi total obat seperti biasa
                $obat->decrement('total_obat', $result['jumlah_terpakai']);
                $obat->refresh();

                $total_after_usage = $obat->total_obat;
                $packs_after_usage = intdiv($total_after_usage, $obat->isi_obat);

                $packs_consumed = $packs_before_usage - $packs_after_usage;

                if ($packs_consumed > 0) {
                    if ($obat->stock_obat < $packs_consumed) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Stock obat tidak cukup untuk dikurangi.',
                            'data' => null
                        ], 400);
                    }
                    $obat->decrement('stock_obat', $packs_consumed);
                }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Data penanganan berhasil ditambahkan.',
                'data' => $penanganan
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Data penanganan gagal ditambahkan.',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $penanganan = Penanganan::with('items')
        ->where('kode_penanganan', $id)
        ->first();

        try {
            if (!$penanganan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data penanganan tidak ditemukan.',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data penanganan berhasil di tampilkan',
                'data' => $penanganan,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data penanganan gagal di tampilkan.',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $penanganan = Penanganan::where('kode_penanganan', $id)->first();

        if (!$penanganan) {
            return response()->json([
                'success' => false,
                'message' => 'Data penanganan tidak ditemukan.',
                'data' => null
            ], 404);
        }

        try {
            $result = $request->validate([
                'kesehatan_id' => 'required|exists:kesehatan,id_kesehatan',
                'judul_penanganan' => 'required|string|max:255',
                'catatan_penanganan' => 'nullable|string',
                'tanggal_penanganan' => 'required|date',

                // Wajib item obat
                'obat_id' => 'required|string|exists:obat,id_obat',
                'jumlah_terpakai' => 'required|int|min:1',
            ]);

            $penanganan->update($result);
            return response()->json([
                'success' => true,
                'message' => 'Data penanganan berhasil di update.',
                'data' => $penanganan
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data penanganan gagal di update.',
                'data' => $th->getMessage()
            ], 500);
        }
    }
    
    public function destroy($id)
    {
        $penanganan = Penanganan::where('kode_penanganan', $id)->first();

        if (!$penanganan) {
            return response()->json([
                'success' => false,
                'message' => 'Data penanganan tidak ditemukan.',
                'data' => null
            ], 404);
        }

        try {
            $penanganan->delete();
            return response()->json([
                'success' => true,
                'message' => 'Data penanganan berhasil dihapus.',
                'data' => $penanganan
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Data penanganan gagal dihapus.',
                'data' => $th->getMessage()
            ], 500);
        }
    }
}
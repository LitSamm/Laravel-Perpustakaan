<?php

namespace App\Http\Controllers;

use App\Models\detailpeminjamanbuku;
use App\Models\pengembalianbuku;
use App\Models\peminjamanbuku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

class transaksicontroller extends Controller
{
    public function pinjambuku(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id_siswa' => 'required',
            'tanggal_pinjam' => 'required',
            'tanggal_kembali' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $save = peminjamanbuku::create([
            'id_siswa' => $req->input('id_siswa'),
            'tanggal_pinjam' => $req->input('tanggal_pinjam'),
            'tanggal_kembali' => $req->input('tanggal_kembali')
        ]);

        if ($save) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false]);
        }
    }

    public function tambahitem(Request $req, $id)
    {
        $validator = Validator::make($req->all(), [
            'id_buku' => 'required',
            'qty' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $save = peminjamanbuku::create([
            'id_peminjaman_buku' => $id,
            'id_buku' => $req->input('id_buku'),
            'qty' => $req->input('qty')
        ]);
        if ($save) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false]);
        }
    }
    public function mengembalikanBuku(Request $req, $id)
    {
        $validator = Validator::make($req->all(), [
            'id_peminjaman_buku' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $cek_kembali = pengembalianbuku::where('id_peminjaman_buku', $req->input('id_peminjaman_buku'))->count();

        if ($cek_kembali == 0) {
            $dt_kembali = peminjamanbuku::where('id', $req->input('id_peminjaman_buku'))->first();
            $tanggal_sekarang = Carbon::now()->format('Y-m-d');
            $tanggal_kembali = new Carbon($dt_kembali->tanggal_kembali);
            $dendaperhari = 1500;

            if (strtotime($tanggal_sekarang) > strtotime($tanggal_kembali)) {
                $jumlah_hari = $tanggal_kembali->diffInDays($tanggal_sekarang);
                $denda = $jumlah_hari * $dendaperhari;
            } else {
                $denda = 0;
            }

            $save = pengembalianbuku::create([
                'id_peminjaman_buku' => $req->input('id_peminjaman_buku'),
                'tanggal_pengembalian' => $tanggal_sekarang,
                'denda' => $denda,
            ]);

            if ($save) {
                $data['status'] = 1;
                $data['message'] = 'Berhasil dikembalikan';
            } else {
                $data['status'] = 0;
                $data['message'] = 'Pengembalian gagal';
            }
        } else {
            $data = [
                'status' => 0,
                'message' => 'Sudah pernah dikembalikan',
            ];
        }

        return response()->json($data);
    }
}

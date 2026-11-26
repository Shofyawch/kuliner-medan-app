<?php

namespace App\Controllers;

class Info extends BaseController
{
    public function faq()
    {
        return view('info/faq', [
            'judul'         => 'FAQ — Kuliner Khas Medan',
            'metaDeskripsi' => 'Pertanyaan yang sering diajukan seputar Kuliner Khas Medan, status halal, harga menu, ulasan, dan integrasi penginapan.',
        ]);
    }

    public function kontak()
    {
        return view('info/kontak', [
            'judul'         => 'Hubungi Kami — Kuliner Khas Medan',
            'metaDeskripsi' => 'Hubungi tim Kuliner Khas Medan melalui WhatsApp, email, atau formulir pesan untuk saran dan informasi restoran.',
        ]);
    }

    public function tentang()
    {
        return view('info/tentang', [
            'judul'         => 'Tentang Kami — Kuliner Khas Medan',
            'metaDeskripsi' => 'Kenali platform Kuliner Khas Medan — panduan digital tempat makan autentik di Kota Medan yang terintegrasi dengan layanan penginapan.',
        ]);
    }

    public function ketentuan()
    {
        return view('info/ketentuan', [
            'judul'         => 'Kebijakan & Ketentuan — Kuliner Khas Medan',
            'metaDeskripsi' => 'Kebijakan privasi, ketentuan penggunaan, serta pedoman ulasan pengunjung di Kuliner Khas Medan.',
        ]);
    }
}

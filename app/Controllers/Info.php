<?php

namespace App\Controllers;

class Info extends BaseController
{
    public function faq()
    {
        return view('info/faq', ['judul' => 'FAQ — Kuliner Khas Medan']);
    }

    public function kontak()
    {
        return view('info/kontak', ['judul' => 'Hubungi Kami — Kuliner Khas Medan']);
    }

    public function tentang()
    {
        return view('info/tentang', ['judul' => 'Tentang Kami — Kuliner Khas Medan']);
    }

    public function ketentuan()
    {
        return view('info/ketentuan', ['judul' => 'Kebijakan & Ketentuan — Kuliner Khas Medan']);
    }
}

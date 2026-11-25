<?php

namespace App\Libraries;

use CodeIgniter\Debug\ExceptionHandlerInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

/**
 * Galat tak terduga pada alamat /api/ dibalas JSON ringkas.
 * Rincian teknis hanya masuk ke log (writable/logs). Pada mode production
 * tidak ada rincian apa pun yang dikirim ke klien.
 */
class ApiExceptionHandler implements ExceptionHandlerInterface
{
    public function handle(
        Throwable $exception,
        RequestInterface $request,
        ResponseInterface $response,
        int $statusCode,
        int $exitCode,
    ): void {
        log_message('error', '[API] ' . get_class($exception) . ': ' . $exception->getMessage());

        $kode  = ($statusCode >= 400 && $statusCode < 600) ? $statusCode : 500;
        $pesan = match (true) {
            $kode === 404 => 'Alamat API tidak ditemukan.',
            $kode >= 500  => 'Terjadi kesalahan pada server.',
            default       => 'Permintaan tidak dapat diproses.',
        };

        $body = ['status' => 'error', 'message' => $pesan];
        if (ENVIRONMENT !== 'production') {
            $body['debug'] = ['jenis' => get_class($exception), 'pesan' => $exception->getMessage()];
        }

        $response->setStatusCode($kode)->setJSON($body)->send();

        if (ENVIRONMENT !== 'testing') {
            exit($exitCode);
        }
    }
}

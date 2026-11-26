<?php

namespace App\Controllers\Api\V1;

use App\Controllers\BaseController;

/**
 * Riwayat pertukaran data antar sistem (tabel log_integrasi) khusus admin.
 *  GET /api/v1/log-integrasi   daftar log (opsional ?arah=masuk|keluar)
 */
class LogIntegrasiApi extends BaseController
{
    use ResponsApi;

    public function index()
    {
        $arah    = $this->request->getGet('arah');
        $perPage = min(max((int) ($this->request->getGet('per_page') ?: 20), 1), 50);
        $page    = max((int) ($this->request->getGet('page') ?: 1), 1);

        $db = db_connect();
        try {
            $countBuilder = $db->table('log_integrasi');
            $dataBuilder  = $db->table('log_integrasi');

            if (in_array($arah, ['masuk', 'keluar'], true)) {
                $countBuilder->where('arah', $arah);
                $dataBuilder->where('arah', $arah);
            }

            $total = $countBuilder->countAllResults();
            $rows  = $dataBuilder
                ->orderBy('created_at', 'DESC')
                ->orderBy('id', 'DESC')
                ->limit($perPage, ($page - 1) * $perPage)
                ->get()->getResultArray();
        } catch (\Throwable $e) {
            return $this->galatDb($e);
        }

        $data = array_map(static fn (array $r): array => [
            'id'          => (int) $r['id'],
            'arah'        => $r['arah'],
            'endpoint'    => $r['endpoint'],
            'metode'      => $r['metode'],
            'parameter'   => $r['parameter'],
            'status_code' => $r['status_code'] !== null ? (int) $r['status_code'] : null,
            'jumlah_data' => $r['jumlah_data'] !== null ? (int) $r['jumlah_data'] : null,
            'durasi_ms'   => $r['durasi_ms'] !== null ? (int) $r['durasi_ms'] : null,
            'keterangan'  => $r['keterangan'],
            'dibuat'      => $r['created_at'],
        ], $rows);

        return $this->sukses($data, [
            'page'        => $page,
            'per_page'    => $perPage,
            'total'       => $total,
            'total_pages' => max(1, (int) ceil($total / $perPage)),
        ]);
    }
}

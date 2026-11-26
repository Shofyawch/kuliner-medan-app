<?php

namespace App\Libraries;

use Throwable;

class HotelService
{
    private string $baseUrl;
    private int $timeout;

    public function __construct()
    {
        $url = env('HOTEL_API_URL', 'https://snooze-study-gravy.ngrok-free.dev/api');
        $this->baseUrl = rtrim((string) $url, '/');
        $this->timeout = 6;
    }

    /**
     * Helper aman untuk membaca cache
     */
    private function getCache(string $key): mixed
    {
        try {
            if (function_exists('cache')) {
                return cache($key);
            }
        } catch (Throwable) {}
        return null;
    }

    /**
     * Helper aman untuk menyimpan cache
     */
    private function setCache(string $key, mixed $value, int $ttl = 300): void
    {
        try {
            if (function_exists('cache')) {
                $c = cache();
                if ($c && is_object($c) && method_exists($c, 'save')) {
                    $c->save($key, $value, $ttl);
                }
            }
        } catch (Throwable) {}
    }

    /**
     * Helper aman untuk menghapus cache
     */
    private function deleteCache(string $key): void
    {
        try {
            if (function_exists('cache')) {
                $c = cache();
                if ($c && is_object($c) && method_exists($c, 'delete')) {
                    $c->delete($key);
                }
            }
        } catch (Throwable) {}
    }
    private function request(string $endpoint, string $method = 'GET', array $params = [], ?array $body = null): ?array
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        if ($method === 'GET' && !empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        try {
            $client = \Config\Services::curlrequest([
                'timeout'     => $this->timeout,
                'headers'     => [
                    'User-Agent'                 => 'KulinerMedanApp/1.0',
                    'ngrok-skip-browser-warning' => '1',
                    'Accept'                     => 'application/json',
                    'Content-Type'               => 'application/json',
                ],
                'http_errors' => false,
                'verify'      => (bool) env('HOTEL_API_VERIFY', true),
            ]);

            $options = [];
            if ($body !== null && in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
                $options['json'] = $body;
            }

            $response = $client->request($method, $url, $options);
            $statusCode = $response->getStatusCode();
            $content = $response->getBody();

            if ($statusCode >= 200 && $statusCode < 300) {
                $data = json_decode($content, true);
                return is_array($data) ? $data : null;
            }

            log_message('error', "Hotel API [{$method} {$url}] status {$statusCode}: " . substr($content, 0, 300));
            return null;
        } catch (Throwable $e) {
            log_message('error', "Hotel API connection error [{$url}]: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Ambil daftar hotel dengan pagination & filter
     */
    public function getHotels(array $filter = [], bool $useCache = true): array
    {
        $cacheKey = 'hotel_list_' . md5(json_encode($filter));
        if ($useCache) {
            $cached = $this->getCache($cacheKey);
            if (is_array($cached)) {
                return $cached;
            }
        }

        $res = $this->request('hotels', 'GET', $filter);
        if ($res && isset($res['data'])) {
            if ($useCache) {
                $this->setCache($cacheKey, $res, 300); // cache 5 menit
            }
            return $res;
        }

        return [
            'status' => 'error',
            'data'   => [],
            'meta'   => ['page' => 1, 'limit' => 20, 'total' => 0, 'total_pages' => 0],
        ];
    }

    /**
     * Ambil detail satu hotel berdasarkan ID
     */
    public function getHotelById(int $id, bool $useCache = true): ?array
    {
        $cacheKey = 'hotel_detail_' . $id;
        if ($useCache) {
            $cached = $this->getCache($cacheKey);
            if (is_array($cached)) {
                return $cached;
            }
        }

        $res = $this->request("hotels/{$id}");
        if ($res && isset($res['data'])) {
            if ($useCache) {
                $this->setCache($cacheKey, $res['data'], 300);
            }
            return $res['data'];
        }

        return null;
    }

    /**
     * Ambil daftar seluruh kecamatan dan jumlah hotel
     */
    public function getKecamatan(bool $useCache = true): array
    {
        $cacheKey = 'hotel_kecamatan_list';
        if ($useCache) {
            $cached = $this->getCache($cacheKey);
            if (is_array($cached)) {
                return $cached;
            }
        }

        $res = $this->request('kecamatan');
        if ($res && isset($res['data']) && is_array($res['data'])) {
            if ($useCache) {
                $this->setCache($cacheKey, $res['data'], 600);
            }
            return $res['data'];
        }

        return [];
    }

    /**
     * Ambil hotel terdekat dari koordinat GPS (Haversine formula)
     */
    public function getHotelsNear(float $lat, float $lng, ?string $kecamatan = null, float $maxRadiusKm = 10.0, int $limit = 4): array
    {
        $params = ['limit' => 100];
        if (!empty($kecamatan)) {
            $params['kecamatan'] = $kecamatan;
        }

        $res = $this->getHotels($params, true);
        $hotels = $res['data'] ?? [];

        // Bila hasil di kecamatan sedikit, ambil hotel dari seluruh kota untuk perbandingan jarak
        if (count($hotels) < $limit) {
            $resAll = $this->getHotels(['limit' => 100], true);
            $hotels = $resAll['data'] ?? [];
        }

        $hasil = [];
        foreach ($hotels as $h) {
            if (!isset($h['latitude'], $h['longitude']) || !is_numeric($h['latitude']) || !is_numeric($h['longitude'])) {
                continue;
            }
            $hLat = (float) $h['latitude'];
            $hLng = (float) $h['longitude'];

            // Haversine formula
            $dLat = deg2rad($hLat - $lat);
            $dLng = deg2rad($hLng - $lng);
            $a = sin($dLat / 2) * sin($dLat / 2) +
                 cos(deg2rad($lat)) * cos(deg2rad($hLat)) *
                 sin($dLng / 2) * sin($dLng / 2);
            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
            $jarakKm = 6371.0 * $c;

            if ($jarakKm <= $maxRadiusKm) {
                $h['jarak_km'] = round($jarakKm, 2);
                $hasil[] = $h;
            }
        }

        usort($hasil, static fn ($a, $b) => $a['jarak_km'] <=> $b['jarak_km']);

        return array_slice($hasil, 0, $limit);
    }

    /**
     * Ambil ulasan ulasan suatu hotel
     */
    public function getReviews(int $hotelId): array
    {
        $res = $this->request('reviews', 'GET', ['hotel_id' => $hotelId]);
        return $res['data'] ?? [];
    }

    /**
     * Kirim ulasan baru ke server Singgah Medan
     */
    public function createReview(int $hotelId, int $rating, string $judul, string $isi, int $userId): ?array
    {
        $res = $this->request('reviews', 'POST', [], [
            'hotel_id' => $hotelId,
            'user_id'  => $userId,
            'rating'   => $rating,
            'judul'    => $judul,
            'isi'      => $isi,
        ]);

        if ($res && ($res['status'] ?? '') === 'ok') {
            $this->deleteCache('hotel_detail_' . $hotelId);
            return $res;
        }

        return null;
    }

    /**
     * Hapus ulasan dari server Singgah Medan
     */
    public function deleteReview(int $reviewId, int $hotelId): ?array
    {
        $res = $this->request("reviews/{$reviewId}", 'DELETE');
        if ($res && ($res['status'] ?? '') === 'ok') {
            $this->deleteCache('hotel_detail_' . $hotelId);
            return $res;
        }
        return null;
    }
}

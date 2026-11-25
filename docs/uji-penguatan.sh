#!/usr/bin/env bash
# Uji rate limit, CORS, dan galat JSON. Pakai: bash docs/uji-penguatan.sh
HOST="${HOST:-http://localhost:8080}"
BASE="$HOST/api/v1"

echo "1) Alamat API yang tidak ada -> harus 404 berbentuk JSON"
curl -s -w "\n   -> HTTP %{http_code}\n" "$BASE/tidak-ada"

echo "2) CORS: origin yang diizinkan -> harus 204 dan ada Access-Control-Allow-Origin"
curl -s -o /dev/null -D - -X OPTIONS "$BASE/restoran" \
  -H "Origin: http://localhost:8080" -H "Access-Control-Request-Method: POST" \
  -H "Access-Control-Request-Headers: authorization,content-type" | grep -i -E "^HTTP|access-control-allow-(origin|methods|headers)"

echo "3) CORS: origin asing -> header Allow-Origin TIDAK boleh berisi origin asing"
curl -s -o /dev/null -D - -X OPTIONS "$BASE/restoran" \
  -H "Origin: https://situs-jahat.example" -H "Access-Control-Request-Method: POST" | grep -i -E "^HTTP|access-control-allow-origin" || echo "   (tidak ada header Allow-Origin, benar)"
echo "   Catatan: bila muncul http://localhost:8080 itu origin yang diizinkan, bukan origin asing,"
echo "   jadi browser akan menolak permintaan dari situs-jahat.example."

echo "4) Rate limit login: 30 percobaan password salah berturut-turut"
for i in $(seq 1 30); do
  curl -s -o /dev/null -w "%{http_code} " -X POST "$BASE/auth/login" -H 'Content-Type: application/json' -d '{"username":"admin","password":"tebakan"}'
done
echo
echo "   Harus ada deretan 401 lalu berubah menjadi 429 (Terlalu banyak permintaan)."
echo "   Tunggu sekitar satu menit sebelum menjalankan skrip uji lain."

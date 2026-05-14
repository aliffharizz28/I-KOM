<x-mail::message>
# Tugasan Baru Ditambah!

Salam sejahtera,

Sebuah tugasan baharu telah ditambah iaitu **{{ $tugasan->fld_tgs_nama }}**.

**Penerangan Tugasan:**  
{{ $tugasan->fld_tgs_desc }}

**Tarikh Tutup:**  
{{ \Carbon\Carbon::parse($tugasan->fld_tgs_tarikh)->format('d M Y') }}

Sila pastikan anda menyelesaikan dan menghantar tugasan ini ke dalam sistem mengikut masa yang telah ditetapkan.

<x-mail::button :url="config('app.url') . '/login'">
Log Masuk ke Sistem
</x-mail::button>

Terima kasih,<br>
Sistem Pengurusan I-KOM
</x-mail::message>
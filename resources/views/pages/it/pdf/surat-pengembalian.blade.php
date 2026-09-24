<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Pengembalian Aset</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 1.5; color: #000; }
        .text-center { text-align: center; }
        .text-justify { text-align: justify; }
        .font-bold { font-weight: bold; }
        .mb-4 { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        td, th { vertical-align: top; padding: 6px; }
        .border-table th, .border-table td { border: 1px solid #000; }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('images/logo/kreston-logo.png');
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        // Logika Deteksi Aset
        $typeName = strtolower($asset->type->name ?? '');
        $catName = strtolower($asset->type->category->name ?? '');
        
        $isMouseOrKeyboard = str_contains($typeName, 'mouse') || str_contains($typeName, 'keyboard') || str_contains($typeName, 'flashdisk');
        $isLaptop = !$isMouseOrKeyboard && (str_contains($typeName, 'laptop') || str_contains($typeName, 'notebook') || $typeName === 'pc' || $catName === 'laptop');
    @endphp

    <!-- Kop Surat -->
    <table style="width:100%; border-collapse:collapse; margin-bottom:0;">
        <tr>
            <td style="width:35%; vertical-align:middle; padding:0; border:none;">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Kreston Indonesia" style="height:70px;">
                @else
                    <strong style="font-size:15px; color:#009EDB;">Kreston Indonesia</strong>
                @endif
            </td>
            <td style="width:65%; vertical-align:middle; text-align:right; padding:0; border:none;">
                <p style="margin:0; font-size:19px; font-weight:bold; color:#009EDB; text-transform:uppercase; letter-spacing:1px; line-height:1.25;">
                    Hendrawinata Hanny<br>Erwin &amp; Sumargo
                </p>
                <p style="margin:6px 0 0 0; font-size:11px; color:#333333; line-height:1.5;">
                    Intiland Tower 18th floor, Jl. Jend. Sudirman Kav. 32, Jakarta 10220, Indonesia<br>
                    T : 62-21 571 2000, 570 7997 | F : 62-21 570 6118, 571 1818<br>
                    Email : hhes.jakarta@kreston.co.id | www.kreston.co.id
                </p>
            </td>
        </tr>
    </table>
    <div style="border-bottom: 2px solid #000000; margin: 12px 0 16px 0;"></div>

    <!-- Judul Surat -->
    <div style="text-align:center; margin:0 0 16px 0;">
        <h2 style="margin:0 0 1.5px 0; font-size:16px; font-weight:bold; text-transform:uppercase; text-decoration:underline;">
            Surat Pengembalian Aset
        </h2>
        <p style="margin:0; font-size:14px; font-weight:bold; color:#111;">
            No. {{ $document_number ?? '001/IT/SP-KMB/VIII/2026' }}
        </p>
    </div>

    <!-- Paragraf Pembuka Dinamis -->
    <p class="text-justify mb-4">
        Pada tanggal <strong>{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</strong>, telah dikembalikan 
        @if($isLaptop)
            1 set Laptop (Notebook) 
        @else
            1 unit {{ ucwords($typeName) }} 
        @endif
        <strong>{{ $asset->asset_code ?? $asset->hostname }}</strong> ke department IT KAP Hendrawinata Hanny Erwin & Sumargo, dari <strong>{{ $employee->first_name }} {{ $employee->last_name }}</strong>.
    </p>

    <p class="mb-2">Dengan rincian kondisi fisik terakhir sebagai berikut:</p>

    <!-- Tabel Kondisi Dinamis -->
    <table class="border-table mb-4">
        <thead>
            <tr style="background-color: #f3f4f6;">
                <th style="width: 40%; text-align: left;">Bagian / Kelengkapan</th>
                <th style="width: 60%; text-align: left;">Kondisi</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Kebersihan</td>
                <td style="text-transform: capitalize;">{{ $kondisi['kebersihan'] ?? 'Baik' }}</td>
            </tr>
            <tr>
                <td>Fisik @if($isLaptop) Laptop @else Aset @endif</td>
                <td style="text-transform: capitalize;">{{ $kondisi['fisik'] ?? 'Baik' }}</td>
            </tr>
            
            <!-- Elemen ini hanya muncul jika barang adalah Laptop -->
            @if($isLaptop)
            <tr>
                <td>Charger</td>
                <td style="text-transform: capitalize;">{{ $kondisi['charger'] ?? 'Baik' }}</td>
            </tr>
            <tr>
                <td>Baterai</td>
                <td style="text-transform: capitalize;">{{ $kondisi['baterai'] ?? 'Baik' }}</td>
            </tr>
            <tr>
                <td>Tas Laptop</td>
                <td style="text-transform: capitalize;">{{ $kondisi['tas'] ?? 'Baik' }}</td>
            </tr>
            @endif
            
            <tr>
                <td>Keterangan Tambahan / Alasan</td>
                <td>{{ $kondisi['lainnya'] ?: '-' }}</td>
            </tr>
        </tbody>
    </table>
    
    <p class="text-justify">Demikian berita acara pengembalian aset ini dibuat dengan sebenarnya agar dapat dipergunakan sebagaimana mestinya.</p>

    <!-- Tanda Tangan -->
    <table style="margin-top: 40px; text-align: center; border:none;">
        <tr>
            <td style="width: 50%; border:none;"></td>
            <td style="width: 50%; border:none;">Jakarta, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td style="border:none;">Dikembalikan oleh,</td>
            <td style="border:none;">Diterima oleh,</td>
        </tr>
        <tr>
            <td style="padding-top: 80px; border:none;">
                <strong><u>{{ $employee->first_name }} {{ $employee->last_name }}</u></strong><br>
                {{ $employee->position ?? 'Karyawan' }}
            </td>
            <td style="padding-top: 80px; border:none;">
                <strong><u>{{ Auth::user()->name ?? 'Michael Willieson' }}</u></strong><br>
                IT Specialist
            </td>
        </tr>
    </table>
</body>
</html>
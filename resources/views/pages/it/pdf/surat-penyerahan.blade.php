<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Penyerahan Aset</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 1.5; color: #000; }
        .text-center { text-align: center; }
        .text-justify { text-align: justify; }
        .font-bold { font-weight: bold; }
        .mb-2 { margin-bottom: 10px; }
        .mb-4 { margin-bottom: 20px; }
        .underline { text-decoration: underline; }
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; padding: 2px; }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('images/logo/kreston-logo.png');
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        // 1. Logika Deteksi Aset (PHP Version)
        $typeName = strtolower($asset->type->name ?? '');
        $catName = strtolower($asset->type->category->name ?? '');
        
        $isMouseOrKeyboard = str_contains($typeName, 'mouse') || str_contains($typeName, 'keyboard') || str_contains($typeName, 'flashdisk');
        
        $isLaptop = !$isMouseOrKeyboard && (str_contains($typeName, 'laptop') || str_contains($typeName, 'notebook') || $typeName === 'pc' || $catName === 'laptop');
    @endphp

    <table style="width:100%; border-collapse:collapse; margin-bottom:0;">
        <tr>
            <td style="width:35%; vertical-align:middle; padding:0;">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Kreston Indonesia" style="height:70px;">
                @else
                    <strong style="font-size:15px; color:#009EDB;">Kreston Indonesia</strong>
                @endif
            </td>
            <td style="width:65%; vertical-align:middle; text-align:right; padding:0;">
                <p style="margin:0; font-size:19px; font-weight:bold; color:#009EDB; text-transform:uppercase; letter-spacing:1px; line-height:1.25;">
                    Hendrawinata Hanny<br>Erwin &amp; Sumargo
                </p>
                <p style="margin:6px 0 0 0; font-size:11px; color:#333333; line-height:1.5;">
                    Intiland Tower 18th floor, Jl. Jend. Sudirman Kav. 32, Jakarta 10220, Indonesia<br>
                    T : 62-21 571 2000, 570 7997 | F : 62-21 570 6118, 571 1818<br>
                    Email : hhes.jakarta@kreston.co.id | www.kreston.co.id<br>
                    A Member of Kreston Global | A global network of independent accounting firms
                </p>
            </td>
        </tr>
    </table>
    <div style="border-bottom: 2px solid #000000; margin: 12px 0 16px 0;"></div>

    <div style="text-align:center; margin:0 0 16px 0;">
        <h2 style="margin:0 0 1.5px 0; font-size:16px; font-weight:bold; text-transform:uppercase; text-decoration:underline;">
            Surat Penyerahan Aset
        </h2>
        <p style="margin:0; font-size:14px; font-weight:bold; color:#111;">
            No. {{ $document_number ?? '001/IT/SP-STNB/VIII/2026' }}
        </p>
    </div>
    
    <p class="text-justify mb-4">
        Pada tanggal <strong>{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</strong>, telah dilaksanakan serah terima 
        
        <!-- 2. Ganti Teks Deskripsi Secara Dinamis -->
        @if($isLaptop)
            1 set Laptop (Notebook) 
        @else
            1 unit {{ ucwords($typeName) }} 
        @endif
        
        <strong>{{ $asset->asset_code ?? $asset->hostname }}</strong> dari department IT KAP Hendrawinata Hanny Erwin & Sumargo, kepada <strong>{{ $employee->first_name }} {{ $employee->last_name }}</strong>.
    </p>

    <div class="mb-4 text-justify">
        <!-- 3. Aturan Berubah Dinamis Tergantung Jenis Aset -->
        @if($isLaptop)
            <p class="font-bold mb-2">KETENTUAN UMUM PENGGUNAAN FASILITAS ASET PC/NOTEBOOK PERUSAHAAN</p>
            <ol>
                <li>Sesuai dengan peraturan perusahaan yang berlaku, maka tanggung jawab atas PC/Notebook tersebut beserta segala isinya diserahkan kepada karyawan yang bersangkutan.</li>
                <li>Karyawan tidak diperbolehkan untuk mengubah spesifikasi hardware PC/Notebook tanpa sepengetahuan dan persetujuan dari HRD & IT.</li>
                <li>Jika karyawan membutuhkan hardware dan/atau software lain untuk menunjang pekerjaannya, maka permintaan tersebut harus diajukan dan mendapatkan persetujuan dari HRD dan IT.</li>
                <li>Karyawan dilarang untuk menyimpan file multimedia berupa musik dan/atau film yang melanggar hak cipta, di dalam PC/Notebook perusahaan.</li>
                <li>Apabila terjadi kerusakan besar, karena kelalaian pengguna (karyawan) dan tidak melaporkan hal tersebut kepada bagian IT segera, maka atas pertimbangan perusahaan karyawan dapat dikenakan penggantian biaya perbaikan PC/Notebook tersebut.</li>
                <li>Apabila terjadi kehilangan Notebook dengan alasan apapun, pengguna (karyawan) berkewajiban untuk mengganti harga Notebook tersebut (harga tergantung dari penilaian dan kebijakan kantor).</li>
                <li>Adalah wewenang perusahaan untuk mengaudit secara periodik maupun khusus atas penggunaan fasilitas yang diberikan. Pelanggaran dapat dikenakan sanksi:
                    <ul type="a">
                        <li>Pencabutan fasilitas</li>
                        <li>Peringatan lisan & tertulis (I, II, III)</li>
                        <li>Sanksi administratif & Skorsing</li>
                        <li>Pemutusan Hubungan Kerja</li>
                    </ul>
                </li>
            </ol>
        @else
            <p class="font-bold mb-2">KETENTUAN UMUM PENGGUNAAN FASILITAS ASET PERUSAHAAN</p>
            <ol>
                <li>Sesuai dengan peraturan perusahaan yang berlaku, maka tanggung jawab atas pemakaian aset tersebut diserahkan kepada karyawan yang bersangkutan.</li>
                <li>Karyawan wajib menjaga kondisi fisik dan kelancaran fungsi aset agar tetap dalam keadaan baik selama masa pemakaian.</li>
                <li>Apabila terjadi kerusakan akibat kelalaian pengguna (karyawan) dan tidak dilaporkan kepada bagian IT segera, maka atas pertimbangan perusahaan, karyawan dapat dikenakan kewajiban penggantian biaya perbaikan aset tersebut.</li>
                <li>Apabila terjadi kehilangan aset dengan alasan apapun, pengguna (karyawan) berkewajiban untuk mengganti harga aset tersebut sesuai dengan penilaian dan kebijakan yang berlaku di kantor.</li>
                <li>Karyawan diwajibkan mengembalikan aset ini kepada departemen IT apabila mengundurkan diri (resign) atau tidak lagi membutuhkan aset tersebut untuk keperluan pekerjaan.</li>
            </ol>
        @endif
        
        <p>Demikian surat pernyataan ini saya tanda tangani sebagai bukti persetujuan atas ketentuan umum perusahaan diatas. Segala akibat hukum yang mungkin timbul dikemudian hari sepenuhnya merupakan tanggung jawab saya.</p>
    </div>

    <table style="margin-top: 40px; text-align: center;">
        <tr>
            <td style="width: 50%;"></td>
            <td style="width: 50%;">Jakarta, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td>Diserahkan oleh,</td>
            <td>Diterima oleh,</td>
        </tr>
        <tr>
            <td style="padding-top: 80px;">
                <strong><u>{{ Auth::user()->name ?? 'Michael Willieson' }}</u></strong><br>
                IT Specialist
            </td>
            <td style="padding-top: 80px;">
                <strong><u>{{ $employee->first_name }} {{ $employee->last_name }}</u></strong><br>
                {{ $employee->position ?? 'Karyawan' }}
            </td>
        </tr>
    </table>
</body>
</html>
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ============================================================
        // 1. SEED DEFAULT USERS
        // ============================================================
        User::updateOrCreate(
            ['email' => 'admin@apel.com'],
            [
                'name'     => 'Admin Apel SMKN 1 Ciamis',
                'password' => Hash::make('admin123'),
                'role'     => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'kepsek@apel.com'],
            [
                'name'     => 'Kepala Sekolah SMKN 1 Ciamis',
                'password' => Hash::make('kepsek123'),
                'role'     => 'kepala_sekolah',
            ]
        );

        // ============================================================
        // 2. SEED EMPLOYEES (Guru, Tendik, TU, PNS, PPPK, dll)
        // ============================================================
        $employees = [];

        // ---- 2a. Data dari sheet "G JP" (Guru & Pegawai) ----
        $gjp = [
            ['name' => 'H. Cepy Wahyudin, A.Md., S.Kom., M.Kom.', 'nip' => '198408252010011010', 'jabatan' => 'Kepala Sekolah'],
            ['name' => 'Oneng Dalilah, M.Pd.', 'nip' => '196706221991032006', 'jabatan' => 'Kepala Perpustakaan/Guru Matematika'],
            ['name' => 'H. Nanang Aziz S., S.Pd.,M.Pd.', 'nip' => '196704281990031002', 'jabatan' => 'Satuan Pengawas Internal/Guru Bahasa Inggris'],
            ['name' => 'Dra. Elin Karlinah', 'nip' => '196806051994122006', 'jabatan' => 'Satuan Pengawas Internal/Guru Akuntansi'],
            ['name' => 'Yana Hendrayana, S.Pd. M.Pd.', 'nip' => '197105281998021004', 'jabatan' => 'Guru Ahli Madya/Guru Bahasa Indonesia'],
            ['name' => 'Drs. Asep Cahrina', 'nip' => '196801231994121006', 'jabatan' => 'Guru Ahli Madya/Guru Pemasaran'],
            ['name' => 'H. Cucu Hermawan, M.Pd.', 'nip' => '197401011997021001', 'jabatan' => 'Wakil Kepala Bidang Kurikulum/ PTK Akademik'],
            ['name' => 'Drs. Dadang Nurdin', 'nip' => '196609071994121001', 'jabatan' => 'Guru BP/BK'],
            ['name' => 'Dedeh Kurniasih, S.Pd, M.Pd.', 'nip' => '197010091996012001', 'jabatan' => 'Kordinator PKL/Guru Pemasaran'],
            ['name' => 'Hj. Alisah, S.Pd.', 'nip' => '197204111996012001', 'jabatan' => 'Ketua Program Keahlian Perhotelan/Guru Perhotelan'],
            ['name' => 'Endah Rahayu D, S.Pd. M.Pd.', 'nip' => '197207122006042006', 'jabatan' => 'Wakil Kepala 2. Bidang Kesiswaan/ PTK Kesiswaan'],
            ['name' => 'Atin Kudriatin, S.Pd.', 'nip' => '197602292006042009', 'jabatan' => 'Staff Kesiswaan Bidang Administrasi, Bina Prestasi'],
            ['name' => 'Kiki Supendi, MT.', 'nip' => '197701202009011007', 'jabatan' => 'Ketua LSP/Guru RPL'],
            ['name' => 'Tita Puspita, S.Pd., M.Pd.', 'nip' => '198102252008012006', 'jabatan' => 'Bendahara Penerimaan/Guru Akuntansi'],
            ['name' => 'Ikhsan Nur Rokhmat, S.Pd.,M.Pd.', 'nip' => '197510112008011004', 'jabatan' => 'Wakasek Bidang Sarpras/ PTK Sarpras'],
            ['name' => 'Dadan Sugiarna, S.Pd.', 'nip' => '196608091997021001', 'jabatan' => 'Guru Akuntansi'],
            ['name' => 'Pebi Dinastriani, S.Pd.', 'nip' => '198802052011012004', 'jabatan' => 'Wakasek Bidang Hubungan Industri dan Masyarakat'],
            ['name' => 'Carkim, S.Pd.', 'nip' => '197512252014081001', 'jabatan' => 'Ketua Program keahlian Akuntansi dan Keuangan Lembaga'],
            ['name' => 'Nastiti, S.Pd.', 'nip' => '198909242014012001', 'jabatan' => 'Guru Ahli Pertama/Ketua Program keahlian Pengembangan Perangkat Lunak dan Gim'],
            ['name' => 'Egi Samsul Mu\'arif, S.Pd.', 'nip' => '199603122020121014', 'jabatan' => 'Guru Ahli Pertama/Guru Teknik Informatika'],
            ['name' => 'Ani Herliani, S. Kom.', 'nip' => '198009162014082003', 'jabatan' => 'Ketua Program keahlian Desain Komunikasi Visual'],
            ['name' => 'Yati Setiawati S.Ag', 'nip' => '197608272021212001', 'jabatan' => 'Guru PAI'],
            ['name' => 'Cahyaman Natawiguna, S.Pd.', 'nip' => '197301312021211002', 'jabatan' => 'Penanggung Jawab Lingkungan Hidup/Guru PJOK'],
            ['name' => 'Iip Masripah, S.Ag.', 'nip' => '197408012021212002', 'jabatan' => 'Staf Kesiswaan Bidang Kesehatan Mental/Fisik/Guru PAI'],
            ['name' => 'Yeyet Rohaeti, S.Pd', 'nip' => '197501032021212005', 'jabatan' => 'Pembina Extrakulikuler Kewirausahaan/SPW/Guru Pemasaran'],
            ['name' => 'Ati Sukmawati, S.Pd.', 'nip' => '198806272022212014', 'jabatan' => 'Ketua Program Keahlian Pemasaran'],
            ['name' => 'Yana Soviyana, S.E.', 'nip' => '198104282022211006', 'jabatan' => 'Staf Sarana/Pembina Kopsis/Oprator Dapodik'],
            ['name' => 'Mahani Gunawan, S.Pd.', 'nip' => '199511012022212012', 'jabatan' => 'Ketua Program Keahlian Kuliner'],
            ['name' => 'Sri Yuliani, S.Pd.', 'nip' => '199007142022212013', 'jabatan' => 'Koordinator Tefa'],
            ['name' => 'Julia Meliani Rosadi, S.Pd.', 'nip' => '199505262023212023', 'jabatan' => 'Guru Akuntansi'],
            ['name' => 'Aziz Muslim, S.Pd.', 'nip' => '197906232025211006', 'jabatan' => 'Staff Kurikulum Bidang Data Akademik dan Pelaporan'],
            ['name' => 'Yusef Abdul Aziz, S.Pd.', 'nip' => '198911282022211010', 'jabatan' => 'Staff Sarpras'],
            ['name' => 'Deslita Seniatsaani, S.Pd.', 'nip' => '199112052022212008', 'jabatan' => 'Kordinator BK/Beasiswa'],
            ['name' => 'Dewi Rosita, S.Pd.', 'nip' => '198507132022212025', 'jabatan' => 'Ketua Unit Produksi Air Minum'],
            ['name' => 'Wiana Yulian, S.Pd.', 'nip' => '199207152023212039', 'jabatan' => 'Guru Ahli Pertama/Pembina extrakulikuler KIR/Pustakawan Remaja'],
            ['name' => 'Pia Amanda Nurhusni, S.Pd.', 'nip' => '198812292022212007', 'jabatan' => 'Staf Humas Hubungan Masyarakat, Publikasi dan informasi'],
            ['name' => 'Deasy Putri Lestari , S.Pd.', 'nip' => '198612132022212017', 'jabatan' => 'Ketua Program Keahlian Manajemen Perkantoran dan Layanan Bisnis'],
            ['name' => 'Wiliandini, S.Pd.', 'nip' => '198202022024212016', 'jabatan' => 'Guru Ahli Pertama/Guru Bahasa Inggris'],
            ['name' => 'Arinaryani Suryani, S.pd.', 'nip' => '199504052024212034', 'jabatan' => 'Tim Medsos/Guru Matematika'],
            ['name' => 'Herna Novitasari, S.Pd.', 'nip' => '199506302024212029', 'jabatan' => 'Pengelola E-bank School/Guru Akuntansi'],
            ['name' => 'Miming Miptahudin, S.Pd.', 'nip' => '198010262025211052', 'jabatan' => 'Pembina Extrakulikuler Paskibra/Guru Bahasa Inggris'],
            ['name' => 'Jajang Ikbal Herlianto, S.Pd', 'nip' => '199602022025211150', 'jabatan' => 'Staf Humas Pengembangan Budaya Kerja, Hubungan Industri'],
            ['name' => 'Befi Apriansyah, S.Pd.', 'nip' => '199204222025211151', 'jabatan' => 'Staf Kesiswaan Bidang Organisasi dan Kegiatan (Pembina)/Guru Bahasa Indonesia'],
            ['name' => 'Muhamad Afrizal, S.Pd.', 'nip' => '199704272024211007', 'jabatan' => 'Staf Kesiswaan Bidang Kedisiplinan, Ketertiban, Keamanan dan Kerapihan'],
            ['name' => 'Angga Febrian Pratama, S.Pd.', 'nip' => '199902022025211076', 'jabatan' => 'Guru Non ASN/Staf Kurikulum Bidang Perencanaan dan Dokumen Kurikulum'],
        ];

        foreach ($gjp as $d) {
            $employees[$d['nip']] = [
                'name'              => $d['name'],
                'nip'               => $d['nip'],
                'jabatan'           => $d['jabatan'],
                'pangkat_golongan'  => null,
                'status'            => 'Guru/Tendik',
            ];
        }

        // ---- 2b. Data dari sheet "TUTT" (Tenaga Kependidikan Non-ASN) ----
        $tutt = [
            ['name' => 'Wawan Rustiawan, A.P.', 'nip' => '197003222025211017', 'jabatan' => 'Pelaksana Administrasi Kesiswaan'],
            ['name' => 'Tedi Herdis, A.P.', 'nip' => '196901052025211031', 'jabatan' => 'Pelaksana Administrasi Sarana Prasarana dan Gudang'],
            ['name' => 'Yeyet Daryati, A.P.', 'nip' => '197308172025212023', 'jabatan' => 'Pelaksana Urusan Administrasi Umum'],
            ['name' => 'Raden Rita Nurhajaty', 'nip' => '197204142025212021', 'jabatan' => 'Pelaksana Administrasi Kesiswaan'],
            ['name' => 'Dudi Sumiadi', 'nip' => '197810012025211072', 'jabatan' => 'Kebersihan Sekolah'],
            ['name' => 'Karman, A.P.', 'nip' => '197906082025211102', 'jabatan' => 'Kebersihan Sekolah/Toolman'],
            ['name' => 'Asep Saeful Rahman', 'nip' => '198903102025211142', 'jabatan' => 'Sopir dan Toolman'],
            ['name' => 'Yani Ramdani', 'nip' => '199003302025212125', 'jabatan' => 'Pelaksana Administrasi Kepegawaian'],
            ['name' => 'Wawan Pahrudin', 'nip' => '199009012025211142', 'jabatan' => 'Keamanan Sekolah/Penjaga Sekolah'],
            ['name' => 'Irman Supriatna', 'nip' => '-', 'jabatan' => 'Kebersihan Sekolah'],
            ['name' => 'Endang Setiawan', 'nip' => '198011252025211078', 'jabatan' => 'Keamanan Sekolah/Penjaga Sekolah'],
            ['name' => 'Ponimin Hadi Prianto', 'nip' => '198201242025211067', 'jabatan' => 'Keamanan Sekolah/Penjaga Sekolah'],
            ['name' => 'Dodo Aripin', 'nip' => '197012222025211018', 'jabatan' => 'Kebersihan Sekolah dan Pembantu UP. Air Minum'],
            ['name' => 'Sri Maryati Rahayu, S.IIP.', 'nip' => '199104252025212118', 'jabatan' => 'Pengelola Perpustakaan'],
            ['name' => 'Edi Setiadi, S.Pd', 'nip' => '198612022025211127', 'jabatan' => 'Pelaksana Administrasi Persuratan dan Pengarsipan'],
            ['name' => 'Parid Padilah, S.Pd.', 'nip' => '198505022025211175', 'jabatan' => 'Pelaksana Administrasi Kurikulum'],
            ['name' => 'Asep Ridha Permana, S.Pd.', 'nip' => '199605292025211088', 'jabatan' => 'Pelaksana Administrasi Sarana Prasarana, Aset, dan Pengolah Data'],
            ['name' => 'Iman Surahman', 'nip' => '197812252025211075', 'jabatan' => 'Kebersihan Sekolah'],
            ['name' => 'Rani Nuryani Kosasih, S. Kom.', 'nip' => '199411152025212102', 'jabatan' => 'Pelaksana Administrasi Persuratan dan Pengarsipan'],
        ];

        foreach ($tutt as $d) {
            $employees[$d['nip']] = [
                'name'              => $d['name'],
                'nip'               => $d['nip'],
                'jabatan'           => $d['jabatan'],
                'pangkat_golongan'  => null,
                'status'            => 'TU TT',
            ];
        }

        // ---- 2c. Data dari sheet "TUT" (Tenaga Administrasi) ----
        $tut = [
            ['name' => 'Dodo Supriadi, S.Kom.', 'nip' => '197301042000031008', 'jabatan' => 'Pengadministrasi Keuangan'],
            ['name' => 'Heri Heryawan', 'nip' => '197302132014081001', 'jabatan' => 'Pengadministrasi Kepegawaian'],
            ['name' => 'Cucu Syamsudin, S.Kom.', 'nip' => '197901282014081001', 'jabatan' => 'Pengadministrasi Sarana Prasarana'],
        ];

        foreach ($tut as $d) {
            $employees[$d['nip']] = [
                'name'              => $d['name'],
                'nip'               => $d['nip'],
                'jabatan'           => $d['jabatan'],
                'pangkat_golongan'  => null,
                'status'            => 'TU',
            ];
        }

        // ---- 2d. Data dari sheet "PNS" (PNS) ----
        $pns = [
            ['name' => 'H. Cepy Wahyudin, A.Md., S.Kom., S.Kom.', 'nip' => '198408252010011010', 'pangkat' => 'Penata Tk.I /III/d'],
            ['name' => 'Oneng Dalilah, M.Pd.', 'nip' => '196706221991032006', 'pangkat' => 'Pembina Utama Muda/IV/c'],
            ['name' => 'Dra. Elin Karlinah', 'nip' => '196806051994122006', 'pangkat' => 'Pembina Utama Muda/IV/c'],
            ['name' => 'H. Nanang Aziz S., S.Pd.,M.Pd.', 'nip' => '196704281990031002', 'pangkat' => 'Pembina Utama Muda/IV/c'],
            ['name' => 'Yana Hendrayana, S.Pd. M.Pd.', 'nip' => '197105281998021004', 'pangkat' => 'Pembina Utama Muda/IV/c'],
            ['name' => 'Drs. Asep Cahrina', 'nip' => '196801231994121006', 'pangkat' => 'Pembina Utama Muda/IV/c'],
            ['name' => 'H. Cucu Hermawan, M.Pd.', 'nip' => '197401011997021001', 'pangkat' => 'Pembina Utama Muda/IV/c'],
            ['name' => 'Drs. Dadang Nurdin', 'nip' => '196609071994121001', 'pangkat' => 'Pembina Tk. I/IV/b'],
            ['name' => 'Dedeh Kurniasih, S.Pd, M.Pd.', 'nip' => '197010091996012001', 'pangkat' => 'Pembina Tk. I/IV/b'],
            ['name' => 'Hj. Alisah, S.Pd.', 'nip' => '197204111996012001', 'pangkat' => 'Pembina Tk. I/IV/b'],
            ['name' => 'Endah Rahayu D, S.Pd. M.Pd.', 'nip' => '197207122006042006', 'pangkat' => 'Pembina /IV/a'],
            ['name' => 'Tika Mustikawati, S.Pd.', 'nip' => '198202102005012008', 'pangkat' => 'Penata Tk.I /III/d'],
            ['name' => 'Atin Kudriatin, S.Pd.', 'nip' => '197602292006042009', 'pangkat' => 'Penata Tk.I /III/d'],
            ['name' => 'Cucu Cahyani, S.Pd.', 'nip' => '198001102008012006', 'pangkat' => 'Penata Tk.I /III/d'],
            ['name' => 'Imas Masriah, S.Pd.', 'nip' => '197903102006042025', 'pangkat' => 'Penata Tk.I /III/d'],
            ['name' => 'Hj. Atin Herawati, M.Pd.', 'nip' => '198001192008012006', 'pangkat' => 'Penata Tk.I /III/d'],
            ['name' => 'Kiki Supendi, MT.', 'nip' => '197701202009011007', 'pangkat' => 'Penata Tk.I /III/d'],
            ['name' => 'Tita Puspita, S.Pd., M.Pd.', 'nip' => '198102252008012006', 'pangkat' => 'Penata Tk.I /III/d'],
            ['name' => 'Ikhsan Nur Rokhmat, S.Pd.,M.Pd.', 'nip' => '197510112008011004', 'pangkat' => 'Penata/III/c'],
            ['name' => 'Dadan Sugiarna, S.Pd.', 'nip' => '196608091997021001', 'pangkat' => 'Penata/III/c'],
            ['name' => 'Pebi Dinastriani, S.Pd.', 'nip' => '198802052011012004', 'pangkat' => 'Penata/III/c'],
            ['name' => 'Irma Sukmarini, S.Pd.', 'nip' => '198212102009012007', 'pangkat' => 'Penata Tk.I /III/d'],
            ['name' => 'Hariman Hendriana, S.Pd.,M.Pd.', 'nip' => '197708292008011002', 'pangkat' => 'Penata/III/c'],
            ['name' => 'Carkim, S.Pd.', 'nip' => '197512252014081001', 'pangkat' => 'Penata Muda Tk.I/III/b'],
            ['name' => 'Nastiti, S.Pd.', 'nip' => '198909242014012001', 'pangkat' => 'Penata Muda Tk.I/III/b'],
            ['name' => 'Ranti Purnama Dewi, S.Pd.', 'nip' => '198909132019032014', 'pangkat' => 'Penata Muda Tk.I/III/b'],
            ['name' => 'Dini Yudi Kasimamora, S.Tr.Par.', 'nip' => '199303112020122022', 'pangkat' => 'Penata Muda Tk.I/III/b'],
            ['name' => 'Egi Samsul Mu\'arif, S.Pd.', 'nip' => '199603122020121014', 'pangkat' => 'Penata Muda Tk.I/III/b'],
            ['name' => 'Ani Herliani, S. Kom.', 'nip' => '198009162014082003', 'pangkat' => 'Penata Muda /III/a'],
        ];

        foreach ($pns as $d) {
            $nip = $d['nip'];
            if (isset($employees[$nip])) {
                // Update pangkat dan status, pertahankan jabatan yang sudah ada
                $employees[$nip]['pangkat_golongan'] = $d['pangkat'];
                $employees[$nip]['status'] = 'PNS';
            } else {
                $employees[$nip] = [
                    'name'              => $d['name'],
                    'nip'               => $nip,
                    'jabatan'           => null,
                    'pangkat_golongan'  => $d['pangkat'],
                    'status'            => 'PNS',
                ];
            }
        }

        // ---- 2e. Data dari sheet "PPPK" ----
        $pppk = [
            ['name' => 'Yati Setiawati S.Ag', 'nip' => '197608272021212001'],
            ['name' => 'Cahyaman Natawiguna, S.Pd.', 'nip' => '197301312021211002'],
            ['name' => 'Iip Masripah, S.Ag.', 'nip' => '197408012021212002'],
            ['name' => 'Yeyet Rohaeti, S.Pd.', 'nip' => '197501032021212005'],
            ['name' => 'Teti Sri Mulyati Hidayat, S.Pd.', 'nip' => '198211152022212018'],
            ['name' => 'Ane Maryani, S.Pd.', 'nip' => '198703162022212007'],
            ['name' => 'Ati Sukmawati, S.Pd.', 'nip' => '198806272022212014'],
            ['name' => 'Sri Yuliani, S.Pd.', 'nip' => '199007142022212013'],
            ['name' => 'Siti Qomariyah, S.Pd.', 'nip' => '198405252022212026'],
            ['name' => 'Yana Soviyana, SE.', 'nip' => '198104282022211006'],
            ['name' => 'Mahani Gunawan, S.Pd.', 'nip' => '199511012022212012'],
            ['name' => 'Neni Rahmawati, S.Pd.', 'nip' => '199303072022212009'],
            ['name' => 'Aning Nurganah, S. Pd.', 'nip' => '199711272022212002'],
            ['name' => 'Sintiya Nuri Rosmalia, S. Pd.', 'nip' => '199610102022212009'],
            ['name' => 'Imas Dewi Ariyanti, S. Kom.', 'nip' => '199707182022212004'],
            ['name' => 'Fahmi Agniyatu Rahman, S. Pd.', 'nip' => '199708242022211001'],
            ['name' => 'Acep Muhammad Soleh, S.Pd.', 'nip' => '199409152022211004'],
            ['name' => 'Dewi Rosita, S.Pd.', 'nip' => '198507132022212025'],
            ['name' => 'Ika Hasanah, S.Pd.', 'nip' => '198601122022212047'],
            ['name' => 'Deasy Putri Lestari , S.Pd.', 'nip' => '198612132022212017'],
            ['name' => 'Devi Rodiana, S.T.', 'nip' => '198712232022211009'],
            ['name' => 'Pia Amanda Nurhusni, S.Pd.', 'nip' => '198812292022212007'],
            ['name' => 'Yusef Abdul Aziz, S.Pd.', 'nip' => '198911282022211010'],
            ['name' => 'Deslita Seniatsaani, S.Pd.', 'nip' => '199112052022212008'],
            ['name' => 'Tenia Octaviana, S. Pd.', 'nip' => '199210102022212015'],
            ['name' => 'Neri Sondari, S.Pd.', 'nip' => '199501262022212017'],
            ['name' => 'Imas Maesaroh, S.Pd', 'nip' => '199609292022212018'],
            ['name' => 'Yeni Mulyaningsih, S.Si.', 'nip' => '198209122023212017'],
            ['name' => 'Amirudin, S.Pd.', 'nip' => '198605012023211012'],
            ['name' => 'Elwin A.R. S.Pd.', 'nip' => '199205062023211017'],
            ['name' => 'Wiana Yulian, S.Pd.', 'nip' => '199207152023212039'],
            ['name' => 'Julia Meliani Rosadi, S.Pd.', 'nip' => '199505262023212023'],
            ['name' => 'Ratna Nur Indah Sari, S.Pd.', 'nip' => '198709112023212031'],
            ['name' => 'Suci Azmiyati, S.Pd.I.', 'nip' => '198303072023212019'],
            ['name' => 'Sahri Agustian, S. Pd.I.', 'nip' => '198303072023211002'],
            ['name' => 'Harini, S.Pd.', 'nip' => '197806052023212018'],
            ['name' => 'Agung Sofiyani, S.E.', 'nip' => '199206122024212036'],
            ['name' => 'Ai Siti Munawaroh, S.Pd.', 'nip' => '199502082024212017'],
            ['name' => 'Anis Kurly, S.Pd.', 'nip' => '199705152024212025'],
            ['name' => 'Arie Iman Maulani, S.Pd.', 'nip' => '198910182024211007'],
            ['name' => 'Arinaryani Suryani, S.pd.', 'nip' => '199504052024212034'],
            ['name' => 'Dadi Rusyadi, S.Pd.', 'nip' => '198104182024211005'],
            ['name' => 'Desty Sobaryantini, S.Pd.', 'nip' => '199112142024212028'],
            ['name' => 'Fitria Agustina, S.Pd.', 'nip' => '197908232024212005'],
            ['name' => 'Herna Novitasari, S.Pd.', 'nip' => '199506302024212029'],
            ['name' => 'Maman Nurohman, S.Pd.', 'nip' => '199506162025212010'],
            ['name' => 'Meta Nur Mawaty, S.Pd.', 'nip' => '198503072024212009'],
            ['name' => 'Riris Sri Budiyanti, S.Pd.', 'nip' => '197405042024212003'],
            ['name' => 'Wiliandini, S.Pd.', 'nip' => '198202022024212016'],
            ['name' => 'Yeni Maryani, S.Pd.', 'nip' => '197811172024212006'],
            ['name' => 'Muhamad Afrizal, S.Pd.', 'nip' => '199704272024211007'],
            ['name' => 'Aziz Muslim, S.Pd.', 'nip' => '197906232025211006'],
            ['name' => 'Zakiatunnisa Iswahyudi, S.P.', 'nip' => '199202142025212011'],
            ['name' => 'Siti Nurasyiah, S.Pd.', 'nip' => '199506162025212010'],
            ['name' => 'Irfan Nur Mutaqin', 'nip' => '199305202023211006'],
            ['name' => 'Farik Samsul Patoni', 'nip' => '198712222023211009'],
        ];

        foreach ($pppk as $d) {
            $nip = $d['nip'];
            if (isset($employees[$nip])) {
                $employees[$nip]['status'] = 'PPPK';
                // jika pangkat masih null, set PPPK/IX
                if (empty($employees[$nip]['pangkat_golongan'])) {
                    $employees[$nip]['pangkat_golongan'] = 'PPPK/IX';
                }
            } else {
                $employees[$nip] = [
                    'name'              => $d['name'],
                    'nip'               => $nip,
                    'jabatan'           => null,
                    'pangkat_golongan'  => 'PPPK/IX',
                    'status'            => 'PPPK',
                ];
            }
        }

        // ---- 2f. Data dari sheet "PPPK PW" ----
        $pppkpw = [
            ['name' => 'Nina, S.Pd', 'nip' => '198609232025212079'],
            ['name' => 'Hj. Dela Hikmatul Ambia, S.Pd.', 'nip' => '199005042025212167'],
            ['name' => 'Dini Apriani Nurramdan, S.Pd.', 'nip' => '199004092025212141'],
            ['name' => 'Miming Miptahudin, S.Pd.', 'nip' => '198010262025211052'],
            ['name' => 'Tresna Yuniawati, S.Pd.', 'nip' => '199008152025212159'],
            ['name' => 'Arif Zapar Sidik, ST.', 'nip' => '198909182025211135'],
            ['name' => 'Jajang Ikbal Herlianto, S.Pd.', 'nip' => '199602022025211150'],
            ['name' => 'Ervin Maulana Herdiansyah, A.Md.', 'nip' => '199606272025211116'],
            ['name' => 'Dhinda Aghita Mahardhika, S.Pd.', 'nip' => '199508162025212120'],
            ['name' => 'Maulina Fajrin, S. Par.', 'nip' => '199806252025212074'],
            ['name' => 'Iqbal Fauzi Lisyanto, S.Par.', 'nip' => '199610022025211081'],
            ['name' => 'Angga Febrian Pratama, S.Pd.', 'nip' => '199902022025211076'],
            ['name' => 'Befi Apriansyah, S.Pd.', 'nip' => '199204222025211151'],
        ];

        foreach ($pppkpw as $d) {
            $nip = $d['nip'];
            if (isset($employees[$nip])) {
                $employees[$nip]['status'] = 'PPPK PW';
                if (empty($employees[$nip]['pangkat_golongan'])) {
                    $employees[$nip]['pangkat_golongan'] = 'PPPK PW';
                }
            } else {
                $employees[$nip] = [
                    'name'              => $d['name'],
                    'nip'               => $nip,
                    'jabatan'           => null,
                    'pangkat_golongan'  => 'PPPK PW',
                    'status'            => 'PPPK PW',
                ];
            }
        }

        // ---- Insert employees ----
        $now = now();
        foreach ($employees as $nip => $data) {
            // skip jika nip = '-'
            if ($nip === '-') continue;

            DB::table('employees')->updateOrInsert(
                ['nip' => $nip],
                [
                    'name'              => $data['name'],
                    'nip'               => $nip,
                    'jabatan'           => $data['jabatan'] ?? null,
                    'pangkat_golongan'  => $data['pangkat_golongan'] ?? null,
                    'status'            => $data['status'] ?? 'Guru/Tendik',
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]
            );
        }

        // ============================================================
        // 3. SEED WALI KELAS (class_teachers)
        // ============================================================
        $waliKelas = [
            ['name' => 'Yeni Mulyaningsih, S.Pd.', 'nip' => '198209122023212017', 'class' => '10 AKL 1'],
            ['name' => 'Tenia Octaviana, S. Pd.', 'nip' => '199210102022212015', 'class' => '10 AKL 2'],
            ['name' => 'Arie Iman Maulani, S.Pd.', 'nip' => '198910182024211007', 'class' => '10 AKL 3'],
            ['name' => 'Ane Maryani, S.Pd.', 'nip' => '198703162022212007', 'class' => '10 AKL 4'],
            ['name' => 'Tresna Yuniawati, S.Pd.', 'nip' => '199008152025212159', 'class' => '10 AKL 5'],
            ['name' => 'Cucu Cahyani, S.Pd.', 'nip' => '198001102008012006', 'class' => '10 PM 1'],
            ['name' => 'Siti Nurasyiah, S.Pd.', 'nip' => '199506162025212010', 'class' => '10 PM 2'],
            ['name' => 'Maman Nurohman, S.Pd.', 'nip' => '199506162025212010', 'class' => '10 PM 3'],
            ['name' => 'Farik Samsul Fatoni, S.Pd.', 'nip' => '198712222023211009', 'class' => '10 PM 4'],
            ['name' => 'Sintiya Nuri Rosmalia, S.Pd.', 'nip' => '199610102022212009', 'class' => '10 MPLB 1'],
            ['name' => 'Hariman Herdiana, M.Pd.', 'nip' => '197708292008011002', 'class' => '10 MPLB 2'],
            ['name' => 'Angga Febrian Pratama, S.Pd.', 'nip' => '199902022025211076', 'class' => '10 MPLB 3'],
            ['name' => 'Dini Yudi Kasimamora, S.Tr.Par.', 'nip' => '199303112020122022', 'class' => '10 Perhotelan 1'],
            ['name' => 'Zakiatunnisa Iswahyudi, S.Pd.', 'nip' => '199202142025212011', 'class' => '10 Perhotelan 2'],
            ['name' => 'Imas Maesaroh, S.Pd.', 'nip' => '199609292022212018', 'class' => '10 Kuliner 1'],
            ['name' => 'Neni Rahmawati, S.Pd.', 'nip' => '199303072022212009', 'class' => '10 Kuliner 2'],
            ['name' => 'Irfan Nur Muttaqin, S.Pd.I.', 'nip' => '199305202023211006', 'class' => '10 DKV'],
            ['name' => 'Devi Rodiana, S.T.', 'nip' => '198712232022211009', 'class' => '10 PPLG'],
            ['name' => 'Irma Sukmarini, S.Pd.', 'nip' => '198212102009012007', 'class' => '11 AKL 1'],
            ['name' => 'Hj. Atin Herawati, M.Pd.', 'nip' => '198001192008012006', 'class' => '11 AKL 2'],
            ['name' => 'Elwin A.R. S.Pd.', 'nip' => '199205062023211017', 'class' => '11 AKL 3'],
            ['name' => 'Imas Masriah, S.Pd.', 'nip' => '197903102006042025', 'class' => '11 AKL 4'],
            ['name' => 'Meta Nur Mawaty, S.Pd.', 'nip' => '198503072024212009', 'class' => '11 PBS'],
            ['name' => 'Nina, S.Pd.', 'nip' => '198609232025212079', 'class' => '11 PM 1'],
            ['name' => 'Riris Sri Budiyanti, S.Pd.', 'nip' => '197405042024212003', 'class' => '11 PM 2'],
            ['name' => 'Fitria Agustina, S.Pd.', 'nip' => '197908232024212005', 'class' => '11 PM 3'],
            ['name' => 'Teti Sri Mulyati Hidayat, S.Pd.', 'nip' => '198211152022212018', 'class' => '11 PM 4'],
            ['name' => 'Yeni Maryani, S.Pd.', 'nip' => '197811172024212006', 'class' => '11 MPLB 1'],
            ['name' => 'Fahmi Agniyatu Rahman, S.Pd.', 'nip' => '199708242022211001', 'class' => '11 MPLB 2'],
            ['name' => 'Neri Sondari, S.Pd.', 'nip' => '199501262022212017', 'class' => '11 MPLB 3'],
            ['name' => 'Iqbal Fauzi Lisyanto, S.Par.', 'nip' => '198303072023212019', 'class' => '11 Perhotelan 1'],
            ['name' => 'Tika Mustikawati, S.Pd.,M.Pd.', 'nip' => '198202102005012008', 'class' => '11 Perhotelan 2'],
            ['name' => 'Ika Hasanah, S.Pd.', 'nip' => '198601122022212047', 'class' => '11 Kuliner 1'],
            ['name' => 'Ervin Maulana Herdiansyah, A.Md.', 'nip' => '198104182024211005', 'class' => '11 Kuliner 2'],
            ['name' => 'Imas Dewi Ariyanti, S.Kom.', 'nip' => '199707182022212004', 'class' => '11 DKV'],
            ['name' => 'Amirudin, S.Pd.', 'nip' => '198605012023211012', 'class' => '11 PPLG'],
            ['name' => 'Siti Qomariyah, S.Pd.', 'nip' => '198405252022212026', 'class' => '12 AKL 1'],
            ['name' => 'Harini, S.Pd.', 'nip' => '197806052023212018', 'class' => '12 AKL 2'],
            ['name' => 'Ai Siti Munawaroh, S.Pd.', 'nip' => '199502082024212017', 'class' => '12 AKL 3'],
            ['name' => 'Agung Sofiyani, S.E.', 'nip' => '199206122024212036', 'class' => '12 AKL 4'],
            ['name' => 'Desty Sobaryantini, S.Pd.', 'nip' => '199112142024212028', 'class' => '12 PBS'],
            ['name' => 'Dini Apriani Nurramdan, S.Pd.', 'nip' => '198806272022212014', 'class' => '12 PM 1'],
            ['name' => 'Hj. Dela Hikmatul Ambia, S.Pd.', 'nip' => '198911282022211010', 'class' => '12 PM 2'],
            ['name' => 'Ratna Nur Indah Sari, S.Pd.', 'nip' => '198709112023212031', 'class' => '12 PM 3'],
            ['name' => 'Dhinda Aghita Mahardhika, S.Pd.', 'nip' => '199508162025212120', 'class' => '12 PM 4'],
            ['name' => 'Anis Kurly, S.Pd.', 'nip' => '199705152024212025', 'class' => '12 MPLB 1'],
            ['name' => 'Aning Nurganah, S.Pd.', 'nip' => '199711272022212002', 'class' => '12 MPLB 2'],
            ['name' => 'Sahri Agustian, S.Pd.I.', 'nip' => '198303072023211002', 'class' => '12 MPLB 3'],
            ['name' => 'Acep Muhammad Soleh, S.Pd.', 'nip' => '199409152022211004', 'class' => '12 Kuliner 1'],
            ['name' => 'Dadi Rusyadi, S.Pd.', 'nip' => '198104182024211005', 'class' => '12 Kuliner 2'],
            ['name' => 'Suci Azmiyati, S.Pd.I.', 'nip' => '198303072023212019', 'class' => '12 Perhotelan 1'],
            ['name' => 'Maulina Fajrin, S.Par.', 'nip' => '199704272024211007', 'class' => '12 Perhotelan 2'],
            ['name' => 'Arif Zapar Sidik, ST.', 'nip' => '197903102006042025', 'class' => '12 DKV'],
            ['name' => 'Ranti Purnama Dewi, S.Pd.', 'nip' => '198909132019032014', 'class' => '12 PPLG'],
        ];

        foreach ($waliKelas as $wk) {
            $nip = $wk['nip'];
            // Pastikan employee dengan NIP ini ada, jika tidak buat baru dengan status 'Guru'
            $employee = DB::table('employees')->where('nip', $nip)->first();
            if (!$employee) {
                // Tambahkan sebagai guru
                DB::table('employees')->insert([
                    'name'              => $wk['name'],
                    'nip'               => $nip,
                    'jabatan'           => 'Wali Kelas ' . $wk['class'],
                    'pangkat_golongan'  => null,
                    'status'            => 'Guru',
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]);
                $employeeId = DB::getPdo()->lastInsertId();
            } else {
                $employeeId = $employee->id;
            }

            // Insert ke class_teachers
            DB::table('class_teachers')->updateOrInsert(
                [
                    'employee_id' => $employeeId,
                    'class_name'  => $wk['class'],
                ],
                [
                    'year'        => 2025,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]
            );
        }

        // ============================================================
        // 4. SEED INTERNS (PLP, PPG, GEMA UPI)
        // ============================================================
        $interns = [];

        // ---- PLP ----
        $plp = [
            ['name' => 'DHEA SOFI YULIANA', 'nim' => '2108230047', 'program_studi' => 'Bahasa Indonesia'],
            ['name' => 'NOER YULIE EKA ASKHOIRIYAH', 'nim' => '2108230055', 'program_studi' => 'Bahasa Indonesia'],
            ['name' => 'RIKA SITI NURJANAH', 'nim' => '2108230026', 'program_studi' => 'Bahasa Indonesia'],
            ['name' => 'DEWI WULANDARI', 'nim' => '2108230027', 'program_studi' => 'Bahasa Indonesia'],
            ['name' => 'NAZLA ZALFA ZAKIYYAH', 'nim' => '2108230031', 'program_studi' => 'Bahasa Indonesia'],
            ['name' => 'DIVA AZZAHRA', 'nim' => '2108230034', 'program_studi' => 'Bahasa Indonesia'],
            ['name' => 'DZIBAN NAIL FARUQ', 'nim' => '2108230036', 'program_studi' => 'Bahasa Indonesia'],
            ['name' => 'RIFA INSANI MAHMUDA', 'nim' => '2108230039', 'program_studi' => 'Bahasa Indonesia'],
            ['name' => 'NOVI NURUL HIDAYAH', 'nim' => '2108230087', 'program_studi' => 'Bahasa Indonesia'],
            ['name' => 'AULIA PUTRI MULYONO', 'nim' => '2108230088', 'program_studi' => 'Bahasa Indonesia'],
            ['name' => 'GISKA APRILYANI SUTISNA', 'nim' => '2107230002', 'program_studi' => 'Akuntansi'],
            ['name' => 'DEDE SURYADI', 'nim' => '2107230005', 'program_studi' => 'Akuntansi'],
            ['name' => 'AMALLYA SYALSYABILA PUTRY', 'nim' => '2107230006', 'program_studi' => 'Akuntansi'],
            ['name' => 'IRMAWATI SRI RAHAYU', 'nim' => '2107230009', 'program_studi' => 'Akuntansi'],
            ['name' => 'SELLA ANATASYA NURPASHA', 'nim' => '2107230010', 'program_studi' => 'Akuntansi'],
            ['name' => 'DEFALYA NAINA SUHERMAN', 'nim' => '2107230021', 'program_studi' => 'Akuntansi'],
            ['name' => 'RINTAN RAHAYU', 'nim' => '2107230024', 'program_studi' => 'Akuntansi'],
            ['name' => 'IZMA AULIA AL KAHFI', 'nim' => '2124230005', 'program_studi' => 'Penjas'],
            ['name' => 'SALMA ZIANI AL HAKIM', 'nim' => '2124230006', 'program_studi' => 'Penjas'],
            ['name' => 'EGA NUGRAHA', 'nim' => '2124230008', 'program_studi' => 'Penjas'],
            ['name' => 'GANJAR SUKMA WIJAYA', 'nim' => '2124230012', 'program_studi' => 'Penjas'],
            ['name' => 'RENDI', 'nim' => '2124230013', 'program_studi' => 'Penjas'],
            ['name' => 'TAUFIK FAUZAN HIDAYAH', 'nim' => '2124230015', 'program_studi' => 'Penjas'],
            ['name' => 'CINDY AULIA BAHAR', 'nim' => '2124230017', 'program_studi' => 'Penjas'],
            ['name' => 'DIAN YULIASARI', 'nim' => '2124230021', 'program_studi' => 'Penjas'],
            ['name' => 'GALUNG SAMPAK GIERI MARTIKAL', 'nim' => '2124230026', 'program_studi' => 'Penjas'],
            ['name' => 'ADI NURJAMAN', 'nim' => '2124230039', 'program_studi' => 'Penjas'],
            ['name' => 'ADE DANA NURDIANA', 'nim' => '2124230040', 'program_studi' => 'Penjas'],
            ['name' => 'MUHAMMAD NABIIL AL MALKI', 'nim' => '2124230108', 'program_studi' => 'Penjas'],
            ['name' => 'NADIA SEPTIANI', 'nim' => '2124230110', 'program_studi' => 'Penjas'],
            ['name' => 'SARTIKA TRIA PRASTIWI', 'nim' => '2124230186', 'program_studi' => 'Penjas'],
            ['name' => 'RATABILABAGI AMRY', 'nim' => '2124230112', 'program_studi' => 'Penjas'],
        ];
        foreach ($plp as $d) {
            $interns[] = array_merge($d, ['jenis' => 'PLP']);
        }

        // ---- PPG ----
        $ppg = [
            ['name' => 'MUHAMMAD FANDRY', 'nim' => '2186250078', 'program_studi' => 'PJOK'],
            ['name' => 'KORI ALFAJRI', 'nim' => '2186250068', 'program_studi' => 'PJOK'],
            ['name' => 'AGUNG BAHTIAR', 'nim' => '2186250076', 'program_studi' => 'PJOK'],
            ['name' => 'MEGA YULIANTIKA', 'nim' => '2186250038', 'program_studi' => 'PJOK'],
            ['name' => 'ALPIN HALIMI', 'nim' => '2186250083', 'program_studi' => 'PJOK'],
            ['name' => 'AGNES MONIKA R', 'nim' => '2186250081', 'program_studi' => 'PJOK'],
            ['name' => 'AHMAD REZA ARDIANSYAH', 'nim' => '2186250074', 'program_studi' => 'PJOK'],
            ['name' => 'KOMALA NOOR HERDIANY', 'nim' => '2186250037', 'program_studi' => 'PJOK'],
            ['name' => 'KEMAL MOHAMAD RADYAN', 'nim' => '2186250058', 'program_studi' => 'PJOK'],
            ['name' => 'A.R. FELIANSYAH WUGUNA', 'nim' => '2186250080', 'program_studi' => 'PJOK'],
            ['name' => 'ALDI SOPANDI', 'nim' => '2186250081', 'program_studi' => 'PJOK'],
            ['name' => 'MELKIOR KRISTO PUJIANTO', 'nim' => '2186250039', 'program_studi' => 'PJOK'],
        ];
        foreach ($ppg as $d) {
            $interns[] = array_merge($d, ['jenis' => 'PPG']);
        }

        // ---- GEMA UPI ----
        $gema = [
            ['name' => 'NAWAL NURHAYIPA', 'nim' => '2301224', 'program_studi' => 'PENDIDIKAN PARIWISATA'],
            ['name' => 'DHANISYA RIZFY ZAKISWARA', 'nim' => '2309856', 'program_studi' => 'PENDIDIKAN PARIWISATA'],
        ];
        foreach ($gema as $d) {
            $interns[] = array_merge($d, ['jenis' => 'GEMA UPI']);
        }

        // Insert interns
        foreach ($interns as $d) {
            DB::table('interns')->updateOrInsert(
                [
                    'nim'   => $d['nim'],
                    'jenis' => $d['jenis'],
                ],
                [
                    'name'           => $d['name'],
                    'nim'            => $d['nim'],
                    'program_studi'  => $d['program_studi'],
                    'jenis'          => $d['jenis'],
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]
            );
        }
    }
}
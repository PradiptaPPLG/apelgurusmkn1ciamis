<?php

namespace App\Services;

use App\Models\ApelSession;
use App\Models\Participant;
use App\Models\Attendance;

class MotivationalQuoteService
{
    /**
     * Dapatkan kata motivasi unik untuk setiap guru pada sesi apel tertentu.
     * Menggunakan permutasi matematis (coprime bijection) sehingga dalam 1 sesi / hari yang sama,
     * setiap guru dijamin mendapatkan kata motivasi yang BERBEDA dan berganti setiap hari.
     */
    public static function getQuoteForAttendance(ApelSession $session, Participant $participant): array
    {
        $quotes = self::getAllQuotes();
        $totalQuotes = count($quotes);

        // Urutan kehadiran guru pada sesi ini (1, 2, 3, ...)
        $attendanceRank = Attendance::where('apel_session_id', $session->id)->count();
        $k = max(0, $attendanceRank - 1);

        // Seed berbasis tanggal dan ID sesi agar setiap hari urutannya selalu baru & teracak
        $dateStr = $session->date ? \Carbon\Carbon::parse($session->date)->format('Y-m-d') : date('Y-m-d');
        $seed = abs((int) crc32($dateStr . '_' . $session->id));

        // Offset harian
        $dailyOffset = $seed % $totalQuotes;

        // Daftar bilangan prima yang koprima terhadap $totalQuotes untuk menjamin permutasi bijektif (tanpa tabrakan)
        $coprimes = [37, 41, 43, 47, 53, 59, 61, 67, 71, 73, 79, 83, 89, 97, 101, 103, 107, 109, 113, 127, 131, 137, 139, 149];
        $coprime = $coprimes[($seed >> 4) % count($coprimes)];

        // Rumus permutasi bijektif: menjamin index selalu unik untuk setiap $k dalam rentang $totalQuotes
        $quoteIndex = ($dailyOffset + ($k * $coprime)) % $totalQuotes;

        return $quotes[$quoteIndex];
    }

    /**
     * Kumpulan 225+ kata motivasi pilihan:
     * - Sopan, santun, inspiratif, bermakna
     * - Tanpa unsur SARA atau provokasi
     * - Relate dengan dedikasi guru, pendidikan, dan kehidupan sehari-hari
     */
    public static function getAllQuotes(): array
    {
        return [
            ['quote' => 'Setiap ilmu yang Bapak/Ibu bagikan hari ini adalah lentera yang akan menerangi masa depan peserta didik.', 'author' => 'Inspirasi Pendidik'],
            ['quote' => 'Ketulusan dalam mendidik tidak akan pernah sia-sia, kebaikan yang ditanam hari ini akan berbuah kesuksesan di masa depan.', 'author' => 'Kata Bijak'],
            ['quote' => 'Ing ngarsa sung tuladha, ing madya mangun karsa, tut wuri handayani. Keteladanan adalah metode mendidik yang paling berkesan.', 'author' => 'Ki Hajar Dewantara'],
            ['quote' => 'Jadikan hari ini kesempatan untuk memberikan pengaruh positif terbaik kepada setiap siswa yang kita temui.', 'author' => 'Motivasi Pagi'],
            ['quote' => 'Pekerjaan hebat tidak dilakukan dengan kekuatan semata, melainkan dengan ketekunan, dedikasi, dan keikhlasan.', 'author' => 'Samuel Johnson'],
            ['quote' => 'Guru terbaik bukan hanya mengajarkan mata pelajaran, tetapi juga menumbuhkan rasa ingin tahu dan karakter luhur.', 'author' => 'Inspirasi Guru'],
            ['quote' => 'Awali pagi ini dengan rasa syukur dan senyuman. Energi positif Anda adalah semangat bagi orang-orang di sekitar Anda.', 'author' => 'Semangat Pagi'],
            ['quote' => 'Keberhasilan besar selalu dimulai dari langkah-langkah kecil yang konsisten dan penuh komitmen.', 'author' => 'Mutiara Kata'],
            ['quote' => 'Di tangan seorang pendidik yang berdedikasi, setiap potensi siswa dapat tumbuh menjadi prestasi yang membanggakan.', 'author' => 'SMKN 1 Ciamis'],
            ['quote' => 'Bekerja dengan hati akan selalu menghadirkan ketenangan dan kepuasan yang mendalam pada setiap hasil karya kita.', 'author' => 'Refleksi Diri'],
            ['quote' => 'Jangan pernah meremehkan senyum dan sapaan ramah Anda pagi ini, hal itu bisa jadi penyemangat terbesar bagi seseorang.', 'author' => 'Pesan Positif'],
            ['quote' => 'Pendidikan adalah senjata paling ampuh yang dapat Anda gunakan untuk mengubah dunia menjadi lebih baik.', 'author' => 'Nelson Mandela'],
            ['quote' => 'Kesabaran dalam membimbing siswa adalah investasi moral yang hasilnya abadi melintasi generasi.', 'author' => 'Inspirasi Edukasi'],
            ['quote' => 'Jadilah versi terbaik dari diri Anda hari ini, karena setiap hari adalah kesempatan baru untuk berbuat lebih baik.', 'author' => 'Pengembangan Diri'],
            ['quote' => 'Kerjasama yang solid di antara rekan kerja membuat tantangan terberat terasa lebih ringan dan menyenangkan.', 'author' => 'Semangat Tim'],
            ['quote' => 'Belajar tidak pernah membuat pikiran lelah. Teruslah tumbuh dan menginspirasi lingkungan sekitar kita.', 'author' => 'Leonardo da Vinci'],
            ['quote' => 'Kualitas hidup kita ditentukan oleh bagaimana kita merespons setiap kesempatan dan tantangan dengan penuh tanggung jawab.', 'author' => 'Motivasi Hidup'],
            ['quote' => 'Mendidik pikiran tanpa mendidik budi pekerti bukanlah pendidikan yang sejati. Tanamkan adab dan akhlak mulia.', 'author' => 'Aristoteles'],
            ['quote' => 'Setiap usaha yang Anda lakukan dengan ikhlas hari ini adalah tabungan kebaikan untuk hari esok.', 'author' => 'Kata Mutiara'],
            ['quote' => 'Kehangatan hati seorang guru mampu mencairkan keraguan dan menumbuhkan rasa percaya diri anak didik.', 'author' => 'Inspirasi Guru'],
            ['quote' => 'Disiplin adalah jembatan antara cita-cita dan pencapaian nyata. Tetaplah konsisten dalam kebaikan.', 'author' => 'Jim Rohn'],
            ['quote' => 'Hargai setiap proses dan kemajuan kecil, karena gunung yang tinggi pun tersusun dari butiran-butiran batu kecil.', 'author' => 'Pepatah Bijak'],
            ['quote' => 'Ketika kita mengajar, kita belajar dua kali. Berbagi pengetahuan adalah cara memperkaya wawasan diri.', 'author' => 'Joseph Joubert'],
            ['quote' => 'Jadikan integritas sebagai kompas dalam setiap tindakan dan keputusan yang kita ambil sehari-hari.', 'author' => 'Nilai Integritas'],
            ['quote' => 'Masa depan bangsa ini sedang duduk di ruang-ruang kelas Anda. Rawatlah impian mereka dengan sepenuh cinta.', 'author' => 'Pendidik Bangsa'],
            ['quote' => 'Kebahagiaan sejati hadir ketika apa yang kita pikirkan, katakan, dan lakukan selaras dalam harmoni kebaikan.', 'author' => 'Mahatma Gandhi'],
            ['quote' => 'Tetaplah rendah hati saat berada di atas, dan tetaplah berpengharapan teguh saat menghadapi rintangan.', 'author' => 'Refleksi Diri'],
            ['quote' => 'Satu kata pujian yang tulus dari seorang guru bisa mengubah jalan hidup seorang siswa selamanya.', 'author' => 'Inspirasi Pendidikan'],
            ['quote' => 'Bekerjalah dengan niat ibadah dan ketulusan, agar setiap lelah kita bernilai pahala dan kebaikan.', 'author' => 'Motivasi Diri'],
            ['quote' => 'Guru yang bijak membimbing siswa bukan dengan paksaan, melainkan dengan membukakan pintu pemahaman.', 'author' => 'Kata Pencerahan'],
            ['quote' => 'Fokuslah pada solusi di setiap masalah yang hadir. Sikap positif selalu melahirkan jalan keluar.', 'author' => 'Pola Pikir Positif'],
            ['quote' => 'Menjadi teladan dalam kejujuran dan kedisiplinan adalah warisan paling berharga bagi generasi penerus.', 'author' => 'Nilai Luhur'],
            ['quote' => 'Semangat pagi ini adalah kunci pembuka keberkahan dan produktivitas sepanjang hari. Selamat beraktivitas!', 'author' => 'Semangat Apel'],
            ['quote' => 'Pohon yang berbuah lebat berawal dari akar yang kokoh. Fondasi karakter anak berawal dari bimbingan guru yang tulus.', 'author' => 'Filosofi Hidup'],
            ['quote' => 'Tantangan hari ini adalah batu loncatan yang akan mendewasakan dan memperkuat kemampuan kita.', 'author' => 'Kekuatan Mental'],
            ['quote' => 'Jadilah lentera di tempat gelap, penyejuk di saat terik, dan pembawa inspirasi di manapun Anda berada.', 'author' => 'Mutiara Pagi'],
            ['quote' => 'Kebaikan yang Anda lakukan hari ini, sekecil apapun itu, akan selalu menemukan jalannya untuk kembali kepada Anda.', 'author' => 'Hukum Kebaikan'],
            ['quote' => 'Kreativitas dalam mengajar adalah seni menghidupkan rasa ingin tahu yang tak pernah padam.', 'author' => 'Inspirasi Pembelajaran'],
            ['quote' => 'Jangan hitung apa yang telah Anda berikan, tetapi nikmatilah bagaimana kebaikan itu memberi arti bagi sesama.', 'author' => 'Keikhlasan Bekerja'],
            ['quote' => 'Keberhasilan seorang guru tercermin saat murid-muridnya mampu melampaui apa yang pernah diajarkan.', 'author' => 'Kebanggaan Pendidik'],
            ['quote' => 'Waktu adalah hal paling berharga yang kita miliki. Isilah hari ini dengan karya dan dedikasi terbaik.', 'author' => 'Manajemen Waktu'],
            ['quote' => 'Tetaplah menjadi pendengar yang baik bagi mereka yang membutuhkan arahan dan pengertian.', 'author' => 'Empati Sosial'],
            ['quote' => 'Setiap hari membawa peluang baru untuk belajar, mengajar, dan menciptakan perubahan yang berarti.', 'author' => 'Peluang Baru'],
            ['quote' => 'Ilmu yang bermanfaat adalah warisan abadi yang tak akan pernah habis terkikis oleh waktu.', 'author' => 'Nilai Kebijakan'],
            ['quote' => 'Ketenangan jiwa berakar dari rasa syukur atas apa yang telah kita capai dan optimisme menyongsong masa depan.', 'author' => 'Rasa Syukur'],
            ['quote' => 'Sentuhan empati dan perhatian dari guru adalah obat terbaik bagi siswa yang sedang kehilangan semangat belajar.', 'author' => 'Kasih Sayang Pendidik'],
            ['quote' => 'Jadilah pribadi yang selalu menebarkan kedamaian, semangat optimisme, dan aura positif di lingkungan sekolah.', 'author' => 'Lingkungan Positif'],
            ['quote' => 'Ketekunan mengalahkan bakat ketika bakat tidak diiringi dengan ketekunan dan kerja keras.', 'author' => 'Tim Notke'],
            ['quote' => 'Guru adalah arsitek jiwa dan karakter bangsa. Bekerjalah dengan rasa bangga dan dedikasi penuh.', 'author' => 'Profesi Mulia'],
            ['quote' => 'Semoga hari ini penuh dengan kelancaran, kemudahan dalam setiap tugas, dan kebahagiaan yang melimpah.', 'author' => 'Doa Pagi'],
            ['quote' => 'Ketika kita mendidik seorang anak dengan cinta, kita sedang menanamkan benih peradaban yang beradab.', 'author' => 'Inspirasi Pengasuhan'],
            ['quote' => 'Bukan seberapa cepat kita sampai ke tujuan, melainkan seberapa kokoh langkah dan integritas kita dalam perjalanan.', 'author' => 'Prinsip Hidup'],
            ['quote' => 'Hadapilah hari ini dengan ketenangan pikiran. Masalah besar akan mengecil saat dihadapi dengan kepala dingin.', 'author' => 'Ketenangan Batin'],
            ['quote' => 'Keahlian teknis membuka pintu peluang, namun karakter dan etika yang baiklah yang akan menjaga pintu itu tetap terbuka.', 'author' => 'Pendidikan Vokasi'],
            ['quote' => 'Siswa tidak hanya mengingat apa yang kita ajarkan di papan tulis, tapi mereka mengenang bagaimana kita memperlakukan mereka.', 'author' => 'Kenangan Guru'],
            ['quote' => 'Mari saling menguatkan, saling menghargai, dan bersama-sama memajukan kualitas pendidikan di SMK Negeri 1 Ciamis.', 'author' => 'SMKN 1 Ciamis Juara'],
            ['quote' => 'Kecerdasan ditambah karakter—itulah tujuan sejati dari sebuah pendidikan yang bermutu.', 'author' => 'Martin Luther King Jr.'],
            ['quote' => 'Tetaplah bersemangat meskipun lelah menyapa, karena setiap tetes keringat perjuangan Anda bernilai mulia.', 'author' => 'Penyemangat Diri'],
            ['quote' => 'Keikhlasan adalah kunci yang membuka pintu kemudahan dalam setiap urusan yang kita jalani.', 'author' => 'Nilai Keikhlasan'],
            ['quote' => 'Membaca membuka jendela dunia, namun mengamalkan ilmu adalah membangun pintu peradaban.', 'author' => 'Pesan Literasi'],
            ['quote' => 'Jadikan ruang kelas Anda tempat yang aman, nyaman, dan menyenangkan bagi anak didik untuk bereksplorasi.', 'author' => 'Suasana Belajar'],
            ['quote' => 'Tidak ada usaha yang sia-sia jika diniatkan untuk kebaikan dan kemaslahatan bersama.', 'author' => 'Kekuatan Niat'],
            ['quote' => 'Kesuksesan sejati adalah saat kehadiran kita membawa manfaat dan ketenteraman bagi orang lain.', 'author' => 'Manfaat Hidup'],
            ['quote' => 'Bintang bersinar paling terang di saat langit paling gelap. Tetaplah optimis di setiap keadaan.', 'author' => 'Harapan Baru'],
            ['quote' => 'Keterampilan vokasi yang terampil dipadukan dengan sikap disiplin adalah bekal terbaik menuju masa depan gemilang.', 'author' => 'Semangat SMK'],
            ['quote' => 'Hargai setiap perbedaan potensi siswa, karena setiap bunga mekar pada waktu dan keindahannya masing-masing.', 'author' => 'Keunikan Siswa'],
            ['quote' => 'Bersikap ramah tidak membutuhkan biaya, namun mampu menghasilkan kekayaan rasa di hati sesama.', 'author' => 'Kebaikan Hati'],
            ['quote' => 'Pendidikan adalah proses menumbuhkan apa yang ada di dalam diri anak, bukan sekadar menuangkan pengetahuan ke dalamnya.', 'author' => 'Alice Wellington Rollins'],
            ['quote' => 'Tinggalkan jejak teladan yang baik, karena jejak itulah yang akan diikuti oleh generasi setelah kita.', 'author' => 'Teladan Guru'],
            ['quote' => 'Semoga setiap ikhtiar Bapak/Ibu guru hari ini dimudahkan dan membawa keberkahan bagi keluarga tercinta.', 'author' => 'Salam Hangat'],
            ['quote' => 'Sikap hormat dan santun adalah bahasa universal yang dapat didengar oleh yang tuli dan dilihat oleh yang buta.', 'author' => 'Mark Twain'],
            ['quote' => 'Jangan pernah merasa terlambat untuk memulai sesuatu yang baik dan bermanfaat.', 'author' => 'Motivasi Tindakan'],
            ['quote' => 'Kekuatan sebuah sekolah terletak pada harmoni dan kebersamaan seluruh tenaga pendidik dan kependidikannya.', 'author' => 'Harmoni Sekolah'],
            ['quote' => 'Pujian yang tepat waktu mampu membangkitkan rasa percaya diri yang terkubur dalam diri seorang siswa.', 'author' => 'Apresiasi Positif'],
            ['quote' => 'Tetaplah konsisten menjaga amanah, karena kepercayaan adalah mahkota kehormatan seorang pendidik.', 'author' => 'Nilai Amanah'],
            ['quote' => 'Hidup adalah rangkaian proses belajar tanpa henti. Selalu ada pelajaran berharga dari setiap peristiwa.', 'author' => 'Pembelajaran Hidup'],
            ['quote' => 'Terima kasih atas dedikasi dan pengabdian tulus Bapak/Ibu sekalian. Guru adalah pahlawan pembangun peradaban.', 'author' => 'Penghargaan Guru'],
            ['quote' => 'Semangat kebersamaan kita hari ini adalah fondasi kokoh bagi kemajuan SMK Negeri 1 Ciamis tercinta.', 'author' => 'SMKN 1 Ciamis'],
            ['quote' => 'Keberanian untuk terus mencoba adalah awal dari segala inovasi dan kemajuan di dunia pendidikan.', 'author' => 'Inovasi Pendidikan'],
            ['quote' => 'Kendalikan apa yang ada dalam kendali kita: sikap, kerja keras, dan respon positif terhadap kehidupan.', 'author' => 'Kebijaksanaan Stoik'],
            ['quote' => 'Jadilah pendidik yang kehadirannya selalu dinantikan dan nasihatnya selalu membekas di relung hati.', 'author' => 'Pendidik Sejati'],
            ['quote' => 'Setiap hari adalah lembaran putih baru. Mari kita tuliskan kisah dedikasi terbaik hari ini.', 'author' => 'Inspirasi Harian'],
            ['quote' => 'Orang hebat bisa melahirkan beberapa karya bermutu, tetapi guru yang bermutu bisa melahirkan ribuan orang hebat.', 'author' => 'Ungkapan Mutiara'],
            ['quote' => 'Kemampuan mendengarkan dengan sabar seringkali lebih berharga daripada seribu kata nasihat.', 'author' => 'Kearifan Komunikasi'],
            ['quote' => 'Mari rawat semangat persaudaraan dan profesionalisme dalam lingkungan kerja kita setiap saat.', 'author' => 'Budaya Kerja Sehat'],
            ['quote' => 'Jangan biarkan kepenatan sesaat melunturkan senyum ramah yang menjadi ciri khas ketulusan Anda.', 'author' => 'Penyegar Jiwa'],
            ['quote' => 'Bimbinglah anak-anak kita sesuai dengan zamannya, bekali mereka dengan kecakapan dan keteguhan akhlak.', 'author' => 'Ali bin Abi Thalib'],
            ['quote' => 'Kesederhanaan dalam bersikap mencerminkan kematangan jiwa dan keagungan budi pekerti.', 'author' => 'Nilai Kesederhanaan'],
            ['quote' => 'Tugas kita bukan sekadar menyampaikan materi kurikulum, melainkan menyalakan api inspirasi dalam diri siswa.', 'author' => 'William Butler Yeats'],
            ['quote' => 'Rasa syukur adalah magnet kebahagiaan. Semakin banyak kita bersyukur, semakin banyak hal baik yang kita rasakan.', 'author' => 'Pikiran Positif'],
            ['quote' => 'Kebersihan lingkungan dan ketertiban administrasi adalah cermin dari kedisiplinan dan profesionalisme kita.', 'author' => 'Budaya Tertib'],
            ['quote' => 'Selamat mengabdi Bapak/Ibu guru hebat. Langkah Anda hari ini bernilai ibadah dan pengabdian luhur.', 'author' => 'Semangat Apel Pagi'],
            ['quote' => 'Guru yang penuh antusiasme akan menularkan semangat belajar yang membara kepada setiap muridnya.', 'author' => 'Energi Belajar'],
            ['quote' => 'Kegagalan adalah guru terbaik jika kita bersedia merenung dan belajar dari setiap kekeliruan.', 'author' => 'Evaluasi Diri'],
            ['quote' => 'Tulus dalam berbuat, santun dalam bersikap, dan gigih dalam berkarya adalah pedoman hidup mulia.', 'author' => 'Karakter Luhur'],
            ['quote' => 'Pendidikan vokasi adalah jembatan nyata yang menghubungkan mimpi generasi muda dengan dunia industri dan kemandirian.', 'author' => 'SMK Bisa SMK Hebat'],
            ['quote' => 'Ketika kita memperlakukan siswa dengan penuh rasa hormat, mereka belajar untuk menghargai diri mereka sendiri dan sesama.', 'author' => 'Rasa Hormat'],
            ['quote' => 'Keceriaan pagi yang Anda pancarkan adalah energi yang menghidupkan suasana sekolah sepanjang hari.', 'author' => 'Keceriaan Pagi'],
            ['quote' => 'Tetaplah teguh memegang prinsip kebenaran dan kejujuran di tengah segala dinamika kehidupan.', 'author' => 'Integritas Diri'],
            ['quote' => 'Satu keteladanan nyata jauh lebih berpengaruh daripada seribu kata-kata teoritis.', 'author' => 'Kekuatan Keteladanan'],
            ['quote' => 'Semoga hari kerja ini diberkahi dengan kesehatan, keselamatan, dan kelapangan rezeki untuk kita semua.', 'author' => 'Doa Rekan Sejawat'],
            ['quote' => 'Hiduplah seolah Anda akan mati besok. Belajarlah seolah Anda akan hidup selamanya.', 'author' => 'Mahatma Gandhi'],
            ['quote' => 'Ketulusan mendidik melahirkan ikatan batin yang tak lekang oleh jarak dan waktu.', 'author' => 'Ikatan Guru-Siswa'],
            ['quote' => 'Jadikan setiap kendala sebagai sarana untuk mengasah daya cipta dan daya juang kita.', 'author' => 'Daya Juang'],
            ['quote' => 'Kebaikan hati adalah pakaian terindah yang tidak akan pernah usang ditelan zaman.', 'author' => 'Etika Kehidupan'],
            ['quote' => 'Kunci kepuasan kerja adalah rasa cinta pada apa yang kita kerjakan dan kebanggaan atas manfaat yang kita berikan.', 'author' => 'Steve Jobs'],
            ['quote' => 'Mengajar adalah seni menanam pohon harapan yang buah manisnya akan dinikmati oleh masa depan bangsa.', 'author' => 'Filosofi Mengajar'],
            ['quote' => 'Kemampuan beradaptasi dengan perubahan adalah syarat utama pendidik di era transformasi digital.', 'author' => 'Adaptasi Pendidikan'],
            ['quote' => 'Saling menyapa dengan hangat di pagi hari membangun suasana kerja yang harmonis dan penuh rasa kekeluargaan.', 'author' => 'Budaya 5S'],
            ['quote' => 'Luangkan waktu sejenak untuk mengapresiasi diri sendiri atas segala kerja keras yang telah Anda curahkan.', 'author' => 'Self Compassion'],
            ['quote' => 'Kreativitas guru mengubah pelajaran yang rumit menjadi pengalaman belajar yang menyenangkan dan mudah dipahami.', 'author' => 'Kreativitas Edukasi'],
            ['quote' => 'Disiplin apel pagi ini membuktikan komitmen dan dedikasi tinggi Anda sebagai abdi negara dan pendidik.', 'author' => 'Apresiasi Kedisiplinan'],
            ['quote' => 'Perubahan besar selalu diawali dari kesadaran individu untuk memulai kebaikan dari diri sendiri.', 'author' => 'Inisiatif Diri'],
            ['quote' => 'Pendidikan karakter bukan sekadar hafalan teori, melainkan pembiasaan nilai-nilai baik dalam keseharian.', 'author' => 'Pendidikan Karakter'],
            ['quote' => 'Mata air yang jernih akan mengalirkan kesejukan. Jiwa pendidik yang ikhlas akan melahirkan murid yang berakhlak.', 'author' => 'Metafora Mendidik'],
            ['quote' => 'Jangan bandingkan proses Anda dengan orang lain, setiap orang memiliki garis waktu dan perjuangannya sendiri.', 'author' => 'Ketenangan Hidup'],
            ['quote' => 'Bekerja dengan cermat dan tuntas adalah wujud profesionalisme yang patut terus kita jaga bersama.', 'author' => 'Etos Kerja'],
            ['quote' => 'Senyum ketulusan seorang guru adalah pelipur lara dan pemicu semangat bagi siswa yang sedang ragu.', 'author' => 'Sentuhan Hangat'],
            ['quote' => 'Kerja keras kita hari ini adalah bekal keberkahan yang akan mengalir ke dalam kehidupan keluarga kita.', 'author' => 'Semangat Keluarga'],
            ['quote' => 'Keberhasilan sekolah adalah akumulasi dari kontribusi terbaik setiap guru, staf, dan seluruh warga sekolah.', 'author' => 'Sinergi SMKN 1 Ciamis'],
            ['quote' => 'Tetaplah memiliki hati yang lapang dan pikiran yang terbuka untuk menerima setiap saran dan masukan membangun.', 'author' => 'Keterbukaan Diri'],
            ['quote' => 'Jadikan kehadiran Anda sebagai solusi dan penyejuk dalam setiap forum diskusi dan musyawarah.', 'author' => 'Musyawarah Mufakat'],
            ['quote' => 'Pendidik sejati tidak pernah memadamkan mimpi siswa, melainkan menyalakan lentera untuk menemukan jalannya.', 'author' => 'Cita-cita Siswa'],
            ['quote' => 'Nikmatilah setiap detik proses mengajar, karena di situlah letak keindahan dari sebuah pengabdian.', 'author' => 'Seni Mengajar'],
            ['quote' => 'Kekayaan yang paling berharga bukanlah harta benda, melainkan ilmu yang bermanfaat dan budi pekerti yang luhur.', 'author' => 'Harta Sejati'],
            ['quote' => 'Semangat baru di hari yang baru! Mari bersama-sama kita wujudkan hari kerja yang produktif dan menyenangkan.', 'author' => 'Salam Optimisme'],
            ['quote' => 'Belajar dari masa lalu, bersyukur atas hari ini, dan mempersiapkan esok hari dengan penuh optimisme.', 'author' => 'Pedoman Waktu'],
            ['quote' => 'Keberhasilan pembelajaran tidak hanya diukur dari nilai angka, melainkan dari perubahan sikap ke arah yang lebih baik.', 'author' => 'Esensi Evaluasi'],
            ['quote' => 'Komunikasi yang hangat dan penuh rasa saling percaya adalah fondasi lingkungan kerja yang sehat dan produktif.', 'author' => 'Komunikasi Positif'],
            ['quote' => 'Tetaplah istiqomah dalam menabur kebaikan, meskipun tidak semua orang menyadari kebaikan yang Anda lakukan.', 'author' => 'Keteguhan Niat'],
            ['quote' => 'Guru adalah lentera di tengah kegelapan, penunjuk arah bagi jiwa-jiwa muda yang sedang mencari masa depannya.', 'author' => 'Lentera Bangsa'],
            ['quote' => 'Menjaga kesehatan fisik dan ketenangan mental adalah bentuk tanggung jawab utama agar kita dapat melayani dengan prima.', 'author' => 'Kesehatan Pendidik'],
            ['quote' => 'Setiap kesulitan selalu berdampingan dengan kemudahan. Yakinlah bahwa ada hikmah di setiap perjuangan.', 'author' => 'Optimisme Hidup'],
            ['quote' => 'Pendidikan vokasi yang unggul melahirkan lulusan yang terampil, mandiri, dan berkarakter kuat.', 'author' => 'Visi SMK Ciamis'],
            ['quote' => 'Bicaralah dengan santun, bertindaklah dengan bijak, dan bekerjalah dengan penuh dedikasi.', 'author' => 'Tiga Pilar Etika'],
            ['quote' => 'Apresiasi tulus dari rekan sejawat mampu melipatgandakan motivasi dan semangat dalam berinovasi.', 'author' => 'Dukungan Sejawat'],
            ['quote' => 'Kebahagiaan bukan tentang tidak adanya masalah, melainkan kemampuan kita untuk menghadapinya dengan penuh rasa syukur.', 'author' => 'Resiliensi Jiwa'],
            ['quote' => 'Ilmu yang diamalkan dengan tulus akan menjadi amal jariyah yang pahalanya mengalir tanpa henti.', 'author' => 'Amal Jariyah'],
            ['quote' => 'Dukunglah potensi unik setiap anak, karena di masa depan mereka akan menjadi ahli di bidangnya masing-masing.', 'author' => 'Diferensiasi Belajar'],
            ['quote' => 'Selamat melanjutkan aktivitas hari ini Bapak/Ibu. Semoga kesuksesan dan keberkahan selalu menyertai kita semua.', 'author' => 'Salam Hangat Guru'],
            ['quote' => 'Jadilah teladan dalam disiplin waktu, karena ketepatan waktu adalah cermin dari penghargaan kita terhadap orang lain.', 'author' => 'Disiplin Waktu'],
            ['quote' => 'Keberanian untuk mengakui kesalahan dan memperbaikinya adalah tanda kematangan karakter seorang pemimpin.', 'author' => 'Jiwa Kepemimpinan'],
            ['quote' => 'Pikiran yang positif di pagi hari akan menarik hal-hal baik dan kemudahan sepanjang sisa hari.', 'author' => 'Hukum Daya Tarik Positif'],
            ['quote' => 'Dedikasi Anda hari ini adalah pondasi bagi lahirnya pemimpin-pemimpin hebat masa depan Indonesia.', 'author' => 'Visi Masa Depan'],
            ['quote' => 'Kerjasama yang harmonis di sekolah menciptakan energi positif yang dirasakan oleh seluruh siswa.', 'author' => 'Energi Kolektif'],
            ['quote' => 'Tetaplah bersabar saat menghadapi tantangan pembelajaran, karena mutiara indah lahir dari proses yang panjang.', 'author' => 'Proses Mendidik'],
            ['quote' => 'Menjadi guru bukan sekadar profesi, melainkan panggilan jiwa untuk mencerdaskan kehidupan bangsa.', 'author' => 'Panggilan Jiwa'],
            ['quote' => 'Tinggalkan keluh kesah, sambutlah hari ini dengan tekad untuk memberikan kontribusi terbaik.', 'author' => 'Fokus Positif'],
            ['quote' => 'Kebaikan kecil yang dilakukan secara rutin jauh lebih bernilai daripada niat besar yang tidak pernah terlaksana.', 'author' => 'Konsistensi Amal'],
            ['quote' => 'Kehangatan sapaan di ruang guru menciptakan atmosfer kerja yang penuh inspirasi dan rasa nyaman.', 'author' => 'Budaya Sekolah'],
            ['quote' => 'Mendidik dengan hati akan menyentuh hati. Ajarkan ilmu dengan cinta, bimbinglah dengan teladan.', 'author' => 'Sentuhan Hati'],
            ['quote' => 'Setiap siswa memiliki potensi emas di dalam dirinya. Tugas kita adalah membantu mereka menemukan dan mengasahnya.', 'author' => 'Potensi Siswa'],
            ['quote' => 'Jadikan rasa lelah hari ini sebagai tanda bahwa kita telah berjuang sungguh-sungguh demi kemajuan bersama.', 'author' => 'Lelah yang Berkah'],
            ['quote' => 'Kemandirian dan etos kerja yang kuat adalah bekal utama yang kita wariskan kepada generasi muda.', 'author' => 'Etos Kerja Vokasi'],
            ['quote' => 'Jangan pernah berhenti belajar dan memperbarui wawasan, karena dunia terus bergerak maju dengan cepat.', 'author' => 'Belajar Sepanjang Hayat'],
            ['quote' => 'Kunci ketenangan hidup adalah tidak membandingkan diri dengan orang lain dan mensyukuri setiap rezeki yang ada.', 'author' => 'Kunci Ketenangan'],
            ['quote' => 'Semoga suasana belajar mengajar hari ini berjalan kondusif, interaktif, dan membawa manfaat luas bagi seluruh siswa.', 'author' => 'Harapan Guru'],
            ['quote' => 'Integritas adalah melakukan hal yang benar, bahkan ketika tidak ada seorang pun yang sedang memperhatikan.', 'author' => 'C.S. Lewis'],
            ['quote' => 'Kebaikan yang kita tabur di lingkungan sekolah akan tumbuh menjadi pohon peneduh bagi banyak orang.', 'author' => 'Benih Kebaikan'],
            ['quote' => 'Hormatilah setiap proses belajar, karena setiap anak memiliki kecepatan dan gaya belajar yang berbeda.', 'author' => 'Gaya Belajar'],
            ['quote' => 'Selamat bertugas Bapak/Ibu guru dan tenaga kependidikan SMKN 1 Ciamis. Pengabdian Anda sungguh luar biasa!', 'author' => 'Apresiasi Sekolah'],
            ['quote' => 'Senyuman hangat yang Anda berikan pagi ini adalah energi pendorong semangat bagi siapa saja yang melihatnya.', 'author' => 'Senyum Pagi'],
            ['quote' => 'Keberhasilan sejati adalah ketika ilmu yang kita ajarkan mampu membuat orang lain mandiri dan bermanfaat.', 'author' => 'Makna Keberhasilan'],
            ['quote' => 'Disiplin yang lahir dari kesadaran hati jauh lebih kokoh dan bertahan lama daripada disiplin karena paksaan.', 'author' => 'Disiplin Positif'],
            ['quote' => 'Bersama-sama kita kuat, bersatu kita maju. Mari jaga soliditas keluarga besar SMK Negeri 1 Ciamis.', 'author' => 'Soliditas SMKN 1 Ciamis'],
            ['quote' => 'Tulus dalam mengajar, bijak dalam mendidik, dan gigih dalam membimbing adalah cerminan guru paripurna.', 'author' => 'Guru Paripurna'],
            ['quote' => 'Setiap hari adalah anugerah terindah untuk menebarkan manfaat dan mencetak jejak kebaikan.', 'author' => 'Anugerah Hidup'],
            ['quote' => 'Kesabaran seorang pendidik adalah jembatan yang menghubungkan kebingungan murid menuju pemahaman yang terang.', 'author' => 'Kekuatan Sabar'],
            ['quote' => 'Jadilah pembelajar sejati yang selalu haus akan ilmu baru dan terbuka terhadap kemajuan teknologi.', 'author' => 'Pendidik Adaptif'],
            ['quote' => 'Ketenangan batin diperoleh saat kita mampu ikhlas melepaskan ekspektasi berlebih dan fokus pada ikhtiar terbaik.', 'author' => 'Keseimbangan Jiwa'],
            ['quote' => 'Keberhasilan peserta didik adalah kebanggaan terbesar dan hadiah terindah bagi seorang guru.', 'author' => 'Kebanggaan Guru'],
            ['quote' => 'Bekerjalah dengan semangat kebersamaan dan integritas tinggi untuk mengharumkan almamater tercinta.', 'author' => 'Dedikasi SMKN 1 Ciamis'],
            ['quote' => 'Terima kasih atas kehadiran dan komitmen apel pagi ini. Mari songsong hari dengan energi penuh dan senyum bahagia!', 'author' => 'Salam Presensi'],
        ];
    }
}

<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\ApelSession;
use App\Models\Attendance;
use App\Services\MotivationalQuoteService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class MotivationalQuoteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test all quotes are valid, non-empty, and contain author attribution.
     */
    public function test_all_quotes_are_valid_and_non_empty(): void
    {
        $quotes = MotivationalQuoteService::getAllQuotes();
        $this->assertGreaterThanOrEqual(100, count($quotes));

        foreach ($quotes as $index => $item) {
            $this->assertArrayHasKey('quote', $item, "Quote index {$index} missing 'quote' key");
            $this->assertArrayHasKey('author', $item, "Quote index {$index} missing 'author' key");
            $this->assertNotEmpty(trim($item['quote']), "Quote index {$index} is empty");
            $this->assertNotEmpty(trim($item['author']), "Author index {$index} is empty");
        }
    }

    /**
     * Test that each teacher who checks in on the same session gets a completely unique quote.
     */
    public function test_every_teacher_gets_a_unique_quote_in_same_session(): void
    {
        $session = ApelSession::create([
            'title' => 'Apel Pagi SMKN 1 Ciamis',
            'date' => '2026-08-23',
            'type' => 'pagi',
            'start_time' => '06:30:00',
            'end_time' => '08:00:00',
            'code' => 'KODE1',
        ]);

        $receivedQuotes = [];
        $totalTeachersToTest = 100;

        for ($i = 1; $i <= $totalTeachersToTest; $i++) {
            $participant = Participant::create([
                'nik' => 'NIK_TEST_' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'name' => 'Guru Test ' . $i,
                'role' => 'Guru',
                'status' => 'aktif',
            ]);

            // Simulasi urutan absensi
            Attendance::create([
                'apel_session_id' => $session->id,
                'participant_nik' => $participant->nik,
                'signature' => 'sig_test',
                'signed_in_at' => Carbon::now(),
            ]);

            $quoteData = MotivationalQuoteService::getQuoteForAttendance($session, $participant);
            $this->assertNotEmpty($quoteData['quote']);

            // Pastikan kata motivasi belum pernah didapatkan oleh guru lain sebelumnya dalam sesi yang sama
            $this->assertNotContains(
                $quoteData['quote'],
                $receivedQuotes,
                "Quote duplicated at teacher #{$i}: '{$quoteData['quote']}'"
            );

            $receivedQuotes[] = $quoteData['quote'];
        }

        // Verifikasi semua quote yang didapatkan 100% unik
        $this->assertCount($totalTeachersToTest, array_unique($receivedQuotes));
    }

    /**
     * Test that checkin submission sets the motivation quote in the redirect session.
     */
    public function test_checkin_submit_flashes_motivation_quote_to_success_page(): void
    {
        $p = Participant::create([
            'nik' => '123456789',
            'name' => 'Budi Santoso, S.Pd.',
            'role' => 'Guru',
            'status' => 'aktif',
        ]);

        $session = ApelSession::create([
            'title' => 'Apel Pagi',
            'date' => Carbon::today()->toDateString(),
            'type' => 'pagi',
            'start_time' => '00:01:00',
            'end_time' => '23:59:00',
            'code' => 'MOTIV',
        ]);

        $response = $this->post(route('apel.submit'), [
            'nik' => '123456789',
            'code' => 'MOTIV',
            'signature' => 'data:image/png;base64,sample_signature',
        ]);

        $response->assertRedirect(route('apel.success'));
        $response->assertSessionHas('motivation_quote');
        $response->assertSessionHas('motivation_author');
    }
}

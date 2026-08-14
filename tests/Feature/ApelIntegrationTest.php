<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\ApelSession;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class ApelIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test lookup endpoints.
     */
    public function test_api_participant_lookup_works_for_all_ids()
    {
        // 1. Create a participant with NIK, NIP and other ID
        $p = Participant::create([
            'nik' => '999111222',
            'nip' => '888111222',
            'other_id' => '777111222',
            'name' => 'Dr. Test Integration',
            'jabatan' => 'Guru',
            'role' => 'Guru',
            'status' => 'aktif'
        ]);

        // Test NIK lookup
        $res = $this->get('/api/participant/999111222');
        $res->assertStatus(200);
        $res->assertJsonPath('name', 'Dr. Test Integration');

        // Test NIP lookup
        $res = $this->get('/api/participant/888111222');
        $res->assertStatus(200);
        $res->assertJsonPath('name', 'Dr. Test Integration');

        // Test other_id lookup
        $res = $this->get('/api/participant/777111222');
        $res->assertStatus(200);
        $res->assertJsonPath('name', 'Dr. Test Integration');

        // Test non-existing ID
        $res = $this->get('/api/participant/000000000');
        $res->assertStatus(404);
    }

    /**
     * Test attendance check-in submission flow.
     */
    public function test_attendance_checkin_flow_accepts_all_ids_and_records_precise_location()
    {
        // 1. Create participant
        $p = Participant::create([
            'nik' => '999333444',
            'nip' => '888333444',
            'other_id' => '777333444',
            'name' => 'Absen Tester',
            'jabatan' => 'Guru',
            'role' => 'Guru',
            'status' => 'aktif'
        ]);

        // 2. Create open session for today
        $session = ApelSession::create([
            'title' => 'Test Session',
            'date' => Carbon::today(),
            'type' => 'pagi',
            'start_time' => '00:01:00',
            'end_time' => '23:59:00',
            'code' => 'TXYZ1'
        ]);

        // 3. Submit with incorrect ID
        $response = $this->post(route('apel.submit'), [
            'nik' => 'wrong_id',
            'code' => 'TXYZ1',
            'signature' => 'data:image/png;base64,sample',
            'latitude' => -7.32300000,
            'longitude' => 108.32600000,
            'location_name' => 'Sindangrasa, Ciamis'
        ]);
        $response->assertSessionHasErrors(['nik']);

        // 4. Submit with NIP (valid secondary)
        $response = $this->post(route('apel.submit'), [
            'nik' => '888333444', // NIP is sent in form's 'nik' field
            'code' => 'TXYZ1',
            'signature' => 'data:image/png;base64,sample',
            'latitude' => -7.32300000,
            'longitude' => 108.32600000,
            'location_name' => 'Sindangrasa, Ciamis'
        ]);

        // Assert check-in success and redirects to success route
        $response->assertRedirect(route('apel.success'));

        // Verify the database record contains the resolved primary NIK and precise location name
        $this->assertDatabaseHas('attendances', [
            'apel_session_id' => $session->id,
            'participant_nik' => '999333444', // Resolved to primary key NIK
            'location_name' => 'Sindangrasa, Ciamis',
            'latitude' => -7.32300000,
            'longitude' => 108.32600000,
        ]);
    }
}

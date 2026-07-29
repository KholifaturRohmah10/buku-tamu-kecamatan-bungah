<?php

namespace Tests\Feature;

use App\Models\KunjunganTamu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ValidatorKunjunganTamuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-06-03 08:00:00', 'Asia/Jakarta'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_validator_can_view_dashboard_and_update_status(): void
    {
        $validator = User::factory()->validator()->create();
        $guest = User::factory()->guest()->create();
        $entry = KunjunganTamu::query()->create([
            'user_id' => $guest->id,
            'name' => 'Mila Putri',
            'nomor_telepon' => '081234567890',
            'nik' => '3175091504900001',
            'tanggal_lahir' => '1990-04-15',
            'umur' => 36,
            'keperluan' => 'kk',
            'detail_keperluan' => 'Pengurusan KK baru.',
            'nilai_pelayanan' => 4,
            'nilai_kecepatan' => 4,
            'nilai_fasilitas' => 4,
            'saran' => 'Mohon tambah kursi tunggu.',
            'waktu_kunjungan' => Carbon::parse('2026-06-03 09:00:00', 'Asia/Jakarta'),
        ]);

        $dashboard = $this->actingAs($validator)->get(route('validator.dashboard'));

        $dashboard->assertOk();
        $dashboard->assertSee('Validasi apakah keperluan tamu sudah selesai atau belum');
        $dashboard->assertSee('Mila Putri');
        $dashboard->assertSee('name="status_selesai" value="1"', false);
        $dashboard->assertSee('name="status_selesai" value="0"', false);

        $response = $this->actingAs($validator)->patch(route('validator.kunjungan-tamu.status', $entry), [
            'status_selesai' => '1',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('kunjungan_tamu', [
            'id' => $entry->id,
            'status_selesai' => true,
            'id_validator' => $validator->id,
        ]);

        $validatedDashboard = $this->actingAs($validator)->get(route('validator.dashboard'));

        $validatedDashboard->assertOk();
        $validatedDashboard->assertDontSee('name="status_selesai" value="1"', false);
        $validatedDashboard->assertDontSee('name="status_selesai" value="0"', false);
        $validatedDashboard->assertSee('Tidak ada aksi');
    }

    public function test_guest_cannot_log_in_from_internal_login_form(): void
    {
        User::factory()->guest()->create([
            'email' => 'tamu@example.com',
            'password' => 'secret12345',
        ]);

        $response = $this->from(route('login'))->post(route('authenticate'), [
            'email' => 'tamu@example.com',
            'password' => 'secret12345',
            'form_context' => 'internal-login',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrorsIn('internalLogin', [
            'email' => 'Login internal hanya untuk admin atau validator.',
        ]);
        $this->assertGuest();
    }

    public function test_validator_can_still_validate_entry_that_is_marked_pending(): void
    {
        $validator = User::factory()->validator()->create();
        $guest = User::factory()->guest()->create();
        $entry = KunjunganTamu::query()->create([
            'user_id' => $guest->id,
            'name' => 'Mila Putri',
            'nomor_telepon' => '081234567890',
            'nik' => '3175091504900001',
            'tanggal_lahir' => '1990-04-15',
            'umur' => 36,
            'keperluan' => 'kk',
            'detail_keperluan' => 'Pengurusan KK baru.',
            'status_selesai' => false,
            'id_validator' => $validator->id,
            'waktu_divalidasi' => Carbon::parse('2026-06-03 09:30:00', 'Asia/Jakarta'),
            'waktu_kunjungan' => Carbon::parse('2026-06-03 09:00:00', 'Asia/Jakarta'),
        ]);

        $dashboard = $this->actingAs($validator)->get(route('validator.dashboard'));

        $dashboard->assertOk();
        $dashboard->assertSee('name="status_selesai" value="1"', false);
        $dashboard->assertSee('name="status_selesai" value="0"', false);

        $response = $this->actingAs($validator)->patch(route('validator.kunjungan-tamu.status', $entry), [
            'status_selesai' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('kunjungan_tamu', [
            'id' => $entry->id,
            'status_selesai' => true,
            'id_validator' => $validator->id,
        ]);
    }

    public function test_validator_cannot_change_status_after_entry_has_been_marked_completed(): void
    {
        $validator = User::factory()->validator()->create();
        $guest = User::factory()->guest()->create();
        $entry = KunjunganTamu::query()->create([
            'user_id' => $guest->id,
            'name' => 'Mila Putri',
            'nomor_telepon' => '081234567890',
            'nik' => '3175091504900001',
            'tanggal_lahir' => '1990-04-15',
            'umur' => 36,
            'keperluan' => 'kk',
            'detail_keperluan' => 'Pengurusan KK baru.',
            'status_selesai' => true,
            'id_validator' => $validator->id,
            'waktu_divalidasi' => Carbon::parse('2026-06-03 09:30:00', 'Asia/Jakarta'),
            'waktu_kunjungan' => Carbon::parse('2026-06-03 09:00:00', 'Asia/Jakarta'),
        ]);

        $response = $this->actingAs($validator)->patch(route('validator.kunjungan-tamu.status', $entry), [
            'status_selesai' => '0',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors([
            'validation_status' => 'Status keperluan tamu yang sudah selesai tidak dapat diubah lagi.',
        ]);

        $this->assertDatabaseHas('kunjungan_tamu', [
            'id' => $entry->id,
            'status_selesai' => true,
            'id_validator' => $validator->id,
            'waktu_divalidasi' => Carbon::parse('2026-06-03 09:30:00', 'Asia/Jakarta'),
        ]);
    }

    public function test_admin_cannot_access_validator_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('validator.dashboard'));

        $response->assertForbidden();
    }
}

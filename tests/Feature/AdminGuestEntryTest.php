<?php

namespace Tests\Feature;

use App\Models\KunjunganTamu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminKunjunganTamuTest extends TestCase
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

    public function test_login_page_can_be_opened(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('Kecamatan Bungah');
        $response->assertSee('Login Tamu');
        $response->assertSee('Daftar Tamu');
        $response->assertSee('data-open="false"', false);
        $response->assertDontSee('data-open="true"', false);
    }

    public function test_admin_can_log_in_and_view_kunjungan_tamu(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@example.com',
            'password' => 'secret12345',
        ]);

        $this->createKunjunganTamu([
            'name' => 'Siti Aminah',
            'waktu_kunjungan' => Carbon::parse('2026-06-01 10:00:00', 'Asia/Jakarta'),
        ]);

        $response = $this->post(route('authenticate'), [
            'email' => 'admin@example.com',
            'password' => 'secret12345',
            'form_context' => 'internal-login',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);

        $dashboard = $this->get(route('admin.dashboard'));

        $dashboard->assertOk();
        $dashboard->assertSee('Administrasi Buku Tamu Kecamatan');
        $dashboard->assertSee('Siti Aminah');
        $dashboard->assertSee('Export PDF');
        $dashboard->assertSee('Export Excel');
    }

    public function test_admin_cannot_log_in_from_guest_login_form(): void
    {
        User::factory()->admin()->create([
            'email' => 'admin@example.com',
            'password' => 'secret12345',
        ]);

        $response = $this->from(route('login'))->post(route('authenticate'), [
            'email' => 'admin@example.com',
            'password' => 'secret12345',
            'form_context' => 'guest-login',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrorsIn('guestLogin', [
            'email' => 'Login tamu hanya untuk akun tamu.',
        ]);
        $this->assertGuest();
    }

    public function test_admin_logout_redirects_to_login_page(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status', 'Anda telah keluar dari sistem.');
        $this->assertGuest();

        $loginpage = $this->get(route('login'));
        $loginpage->assertOk();
        $loginpage->assertSee('Admin / Validator');
        $loginpage->assertDontSee('data-open="true"', false);
    }

    public function test_tamu_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->guest()->create();

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertForbidden();
    }

    public function test_admin_can_update_and_delete_guest_entry(): void
    {
        $admin = User::factory()->admin()->create();
        $entry = $this->createKunjunganTamu([
            'name' => 'Rina Lama',
            'keperluan' => 'ktp',
        ]);

        $updateResponse = $this
            ->actingAs($admin)
            ->put(route('admin.kunjungan-tamu.update', $entry), [
                'name' => 'Rina Baru',
                'nomor_telepon' => '081234567890',
                'nik' => '3175091504900001',
                'keperluan' => 'domisili',
                'detail_keperluan' => 'Perlu pembaruan surat domisili.',
                'nilai_pelayanan' => 5,
                'nilai_kecepatan' => 4,
                'nilai_fasilitas' => 4,
                'saran' => 'Pelayanan sudah baik.',
                'waktu_kunjungan' => '2026-06-03T09:00',
            ]);

        $updateResponse->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('kunjungan_tamu', [
            'id' => $entry->id,
            'name' => 'Rina Baru',
            'keperluan' => 'domisili',
            'detail_keperluan' => 'Perlu pembaruan surat domisili.',
        ]);

        $deleteResponse = $this
            ->actingAs($admin)
            ->delete(route('admin.kunjungan-tamu.destroy', $entry));

        $deleteResponse->assertRedirect(route('admin.dashboard'));
        $this->assertDatabaseMissing('kunjungan_tamu', ['id' => $entry->id]);
    }

    public function test_admin_survey_update_is_applied_to_all_entries_of_same_guest(): void
    {
        $admin = User::factory()->admin()->create();
        $guest = User::factory()->guest()->create();

        $firstEntry = $this->createKunjunganTamu([
            'user_id' => $guest->id,
            'name' => 'Budi Santoso',
            'nilai_pelayanan' => 3,
            'nilai_kecepatan' => 3,
            'nilai_fasilitas' => 3,
            'saran' => 'Awal.',
        ]);

        $secondEntry = $this->createKunjunganTamu([
            'user_id' => $guest->id,
            'name' => 'Budi Santoso',
            'keperluan' => 'kk',
            'nilai_pelayanan' => 3,
            'nilai_kecepatan' => 3,
            'nilai_fasilitas' => 3,
            'saran' => 'Awal.',
        ]);

        $response = $this
            ->actingAs($admin)
            ->put(route('admin.kunjungan-tamu.update', $firstEntry), [
                'name' => 'Budi Santoso',
                'nomor_telepon' => '081234567890',
                'nik' => '3175091504900001',
                'keperluan' => 'ktp',
                'detail_keperluan' => 'Pengurusan dokumen.',
                'nilai_pelayanan' => 5,
                'nilai_kecepatan' => 4,
                'nilai_fasilitas' => 4,
                'saran' => 'Dikoreksi admin.',
                'waktu_kunjungan' => '2026-06-03T09:00',
            ]);

        $response->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('kunjungan_tamu', [
            'id' => $firstEntry->id,
            'nilai_pelayanan' => 5,
            'nilai_kecepatan' => 4,
            'nilai_fasilitas' => 4,
            'saran' => 'Dikoreksi admin.',
        ]);

        $this->assertDatabaseHas('kunjungan_tamu', [
            'id' => $secondEntry->id,
            'nilai_pelayanan' => 5,
            'nilai_kecepatan' => 4,
            'nilai_fasilitas' => 4,
            'saran' => 'Dikoreksi admin.',
        ]);
    }

    public function test_admin_can_print_report_by_month_range_and_status(): void
    {
        $admin = User::factory()->admin()->create();

        $this->createKunjunganTamu([
            'name' => 'Rina Juni',
            'status_selesai' => true,
            'waktu_kunjungan' => Carbon::parse('2026-06-12 09:15:00', 'Asia/Jakarta'),
        ]);

        $this->createKunjunganTamu([
            'name' => 'Andi Juli',
            'status_selesai' => false,
            'waktu_kunjungan' => Carbon::parse('2026-07-03 11:30:00', 'Asia/Jakarta'),
        ]);

        $response = $this
            ->actingAs($admin)
            ->get(route('admin.kunjungan-tamu.print', [
                'start_month' => '2026-06',
                'end_month' => '2026-06',
                'completion_status' => 'completed',
            ]));

        $response->assertOk();
        $response->assertSee('Laporan Buku Tamu dan Survei Kepuasan Masyarakat');
        $response->assertSee('Rina Juni');
        $response->assertDontSee('Andi Juli');
        $response->assertSee('Juni 2026 - Juni 2026');
    }

    public function test_admin_can_export_report_as_pdf(): void
    {
        $admin = User::factory()->admin()->create();

        $this->createKunjunganTamu([
            'name' => 'Rina Juni',
            'status_selesai' => true,
            'waktu_kunjungan' => Carbon::parse('2026-06-12 09:15:00', 'Asia/Jakarta'),
        ]);

        $this->createKunjunganTamu([
            'name' => 'Andi Juli',
            'status_selesai' => false,
            'waktu_kunjungan' => Carbon::parse('2026-07-03 11:30:00', 'Asia/Jakarta'),
        ]);

        $response = $this
            ->actingAs($admin)
            ->get(route('admin.kunjungan-tamu.export', [
                'format' => 'pdf',
                'start_month' => '2026-06',
                'end_month' => '2026-06',
                'completion_status' => 'completed',
            ]));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type') ?? '');
        $this->assertStringContainsString('.pdf', $response->headers->get('content-disposition') ?? '');
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition') ?? '');
    }

    public function test_admin_can_export_report_as_excel_csv(): void
    {
        $admin = User::factory()->admin()->create();

        $this->createKunjunganTamu([
            'name' => 'Rina Juni',
            'status_selesai' => true,
            'waktu_kunjungan' => Carbon::parse('2026-06-12 09:15:00', 'Asia/Jakarta'),
        ]);

        $this->createKunjunganTamu([
            'name' => 'Andi Juli',
            'status_selesai' => false,
            'waktu_kunjungan' => Carbon::parse('2026-07-03 11:30:00', 'Asia/Jakarta'),
        ]);

        $response = $this
            ->actingAs($admin)
            ->get(route('admin.kunjungan-tamu.export', [
                'format' => 'excel',
                'start_month' => '2026-06',
                'end_month' => '2026-06',
                'completion_status' => 'completed',
            ]));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type') ?? '');
        $this->assertStringContainsString('.csv', $response->headers->get('content-disposition') ?? '');

        $csvContent = $response->streamedContent();

        $this->assertStringContainsString('Laporan Buku Tamu dan Survei Kepuasan Masyarakat', $csvContent);
        $this->assertStringContainsString('Rina Juni', $csvContent);
        $this->assertStringNotContainsString('Andi Juli', $csvContent);
        $this->assertStringContainsString('Juni 2026 - Juni 2026', $csvContent);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createKunjunganTamu(array $attributes = []): KunjunganTamu
    {
        return KunjunganTamu::query()->create(array_merge([
            'user_id' => User::factory()->guest()->create()->id,
            'name' => 'Budi Santoso',
            'nomor_telepon' => '081234567890',
            'nik' => '3175091504900001',
            'tanggal_lahir' => '1990-04-15',
            'umur' => 36,
            'keperluan' => 'ktp',
            'detail_keperluan' => 'Pengurusan dokumen.',
            'nilai_pelayanan' => 4,
            'nilai_kecepatan' => 4,
            'nilai_fasilitas' => 5,
            'saran' => 'Sudah cukup baik.',
            'waktu_kunjungan' => Carbon::parse('2026-06-03 09:00:00', 'Asia/Jakarta'),
        ], $attributes));
    }
}

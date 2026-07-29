<?php

namespace Tests\Feature;

use App\Models\KunjunganTamu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class KunjunganTamuFlowTest extends TestCase
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

    public function test_welcome_page_can_be_opened(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Sistem Buku Tamu Kecamatan Bungah Gresik');
        $response->assertSee('Masuk ke Sistem');
        $response->assertSee('Tentang Bungah');
    }

    public function test_tamu_can_register_store_guest_entry_then_submit_survey(): void
    {
        $registerResponse = $this->post(route('register.store'), [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => 'secret12345',
            'password_confirmation' => 'secret12345',
        ]);

        $registerResponse->assertRedirect(route('kunjungan-tamu.index'));

        $user = User::query()->where('email', 'budi@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame(User::ROLE_TAMU, $user->role);

        $response = $this->post(route('kunjungan-tamu.store'), [
            'name' => 'Budi Santoso',
            'nomor_telepon' => '081234567890',
            'nik' => '3175091504900001',
            'keperluan' => 'ktp',
            'detail_keperluan' => 'Pembaruan KTP elektronik.',
        ]);

        $entry = KunjunganTamu::query()->where('id_petugas', $user->id)->latest('id')->firstOrFail();

        $response->assertRedirect(route('kunjungan-tamu.survey.create', $entry));
        $response->assertSessionHasNoErrors();

        $surveypage = $this->actingAs($user)->get(route('kunjungan-tamu.survey.create', $entry));

        $surveypage->assertOk();
        $surveypage->assertSee('Survei Kepuasan Masyarakat');
        $surveypage->assertSee('Progress Jawaban');
        $surveypage->assertSee('Arti Nilai 1 sampai 5');
        $surveypage->assertSee('Kembali');
        $surveypage->assertSee('Kirim Survey');

        $this->assertDatabaseHas('kunjungan_tamu', [
            'user_id' => $user->id,
            'name' => 'Budi Santoso',
            'nomor_telepon' => '081234567890',
            'nik' => '3175091504900001',
            'keperluan' => 'ktp',
            'detail_keperluan' => 'Pembaruan KTP elektronik.',
            'umur' => 36,
            'status_selesai' => false,
        ]);

        $surveyResponses = collect(KunjunganTamu::PERTANYAAN_SURVEI)
            ->mapWithKeys(fn (array $question): array => [$question['key'] => 5])
            ->all();

        $surveyResponse = $this->post(route('kunjungan-tamu.survey.store', $entry), [
            'responses' => $surveyResponses,
            'saran' => 'Pelayanan sudah sangat baik.',
        ]);

        $surveyResponse->assertRedirect(route('kunjungan-tamu.index'));
        $surveyResponse->assertSessionHasNoErrors();

        $this->assertDatabaseHas('kunjungan_tamu', [
            'id' => $entry->id,
            'nilai_pelayanan' => 5,
            'nilai_kecepatan' => 5,
            'nilai_fasilitas' => 5,
            'saran' => 'Pelayanan sudah sangat baik.',
        ]);

        $this->assertCount(count(KunjunganTamu::PERTANYAAN_SURVEI), $entry->fresh()->jawaban_survei);
        $this->assertNotNull($entry->fresh()->survey_waktu_dikirim);
    }

    public function test_tamu_only_needs_to_submit_survey_once(): void
    {
        $user = User::factory()->guest()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
        ]);

        $firstEntry = KunjunganTamu::query()->create([
            'user_id' => $user->id,
            'name' => 'Budi Santoso',
            'nomor_telepon' => '081234567890',
            'nik' => '3175091504900001',
            'tanggal_lahir' => '1990-04-15',
            'umur' => 36,
            'keperluan' => 'ktp',
            'detail_keperluan' => 'Pembaruan KTP elektronik.',
            'waktu_kunjungan' => Carbon::parse('2026-06-03 08:30:00', 'Asia/Jakarta'),
        ]);

        $surveyResponses = collect(KunjunganTamu::PERTANYAAN_SURVEI)
            ->mapWithKeys(fn (array $question): array => [$question['key'] => 4])
            ->all();

        $this->actingAs($user)->post(route('kunjungan-tamu.survey.store', $firstEntry), [
            'responses' => $surveyResponses,
            'saran' => 'Pelayanan cukup baik.',
        ])->assertRedirect(route('kunjungan-tamu.index'));

        $secondEntryResponse = $this->actingAs($user)->post(route('kunjungan-tamu.store'), [
            'name' => 'Budi Santoso',
            'nomor_telepon' => '081234567890',
            'nik' => '3175091504900001',
            'keperluan' => 'kk',
            'detail_keperluan' => 'Pengurusan kartu keluarga.',
        ]);

        $secondEntryResponse->assertRedirect(route('kunjungan-tamu.index'));
        $secondEntryResponse->assertSessionHas(
            'status',
            'Keperluan berhasil dikirim. Survei tamu sudah pernah diisi sebelumnya, jadi Anda tidak perlu mengulang survei.'
        );

        $secondEntry = KunjunganTamu::query()
            ->where('id_petugas', $user->id)
            ->where('keperluan', 'kk')
            ->latest('id')
            ->firstOrFail();

        $this->assertSame(4, $secondEntry->nilai_pelayanan);
        $this->assertSame(4, $secondEntry->nilai_kecepatan);
        $this->assertSame(4, $secondEntry->nilai_fasilitas);
        $this->assertSame('Pelayanan cukup baik.', $secondEntry->saran);
        $this->assertNotNull($secondEntry->survey_waktu_dikirim);

        $dashboard = $this->actingAs($user)->get(route('kunjungan-tamu.index'));

        $dashboard->assertOk();
        $dashboard->assertSee('Survey sudah tersimpan');
        $dashboard->assertSee('Pengajuan berikutnya tidak perlu mengisi survey ulang.');
    }

    public function test_tamu_logout_redirects_to_login_page(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_TAMU,
            'is_admin' => false,
        ]);

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status', 'Anda telah keluar dari sistem.');
        $this->assertGuest();

        $loginpage = $this->get(route('login'));

        $loginpage->assertOk();
        $loginpage->assertSee('Buka Form Tamu');
        $loginpage->assertDontSee('data-open="true"', false);
    }

    public function test_tamu_cannot_open_other_guest_survey_page(): void
    {
        $owner = User::factory()->guest()->create();
        $otherUser = User::factory()->guest()->create();
        $entry = KunjunganTamu::query()->create([
            'user_id' => $owner->id,
            'name' => 'Pemilik Data',
            'nomor_telepon' => '081234567890',
            'nik' => '3175091504900001',
            'tanggal_lahir' => '1990-04-15',
            'umur' => 36,
            'keperluan' => 'ktp',
            'detail_keperluan' => 'Pengajuan milik orang lain.',
            'waktu_kunjungan' => Carbon::parse('2026-06-03 09:00:00', 'Asia/Jakarta'),
        ]);

        $response = $this
            ->actingAs($otherUser)
            ->get(route('kunjungan-tamu.survey.create', $entry));

        $response->assertForbidden();
    }
}

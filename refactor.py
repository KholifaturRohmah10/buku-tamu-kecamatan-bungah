import os
import glob
import re

directories = [
    'app/',
    'resources/views/',
    'routes/',
    'tests/'
]

replacements = {
    'GuestEntry': 'KunjunganTamu',
    'guestEntry': 'kunjunganTamu',
    'guest_entries': 'kunjungan_tamu',
    'guest-entries': 'kunjungan-tamu',
    'GuestSurvey': 'SurveiTamu',
    'guestSurvey': 'surveiTamu',
    'guest_surveys': 'survei_tamu',
    'guest-surveys': 'survei-tamu',
    'StoreGuestEntryRequest': 'StoreKunjunganTamuRequest',
    'StoreGuestSurveyRequest': 'StoreSurveiTamuRequest',
    'UpdateGuestEntryRequest': 'UpdateKunjunganTamuRequest',
    'FilterGuestEntriesRequest': 'FilterKunjunganTamuRequest',
    'UpdateGuestEntryValidationRequest': 'UpdateValidasiKunjunganTamuRequest',
    'AdminGuestEntryController': 'AdminKunjunganTamuController',
    'GuestEntryController': 'KunjunganTamuController',
    'ValidatorGuestEntryController': 'ValidatorKunjunganTamuController',
    'SurveyReportController': 'LaporanSurveiController',
    'SurveyReportService': 'LayananLaporanSurvei',
    
    # Columns and properties
    'phone_number': 'nomor_telepon',
    'birth_date': 'tanggal_lahir',
    'age': 'umur',
    'purpose_detail': 'detail_keperluan',
    'purpose': 'keperluan',
    'is_completed': 'status_selesai',
    'validated_by_user_id': 'id_validator',
    'visited_at': 'waktu_kunjungan',
    'validated_at': 'waktu_divalidasi',
    'service_rating': 'nilai_pelayanan',
    'speed_rating': 'nilai_kecepatan',
    'facility_rating': 'nilai_fasilitas',
    'suggestion': 'saran',
    'critique': 'kritik',
    'survey_answers': 'jawaban_survei',
    'submitted_at': 'waktu_dikirim',
    'guest_survey_id': 'id_survei_tamu',
    'guest_entry_id': 'id_kunjungan_tamu',
    
    # Constants
    'PURPOSES': 'KEPERLUAN',
    'SURVEY_CATEGORIES': 'KATEGORI_SURVEI',
    'SURVEY_SUMMARY_GROUPS': 'GRUP_RINGKASAN_SURVEI',
    'SURVEY_SCORE_OPTIONS': 'OPSI_SKOR_SURVEI',
    'SURVEY_QUESTIONS': 'PERTANYAAN_SURVEI',
    
    # Relations
    'guestEntries': 'daftarKunjunganTamu',
    'guestSurveys': 'daftarSurveiTamu',
    'validatedGuestEntries': 'daftarKunjunganTamuDivalidasi',
}

# specific user_id replacements based on context
def custom_replacements(content):
    # In KunjunganTamu context, user_id is id_petugas
    # We can just replace 'user_id' -> 'id_petugas' where it refers to KunjunganTamu user_id
    # To be safe, we'll replace `$kunjunganTamu->user_id` with `$kunjunganTamu->id_petugas`
    content = content.replace("->user_id", "->id_petugas")
    content = content.replace("['user_id']", "['id_petugas']")
    content = content.replace("['user_id' =>", "['id_petugas' =>")
    content = content.replace("'user_id',", "'id_petugas',")
    # Actually, in SurveiTamu, it's id_pengguna, but let's just make it id_pengguna manually if we encounter it.
    # We replaced models manually already.
    return content

for d in directories:
    for root, dirs, files in os.walk(d):
        for file in files:
            if file.endswith('.php'):
                filepath = os.path.join(root, file)
                # Skip the new models and requests we already created
                if 'KunjunganTamu.php' in filepath or 'SurveiTamu.php' in filepath or 'Request.php' in filepath:
                    continue
                    
                with open(filepath, 'r', encoding='utf-8') as f:
                    content = f.read()
                    
                original = content
                
                # We need to sort keys by length to replace longer strings first 
                # (e.g. guest_entry_id before guest_entry)
                for k in sorted(replacements.keys(), key=len, reverse=True):
                    content = content.replace(k, replacements[k])
                
                content = custom_replacements(content)
                
                if content != original:
                    with open(filepath, 'w', encoding='utf-8') as f:
                        f.write(content)
                        
print("Done")

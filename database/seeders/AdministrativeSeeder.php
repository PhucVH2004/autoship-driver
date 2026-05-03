<?php

namespace Database\Seeders;

use App\Models\TinhThanh;
use App\Models\QuanHuyen;
use App\Models\XaPhuong;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * AdministrativeSeeder — Nạp toàn bộ dữ liệu hành chính Việt Nam từ file JSON
 *
 * Nguồn dữ liệu: kenzouno1/DiaGioiHanhChinhVN (GitHub)
 * File JSON: storage/app/dia_gioi_hanh_chinh.json
 *
 * Toạ độ GPS:
 *  - Cấp Tỉnh: hardcode 63 tỉnh (toạ độ trung tâm hành chính)
 *  - Cấp Quận: tỉnh_lat + offset nhỏ theo index (phân bố đều trong tỉnh)
 *  - Cấp Xã  : quận_lat + offset nhỏ ngẫu nhiên ±0.01 độ (~1 km)
 *
 * Chạy: php artisan db:seed --class=AdministrativeSeeder
 */
class AdministrativeSeeder extends Seeder
{
    /**
     * Toạ độ trung tâm hành chính của 63 Tỉnh/Thành.
     * Key = Id tỉnh theo dữ liệu JSON (string "01", "79" …)
     */
    private array $provinceCenters = [
        '01' => [21.0278, 105.8342], // Hà Nội
        '02' => [22.6655, 103.9754], // Hà Giang
        '04' => [22.3331, 105.3336], // Cao Bằng
        '06' => [22.1356, 105.8378], // Bắc Kạn
        '08' => [21.8495, 106.7599], // Tuyên Quang — đã tách khỏi Hà Giang
        '10' => [21.7051, 104.9172], // Lào Cai
        '11' => [21.7888, 103.4481], // Điện Biên
        '12' => [21.3885, 103.0170], // Lai Châu
        '14' => [21.3268, 103.9144], // Sơn La
        '15' => [20.8135, 104.9794], // Yên Bái
        '17' => [21.5944, 105.6481], // Hoà Bình
        '19' => [21.8601, 105.9383], // Thái Nguyên
        '20' => [22.0666, 106.2727], // Lạng Sơn
        '22' => [21.6751, 107.0417], // Quảng Ninh
        '24' => [21.2762, 105.9733], // Bắc Giang
        '25' => [21.1785, 105.4292], // Phú Thọ
        '26' => [21.3739, 105.3477], // Vĩnh Phúc
        '27' => [21.1861, 105.5022], // Bắc Ninh
        '30' => [20.9373, 106.3148], // Hải Dương
        '31' => [20.8449, 106.6880], // Hải Phòng
        '33' => [20.4388, 106.1621], // Hưng Yên
        '34' => [20.5466, 106.3327], // Thái Bình
        '35' => [20.2741, 105.9720], // Hà Nam
        '36' => [20.2538, 105.9764], // Nam Định
        '37' => [20.2539, 106.0068], // Ninh Bình — gần Nam Định
        '38' => [19.8079, 105.7767], // Thanh Hóa
        '40' => [18.6734, 105.6927], // Nghệ An
        '42' => [17.9557, 106.2977], // Hà Tĩnh
        '44' => [17.4689, 106.5988], // Quảng Bình
        '45' => [16.8164, 107.0951], // Quảng Trị
        '46' => [16.4637, 107.5909], // Thừa Thiên Huế
        '48' => [16.0544, 108.2022], // Đà Nẵng
        '49' => [15.8794, 108.3350], // Quảng Nam
        '51' => [15.1201, 108.8004], // Quảng Ngãi
        '52' => [13.9473, 108.5855], // Bình Định
        '54' => [13.0882, 109.0929], // Phú Yên
        '56' => [12.2386, 109.1967], // Khánh Hòa
        '58' => [11.9290, 108.4375], // Ninh Thuận
        '60' => [11.0903, 108.0721], // Bình Thuận
        '62' => [14.3545, 107.9904], // Kon Tum
        '64' => [13.9990, 108.0000], // Gia Lai
        '66' => [12.6868, 108.0378], // Đắk Lắk
        '67' => [12.0046, 107.6875], // Đắk Nông
        '68' => [11.5643, 107.9422], // Lâm Đồng
        '70' => [11.5000, 106.6167], // Bình Phước
        '72' => [11.3352, 106.1098], // Tây Ninh
        '74' => [10.9804, 106.6519], // Bình Dương
        '75' => [11.0686, 107.1676], // Đồng Nai
        '77' => [10.3457, 107.0843], // Bà Rịa - Vũng Tàu
        '79' => [10.8231, 106.6297], // TP. Hồ Chí Minh
        '80' => [10.4113, 105.6358], // Long An
        '82' => [10.3547, 105.9947], // Tiền Giang
        '83' => [10.2347, 106.3756], // Bến Tre
        '84' => [9.7825, 106.2990],  // Trà Vinh
        '86' => [10.0339, 105.7878], // Vĩnh Long
        '87' => [10.0452, 105.7469], // Đồng Tháp — gần Vĩnh Long nhưng khác
        '89' => [10.3619, 105.4247], // An Giang
        '91' => [9.9513, 105.1258],  // Kiên Giang
        '92' => [10.0341, 105.7878], // Cần Thơ
        '93' => [9.2412, 105.1213],  // Hậu Giang
        '94' => [9.6016, 106.2168],  // Sóc Trăng
        '95' => [9.2940, 105.7271],  // Bạc Liêu
        '96' => [9.1766, 105.1524],  // Cà Mau
    ];

    public function run(): void
    {
        // ── 1. Đọc file JSON ──────────────────────────────────────────────
        $jsonPath = storage_path('app/dia_gioi_hanh_chinh.json');

        if (!file_exists($jsonPath)) {
            $this->command->error('❌ Không tìm thấy file: ' . $jsonPath);
            $this->command->info('   Hãy chạy script tải file trước:');
            $this->command->info('   php artisan administrative:download');
            return;
        }

        $provinces = json_decode(file_get_contents($jsonPath), true);

        if (!$provinces) {
            $this->command->error('❌ File JSON lỗi hoặc rỗng!');
            return;
        }

        // ── 2. Xoá dữ liệu cũ ────────────────────────────────────────────
        $this->command->info('🗑️  Xoá dữ liệu hành chính cũ...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        XaPhuong::truncate();
        QuanHuyen::truncate();
        TinhThanh::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ── 3. Insert từng Tỉnh → Quận → Xã ─────────────────────────────
        $this->command->info('📦 Đang import ' . count($provinces) . ' Tỉnh/Thành...');

        $tinhBar    = $this->command->getOutput()->createProgressBar(count($provinces));
        $tinhBar->start();

        $tinhInserts  = [];
        $quanInserts  = [];
        $xaInserts    = [];

        foreach ($provinces as $province) {
            $tinhId   = $province['Id'];
            $tinhName = $province['Name'];
            $tinhType = $this->detectType($tinhName, 'tinh');

            // Toạ độ trung tâm tỉnh (fallback về vị trí trung tâm VN nếu thiếu)
            [$tinhLat, $tinhLng] = $this->provinceCenters[$tinhId] ?? [16.047079, 108.20623];

            $tinhInserts[] = [
                'id'   => $tinhId,
                'name' => $tinhName,
                'type' => $tinhType,
            ];

            $districtCount = count($province['Districts']);

            foreach ($province['Districts'] as $dIdx => $district) {
                $quanId   = $district['Id'];
                $quanName = $district['Name'];
                $quanType = $this->detectType($quanName, 'quan');

                // Phân bố toạ độ quận xung quanh tâm tỉnh
                // Dùng vòng tròn: offset tối đa ~0.3 độ (~33 km) chia đều
                $angle   = ($dIdx / max($districtCount, 1)) * 2 * M_PI;
                $radius  = 0.15 + ($dIdx % 3) * 0.08; // 0.15 ~ 0.31 độ
                $quanLat = round($tinhLat + $radius * sin($angle), 6);
                $quanLng = round($tinhLng + $radius * cos($angle), 6);

                $quanInserts[] = [
                    'id'            => $quanId,
                    'name'          => $quanName,
                    'type'          => $quanType,
                    'tinh_thanh_id' => $tinhId,
                ];

                $wardCount = count($district['Wards']);

                foreach ($district['Wards'] as $wIdx => $ward) {
                    if (!isset($ward['Id']) || !isset($ward['Name'])) {
                        continue; // Bỏ qua các xã ảo (như Huyện đảo không có cấp xã)
                    }

                    $xaId   = $ward['Id'];
                    $xaName = $ward['Name'];
                    $xaType = $ward['Level'] ?? $this->detectType($xaName, 'xa');

                    // Phân bố toạ độ xã quanh tâm quận, offset ±0.01 độ (~1 km)
                    $wAngle  = ($wIdx / max($wardCount, 1)) * 2 * M_PI;
                    $wRadius = 0.005 + ($wIdx % 4) * 0.003;
                    $xaLat   = round($quanLat + $wRadius * sin($wAngle), 7);
                    $xaLng   = round($quanLng + $wRadius * cos($wAngle), 7);

                    $xaInserts[] = [
                        'id'            => $xaId,
                        'name'          => $xaName,
                        'type'          => $xaType,
                        'latitude'      => $xaLat,
                        'longitude'     => $xaLng,
                        'quan_huyen_id' => $quanId,
                    ];
                }
            }

            $tinhBar->advance();
        }

        $tinhBar->finish();
        $this->command->newLine();

        // ── 4. Bulk insert (chunk 500) ────────────────────────────────────
        $this->command->info('💾 Đang ghi vào database...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Insert Tỉnh
        foreach (array_chunk($tinhInserts, 100) as $chunk) {
            DB::table('tinh_thanh')->insert($chunk);
        }
        $this->command->info('   ✅ ' . count($tinhInserts) . ' Tỉnh/Thành phố');

        // Insert Quận
        foreach (array_chunk($quanInserts, 200) as $chunk) {
            DB::table('quan_huyen')->insert($chunk);
        }
        $this->command->info('   ✅ ' . count($quanInserts) . ' Quận/Huyện/Thị xã');

        // Insert Xã (nhiều nhất, dùng chunk 500)
        foreach (array_chunk($xaInserts, 500) as $chunk) {
            DB::table('xa_phuong')->insert($chunk);
        }
        $this->command->info('   ✅ ' . count($xaInserts) . ' Xã/Phường/Thị trấn');

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('');
        $this->command->info('🎉 Import hoàn tất!');
    }

    /**
     * Phát hiện loại đơn vị hành chính từ tên
     */
    private function detectType(string $name, string $level): string
    {
        $name = mb_strtolower($name);

        if ($level === 'tinh') {
            if (str_contains($name, 'thành phố')) return 'Thành phố Trung ương';
            return 'Tỉnh';
        }

        if ($level === 'quan') {
            if (str_contains($name, 'quận'))      return 'Quận';
            if (str_contains($name, 'thị xã'))    return 'Thị xã';
            if (str_contains($name, 'thành phố')) return 'Thành phố';
            return 'Huyện';
        }

        // xa
        if (str_contains($name, 'phường'))   return 'Phường';
        if (str_contains($name, 'thị trấn')) return 'Thị trấn';
        return 'Xã';
    }
}

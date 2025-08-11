<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use Carbon\Carbon;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            // ✅ Platinum Customers - VIP khách hàng cao cấp
            [
                'name' => 'Nguyễn Văn Minh',
                'phone' => '0901234567',
                'email' => 'minh.nguyen@gmail.com',
                'birthday' => '1985-03-15',
                'gender' => 'male',
                'address' => '123 Nguyễn Huệ, Quận 1, TP.HCM',
                'notes' => 'Khách hàng VIP, thường mua số lượng lớn',
                'total_orders' => 45,
                'total_spent' => 75000000, // 75M VND
                'customer_tier' => 'platinum',
                'is_vip' => true,
                'created_at' => Carbon::now()->subMonths(18),
                'updated_at' => Carbon::now()->subDays(5),
            ],
            [
                'name' => 'Trần Thị Hương',
                'phone' => '0902345678',
                'email' => 'huong.tran@yahoo.com',
                'birthday' => '1978-11-22',
                'gender' => 'female',
                'address' => '456 Lê Lợi, Quận 3, TP.HCM',
                'notes' => 'Chủ cửa hàng thời trang, mua hàng định kỳ',
                'total_orders' => 52,
                'total_spent' => 82000000, // 82M VND
                'customer_tier' => 'platinum',
                'is_vip' => true,
                'created_at' => Carbon::now()->subMonths(24),
                'updated_at' => Carbon::now()->subDays(2),
            ],

            // ✅ Gold Customers - Khách hàng thân thiết
            [
                'name' => 'Lê Hoàng Nam',
                'phone' => '0903456789',
                'email' => 'nam.le@outlook.com',
                'birthday' => '1990-07-08',
                'gender' => 'male',
                'address' => '789 Hai Bà Trưng, Quận Tân Bình, TP.HCM',
                'notes' => 'Thích sản phẩm cao cấp, quan tâm đến chất lượng',
                'total_orders' => 28,
                'total_spent' => 35000000, // 35M VND
                'customer_tier' => 'gold',
                'is_vip' => false,
                'created_at' => Carbon::now()->subMonths(15),
                'updated_at' => Carbon::now()->subDays(7),
            ],
            [
                'name' => 'Phạm Thị Lan',
                'phone' => '0904567890',
                'email' => 'lan.pham@gmail.com',
                'birthday' => '1988-12-03',
                'gender' => 'female',
                'address' => '321 Nguyễn Trãi, Quận 5, TP.HCM',
                'notes' => 'Khách hàng lâu năm, rất tin tưởng sản phẩm',
                'total_orders' => 33,
                'total_spent' => 42000000, // 42M VND
                'customer_tier' => 'gold',
                'is_vip' => false,
                'created_at' => Carbon::now()->subMonths(20),
                'updated_at' => Carbon::now()->subDays(3),
            ],

            // ✅ Silver Customers - Khách hàng trung thành
            [
                'name' => 'Đặng Thị Mai',
                'phone' => '0906789012',
                'email' => 'mai.dang@yahoo.com',
                'birthday' => '1995-09-25',
                'gender' => 'female',
                'address' => '147 Điện Biên Phủ, Quận Bình Thạnh, TP.HCM',
                'notes' => 'Sinh viên, thích sản phẩm trendy',
                'total_orders' => 18,
                'total_spent' => 12500000, // 12.5M VND
                'customer_tier' => 'silver',
                'is_vip' => false,
                'created_at' => Carbon::now()->subMonths(8),
                'updated_at' => Carbon::now()->subDays(4),
            ],
            [
                'name' => 'Bùi Văn Hải',
                'phone' => '0907890123',
                'email' => 'hai.bui@gmail.com',
                'birthday' => '1987-01-12',
                'gender' => 'male',
                'address' => '258 Lý Thái Tổ, Quận 3, TP.HCM',
                'notes' => 'Kỹ sư IT, mua hàng online nhiều',
                'total_orders' => 15,
                'total_spent' => 9800000, // 9.8M VND
                'customer_tier' => 'silver',
                'is_vip' => false,
                'created_at' => Carbon::now()->subMonths(10),
                'updated_at' => Carbon::now()->subDays(6),
            ],

            // ✅ Bronze Customers - Khách hàng mới
            [
                'name' => 'Lý Văn Đức',
                'phone' => '0909012345',
                'email' => 'duc.ly@yahoo.com',
                'birthday' => '1996-08-14',
                'gender' => 'male',
                'address' => '741 Trân Hưng Đạo, Quận 1, TP.HCM',
                'notes' => 'Khách hàng mới, có tiềm năng phát triển',
                'total_orders' => 5,
                'total_spent' => 2800000, // 2.8M VND
                'customer_tier' => 'bronze',
                'is_vip' => false,
                'created_at' => Carbon::now()->subMonths(3),
                'updated_at' => Carbon::now()->subDays(12),
            ],

            // ✅ Customers without email
            [
                'name' => 'Trần Văn Tài',
                'phone' => '0912345678',
                'email' => null,
                'birthday' => null,
                'gender' => 'male',
                'address' => 'Quận 7, TP.HCM',
                'notes' => 'Khách vãng lai, không để lại thông tin chi tiết',
                'total_orders' => 2,
                'total_spent' => 800000, // 800K VND
                'customer_tier' => 'bronze',
                'is_vip' => false,
                'created_at' => Carbon::now()->subMonths(1),
                'updated_at' => Carbon::now()->subDays(20),
            ],
        ];

        // ✅ Seed customers với batch processing
        foreach ($customers as $customerData) {
            Customer::create($customerData);
        }

        // ✅ Generate additional random customers
        $this->generateRandomCustomers(20);
    }

    /**
     * Generate random customers for testing
     */
    private function generateRandomCustomers(int $count): void
    {
        $firstNames = [
            'male' => ['Minh', 'Nam', 'Tuấn', 'Hải', 'Đức', 'Khôi', 'Quang', 'Tài', 'Long', 'Hùng'],
            'female' => ['Hương', 'Lan', 'Mai', 'Nga', 'Bình', 'Xuân', 'Linh', 'Thu', 'Hoa', 'Yến'],
        ];

        $lastNames = ['Nguyễn', 'Trần', 'Lê', 'Phạm', 'Võ', 'Đặng', 'Bùi', 'Hoàng', 'Lý', 'Ngô', 'Phan', 'Đỗ'];

        $domains = ['gmail.com', 'yahoo.com', 'outlook.com', 'hotmail.com'];
        $districts = ['Quận 1', 'Quận 3', 'Quận 5', 'Quận 7', 'Quận 10', 'Tân Bình', 'Bình Thạnh', 'Thủ Đức'];

        for ($i = 0; $i < $count; $i++) {
            $gender = fake()->randomElement(['male', 'female']);
            $firstName = fake()->randomElement($firstNames[$gender]);
            $lastName = fake()->randomElement($lastNames);
            $fullName = $lastName . ' ' . $firstName;

            // Calculate tier based on total_spent
            $totalSpent = fake()->numberBetween(100000, 15000000);
            $tier = $this->calculateTier($totalSpent);
            $totalOrders = $this->calculateOrdersFromSpent($totalSpent);

            // ✅ SỬA: Safe date handling
            $birthdayDate = fake()->optional(0.7)->dateTimeBetween('-50 years', '-18 years');
            $birthday = $birthdayDate ? $birthdayDate->format('Y-m-d') : null;

            $customerData = [
                'name' => $fullName,
                'phone' => '09' . fake()->numerify('########'),
                'email' => fake()->optional(0.8)->safeEmail(), // 80% có email
                'birthday' => $birthday, // ✅ Đã được xử lý safe
                'gender' => $gender,
                'address' => fake()->optional(0.9)->streetAddress . ', ' . fake()->randomElement($districts) . ', TP.HCM',
                'notes' => fake()->optional(0.3)->sentence(),
                'total_orders' => $totalOrders,
                'total_spent' => $totalSpent,
                'customer_tier' => $tier,
                'is_vip' => $tier === 'platinum' && fake()->boolean(30), // 30% platinum customers are VIP
                'created_at' => fake()->dateTimeBetween('-2 years', 'now'),
                'updated_at' => fake()->dateTimeBetween('-1 month', 'now'),
            ];

            Customer::create($customerData);
        }
    }

    /**
     * Calculate customer tier based on total spent
     */
    private function calculateTier(int $totalSpent): string
    {
        if ($totalSpent >= 50000000) return 'platinum'; // >= 50M
        if ($totalSpent >= 20000000) return 'gold';     // >= 20M
        if ($totalSpent >= 5000000) return 'silver';    // >= 5M
        return 'bronze';                                 // < 5M
    }

    /**
     * Calculate approximate orders from total spent
     */
    private function calculateOrdersFromSpent(int $totalSpent): int
    {
        $avgOrderValue = fake()->numberBetween(300000, 800000); // 300K - 800K per order
        return max(1, intval($totalSpent / $avgOrderValue));
    }
}

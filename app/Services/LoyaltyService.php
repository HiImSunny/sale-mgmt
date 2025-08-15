<?php

namespace App\Services;

use App\Models\Customer;
use App\Services\SettingsService;

class LoyaltyService
{
    /**
     * Lấy hạng khách hàng dựa trên tổng chi tiêu
     */
    public static function getCustomerRank($totalSpent)
    {
        $platinumMin = SettingsService::get('loyalty.ranks.platinum_min_amount', 15000000);
        $goldMin = SettingsService::get('loyalty.ranks.gold_min_amount', 5000000);
        $silverMin = SettingsService::get('loyalty.ranks.silver_min_amount', 2000000);
        $bronzeMin = SettingsService::get('loyalty.ranks.bronze_min_amount', 500000);

        if ($totalSpent >= $platinumMin) return 'platinum';
        if ($totalSpent >= $goldMin) return 'gold';
        if ($totalSpent >= $silverMin) return 'silver';
        if ($totalSpent >= $bronzeMin) return 'bronze';
        return 'regular';
    }

    /**
     * Cập nhật hạng khách hàng trong database
     */
    public static function updateCustomerTier($customerId, $totalSpent)
    {
        $newTier = self::getCustomerRank($totalSpent);

        $customer = Customer::find($customerId);
        if ($customer && $customer->customer_tier !== $newTier) {
            $customer->update([
                'customer_tier' => $newTier,
                'total_spent' => $totalSpent
            ]);

            return [
                'updated' => true,
                'old_tier' => $customer->customer_tier,
                'new_tier' => $newTier
            ];
        }

        return ['updated' => false];
    }

    public static function calculateDiscountByRank($rank, $orderAmount)
    {
        $discountConfig = SettingsService::get("loyalty.discounts.{$rank}", ['type' => 'percent', 'value' => 0]);

        $type = $discountConfig['type'];
        $value = $discountConfig['value'];

        if ($type === 'percent') {
            return ($orderAmount * $value) / 100;
        } else {
            return $value;
        }
    }

    public static function calculateCustomerBenefits($customerId, $orderAmount)
    {
        $customer = Customer::find($customerId);
        $rank = $customer ? $customer->customer_tier : 'regular';

        $discount = self::calculateDiscountByRank($rank, $orderAmount);
        $points = self::calculateRewardPoints($orderAmount);

        return [
            'customer_id' => $customerId,
            'rank' => $rank,
            'rank_name' => self::getRankDisplayName($rank),
            'discount' => $discount,
            'points' => $points,
            'final_amount' => $orderAmount - $discount,
        ];
    }


    public static function calculateRewardPoints($orderAmount)
    {
        $rate = SettingsService::get('loyalty.rewards.points_rate', 100);
        return floor($orderAmount / 100000) * $rate;
    }

    public static function getRankDisplayName($rank)
    {
        $names = [
            'regular' => 'Khách hàng thường',
            'bronze' => 'Bronze',
            'silver' => 'Silver',
            'gold' => 'Gold',
            'platinum' => 'Platinum'
        ];

        return $names[$rank] ?? 'Không xác định';
    }

    public static function getRankIcon($rank)
    {
        $icons = [
            'regular' => 'fas fa-user',
            'bronze' => 'fas fa-medal',
            'silver' => 'fas fa-medal',
            'gold' => 'fas fa-medal',
            'platinum' => 'fas fa-crown'
        ];

        return $icons[$rank] ?? 'fas fa-user';
    }

    public static function getRankColor($rank)
    {
        $colors = [
            'regular' => '#6c757d',
            'bronze' => '#CD7F32',
            'silver' => '#C0C0C0',
            'gold' => '#FFD700',
            'platinum' => '#E5E4E2'
        ];

        return $colors[$rank] ?? '#6c757d';
    }
}

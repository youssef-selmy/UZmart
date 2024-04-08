<?php
declare(strict_types=1);

namespace App\Helpers;

use App\Models\Shop;
use App\Models\User;

class GetShop
{
    /**
     * @return Shop|null
     */
    public static function shop(): ?Shop
    {
        $shop = null;

        /** @var User $user */
        $user = auth('sanctum')->user();

        if (isset($user->shop)) {
            $shop = $user->shop;
        } else if (isset($user->moderatorShop) && ($user->role == 'moderator' || $user->role == 'deliveryman')) {
            $shop = $user->moderatorShop;
        }

        return $shop;
    }
}

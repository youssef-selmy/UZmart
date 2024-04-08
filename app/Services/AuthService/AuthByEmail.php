<?php
declare(strict_types=1);

namespace App\Services\AuthService;

use App\Events\Mails\SendEmailVerification;
use App\Models\User;
use App\Services\CoreService;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;

class AuthByEmail extends CoreService
{
    /**
     * @return string
     */
    protected function getModelClass(): string
    {
        return User::class;
    }

    public function authentication(array $array): JsonResponse
    {
        /** @var User $user */

        $user = $this->model()
            
            ->updateOrCreate([
                'email'         => data_get($array, 'email')
            ], [
                'firstname'     => data_get($array, 'email', data_get($array, 'email')),
                'email'         => data_get($array, 'email'),
                'ip_address'    => request()->ip(),
        ]);

        if (!$user->hasAnyRole(Role::query()->pluck('name')->toArray())) {
            $user->syncRoles('user');
        }

        event((new SendEmailVerification($user)));

        return $this->successResponse('User send email', []);
    }

}

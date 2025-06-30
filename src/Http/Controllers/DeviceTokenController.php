<?php

namespace Botble\Api\Http\Controllers;

use Botble\Api\Http\Requests\DeviceTokenRequest;
use Botble\Api\Models\DeviceToken;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Exception;
use Illuminate\Http\Request;

class DeviceTokenController extends BaseApiController
{
    /**
     * Register or update device token
     *
     * Register a new device token or update an existing one for push notifications.
     *
     * @bodyParam token string required The FCM device token.
     * @bodyParam platform string The device platform (android, ios).
     * @bodyParam app_version string The app version.
     * @bodyParam device_id string The unique device identifier.
     * @bodyParam user_type string The user type (customer, admin).
     * @bodyParam user_id integer The user ID.
     *
     * @group Device Tokens
     */
    public function store(DeviceTokenRequest $request, BaseHttpResponse $response)
    {
        try {
            $deviceToken = DeviceToken::createOrUpdateToken($request->validated());

            return $response
                ->setData([
                    'id' => $deviceToken->id,
                    'token' => $deviceToken->token,
                    'platform' => $deviceToken->platform,
                    'is_active' => $deviceToken->is_active,
                    'created_at' => $deviceToken->created_at,
                    'updated_at' => $deviceToken->updated_at,
                ])
                ->setMessage('Device token registered successfully');
        } catch (Exception $e) {
            return $response
                ->setError()
                ->setMessage('Failed to register device token: ' . $e->getMessage())
                ->setCode(500);
        }
    }

    /**
     * Get user's device tokens
     *
     * Retrieve all device tokens for the authenticated user.
     *
     * @group Device Tokens
     */
    public function index(Request $request, BaseHttpResponse $response)
    {
        $user = $request->user();

        if (! $user) {
            return $response
                ->setError()
                ->setMessage('Unauthorized')
                ->setCode(401);
        }

        $userType = $this->getUserType($user);

        $tokens = DeviceToken::active()
            ->forUser($userType, $user->id)
            ->select(['id', 'token', 'platform', 'app_version', 'device_id', 'last_used_at', 'created_at'])
            ->orderBy('last_used_at', 'desc')
            ->get();

        return $response->setData($tokens);
    }

    /**
     * Update device token
     *
     * Update an existing device token.
     *
     * @bodyParam platform string The device platform (android, ios).
     * @bodyParam app_version string The app version.
     * @bodyParam device_id string The unique device identifier.
     *
     * @group Device Tokens
     */
    public function update(Request $request, int|string $id, BaseHttpResponse $response)
    {
        $user = $request->user();

        if (! $user) {
            return $response
                ->setError()
                ->setMessage('Unauthorized')
                ->setCode(401);
        }

        $userType = $this->getUserType($user);

        $deviceToken = DeviceToken::active()
            ->forUser($userType, $user->id)
            ->find($id);

        if (! $deviceToken) {
            return $response
                ->setError()
                ->setMessage('Device token not found')
                ->setCode(404);
        }

        $deviceToken->update($request->only(['platform', 'app_version', 'device_id']));
        $deviceToken->markAsUsed();

        return $response
            ->setData([
                'id' => $deviceToken->id,
                'token' => $deviceToken->token,
                'platform' => $deviceToken->platform,
                'app_version' => $deviceToken->app_version,
                'device_id' => $deviceToken->device_id,
                'is_active' => $deviceToken->is_active,
                'last_used_at' => $deviceToken->last_used_at,
                'updated_at' => $deviceToken->updated_at,
            ])
            ->setMessage('Device token updated successfully');
    }

    /**
     * Delete device token
     *
     * Delete a device token to stop receiving push notifications.
     *
     * @group Device Tokens
     */
    public function destroy(Request $request, int|string $id, BaseHttpResponse $response)
    {
        $user = $request->user();

        if (! $user) {
            return $response
                ->setError()
                ->setMessage('Unauthorized')
                ->setCode(401);
        }

        $userType = $this->getUserType($user);

        $deviceToken = DeviceToken::forUser($userType, $user->id)->find($id);

        if (! $deviceToken) {
            return $response
                ->setError()
                ->setMessage('Device token not found')
                ->setCode(404);
        }

        $deviceToken->delete();

        return $response->setMessage('Device token deleted successfully');
    }

    /**
     * Delete device token by token value
     *
     * Delete a device token using the token value.
     *
     * @bodyParam token string required The FCM device token to delete.
     *
     * @group Device Tokens
     */
    public function destroyByToken(Request $request, BaseHttpResponse $response)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $user = $request->user();

        if (! $user) {
            return $response
                ->setError()
                ->setMessage('Unauthorized')
                ->setCode(401);
        }

        $userType = $this->getUserType($user);

        $deviceToken = DeviceToken::query()->where('token', $request->input('token'))
            ->forUser($userType, $user->id)
            ->first();

        if (! $deviceToken) {
            return $response
                ->setError()
                ->setMessage('Device token not found')
                ->setCode(404);
        }

        $deviceToken->delete();

        return $response->setMessage('Device token deleted successfully');
    }

    /**
     * Deactivate device token
     *
     * Deactivate a device token without deleting it.
     *
     * @group Device Tokens
     */
    public function deactivate(Request $request, int|string $id, BaseHttpResponse $response)
    {
        $user = $request->user();

        if (! $user) {
            return $response
                ->setError()
                ->setMessage('Unauthorized')
                ->setCode(401);
        }

        $userType = $this->getUserType($user);

        $deviceToken = DeviceToken::forUser($userType, $user->id)->find($id);

        if (! $deviceToken) {
            return $response
                ->setError()
                ->setMessage('Device token not found')
                ->setCode(404);
        }

        $deviceToken->deactivate();

        return $response->setMessage('Device token deactivated successfully');
    }

    protected function getUserType($user): string
    {
        // Determine user type based on the model class
        $class = get_class($user);

        if (str_contains($class, 'Customer')) {
            return 'customer';
        }

        if (str_contains($class, 'User')) {
            return 'admin';
        }

        return 'user';
    }
}

<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Table\TrustedDevicesTable;
use App\Model\Table\TwoFactorCodesTable;
use DateTime;

/**
 * Service for handling two-factor authentication codes and trusted devices.
 *
 * This service provides methods to generate and verify one-time codes stored in the database,
 * as well as to manage trusted devices for bypassing two-factor prompts.
 */
class TwoFactorService
{
    /**
     * @var \App\Model\Table\TwoFactorCodesTable
     */
    protected TwoFactorCodesTable $CodesTable;

    /**
     * @var \App\Model\Table\TrustedDevicesTable
     */
    protected TrustedDevicesTable $DevicesTable;

    /**
     * Constructor.
     *
     * Initializes the table locators for two-factor codes and trusted devices.
     */
    public function __construct(TwoFactorCodesTable $codesTable, TrustedDevicesTable $devicesTable)
    {
        $this->CodesTable = $codesTable;
        $this->DevicesTable = $devicesTable;
    }

    /**
     * Generates a 6-digit verification code for a user and stores it with a 10-minute expiry.
     *
     * @param string $userId The ID of the user.
     * @return string The generated 6-digit verification code.
     * @throws \Random\RandomException
     */
    public function generateCode(string $userId): string
    {
        $code = sprintf('%06d', random_int(0, 999999));
        $expires = new DateTime('+10 minutes');

        $entity = $this->CodesTable->newEntity([
            'user_id' => $userId,
            'code' => $code,
            'expires' => $expires,
        ]);
        $this->CodesTable->save($entity);

        return $code;
    }

    /**
     * Verifies a code for a user. Returns true if a matching, unexpired code exists, and deletes it.
     *
     * @param string $userId The ID of the user.
     * @param string $code The verification code to check.
     * @return bool True if the code is valid and not expired; otherwise, false.
     */
    public function verifyCode(string $userId, string $code): bool
    {
        $now = new DateTime();
        $record = $this->CodesTable->find()
            ->where([
                'user_id' => $userId,
                'code' => $code,
                'expires >=' => $now,
            ])
            ->first();

        if (!$record) {
            return false;
        }

        $this->CodesTable->delete($record);

        return true;
    }

    /**
     * Determines whether two-factor authentication should be required for a user on this device.
     *
     * @param string $userId The ID of the user.
     * @param string|null $deviceId The identifier of the device (optional).
     * @return bool True if 2FA is required; false if the device is trusted.
     */
    public function shouldRequire2FA(string $userId, ?string $deviceId = null): bool
    {
        if ($deviceId && $this->isTrustedDevice($userId, $deviceId)) {
            return false;
        }

        return true;
    }

    /**
     * Checks if a given device is trusted for a user.
     *
     * @param string $userId The ID of the user.
     * @param string $deviceId The identifier of the device.
     * @return bool True if the device is trusted and not expired; otherwise, false.
     */
    public function isTrustedDevice(string $userId, string $deviceId): bool
    {
        $now = new DateTime();

        return (bool)$this->DevicesTable->find()
            ->where([
                'user_id' => $userId,
                'device_id' => $deviceId,
                'expires >=' => $now,
            ])
            ->count();
    }

    /**
     * Adds a device to the trusted devices list for a user for 30 days.
     *
     * @param string $userId The ID of the user.
     * @param string $deviceId The identifier of the device.
     * @return void
     */
    public function addTrustedDevice(string $userId, string $deviceId): void
    {
        $expires = new DateTime('+30 days');

        $entity = $this->DevicesTable->newEntity([
            'user_id' => $userId,
            'device_id' => $deviceId,
            'expires' => $expires,
        ]);
        $this->DevicesTable->save($entity);
    }
}

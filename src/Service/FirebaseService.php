<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Core\Configure;
use Cake\Log\Log;
use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Factory;
use RuntimeException;

/**
 * Firebase Authentication Service
 *
 * Handles Firebase authentication operations including email verification
 * and 2FA functionality.
 */
class FirebaseService
{
    /**
     * Firebase Authentication instance
     *
     * @var \Kreait\Firebase\Contract\Auth
     */
    private Auth $Auth;

    /**
     * Constructor
     *
     * Initialize Firebase Authentication with service account credentials
     */
    public function __construct()
    {
        $credentialsFile = CONFIG . 'firebase-credentials.json';
        if (!is_readable($credentialsFile)) {
            throw new RuntimeException("Firebase credentials file missing or unreadable at: $credentialsFile");
        }

        $credentials = file_get_contents($credentialsFile);
        if (empty($credentials)) {
            throw new RuntimeException('Firebase credentials file is empty.');
        }
        json_decode($credentials, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Invalid JSON in Firebase credentials file: ' . json_last_error_msg());
        }

        $factory = (new Factory())
            ->withServiceAccount($credentialsFile)
            ->withDatabaseUri(Configure::read('Firebase.databaseUrl'));

        $this->Auth = $factory->createAuth();
    }

    /**
     * Generate and send a verification code via email
     *
     * @param string $email User email address
     * @return string The generated verification code
     */
    public function sendVerificationCode(string $email): string
    {
        $code = sprintf('%06d', mt_rand(0, 999999));
        try {
            $user = $this->Auth->getUserByEmail($email);
        } catch (UserNotFound $e) {
            // Create user if not exists
            $user = $this->Auth->createUser([
                'email' => $email,
                'emailVerified' => false,
            ]);
        }

        $expiry = time() + 600;

        $claims = [
            'verificationCode' => $code,
            'verificationCodeExpiry' => $expiry,
        ];
        $this->Auth->setCustomUserClaims($user->uid, $claims);

        return $code;
    }

    /**
     * Verify the code provided by the user during 2FA
     *
     * @param string $email User email address
     * @param string $code Verification code to check
     * @return bool True if code is valid, false otherwise
     */
    public function verifyCode(string $email, string $code): bool
    {
        $code = preg_replace('/\D+/', '', $code);

        try {
            $user = $this->Auth->getUserByEmail($email);
        } catch (UserNotFound) {
            return false;
        }

        $attrs = $user->customAttributes ?? [];
        $stored = $attrs['verificationCode'] ?? null;
        $expiry = isset($attrs['verificationCodeExpiry'])
            ? (int)$attrs['verificationCodeExpiry']
            : 0;

        if ($stored === $code && time() < $expiry) {
            // Clear the code so it can't be reused
            $this->Auth->setCustomUserClaims($user->uid, [
                'verificationCode' => null,
                'verificationCodeExpiry' => null,
                'lastVerifiedLogin' => time(),
            ]);

            return true;
        }

        return false;
    }

    /**
     * Check if a device is trusted based on previous successful logins
     *
     * @param string $email User email address
     * @param string $deviceId Unique identifier for the device
     * @return bool True if device is trusted, false otherwise
     */
    public function isTrustedDevice(string $email, string $deviceId): bool
    {
        try {
            $user = $this->Auth->getUserByEmail($email);
            $attrs = $user->customAttributes ?? [];
            $devices = $attrs['trustedDevices'] ?? [];
        } catch (UserNotFound) {
            return false;
        }

        $now = time();
        $updated = [];
        $trusted = false;
        foreach ($devices as $device) {
            if (is_array($device) && isset($device['id'], $device['expires'])) {
                if ($device['id'] === $deviceId && $device['expires'] > $now) {
                    $trusted = true;
                }
                if ($device['expires'] > $now) {
                    $updated[] = $device;
                }
            }
        }

        if (count($updated) !== count($devices)) {
            // clean up expired entries
            $this->Auth->setCustomUserClaims($user->uid, ['trustedDevices' => $updated]);
        }

        return $trusted;
    }

    /**
     * Add a device to the user's trusted devices list
     *
     * @param string $email User email address
     * @param string $deviceId Unique identifier for the device
     * @return bool Success status
     */
    public function addTrustedDevice(string $email, string $deviceId): bool
    {
        try {
            $user = $this->Auth->getUserByEmail($email);
            $attrs = $user->customAttributes ?? [];
            $devices = $attrs['trustedDevices'] ?? [];
        } catch (UserNotFound) {
            return false;
        }

        $now = time();
        // Remove expired and enforce max 5
        $devices = array_filter($devices, function ($d) use ($now) {
            return is_array($d) && !empty($d['expires']) && $d['expires'] > $now;
        });
        if (!in_array($deviceId, array_column($devices, 'id'))) {
            if (count($devices) >= 5) {
                array_shift($devices);
            }
            $devices[] = ['id' => $deviceId, 'added' => $now, 'expires' => $now + 30 * 24 * 3600];
        }

        $this->Auth->setCustomUserClaims($user->uid, ['trustedDevices' => $devices]);

        return true;
    }

    /**
     * Remove a device from the user's trusted devices list
     *
     * @param string $email User email address
     * @param string $deviceId Unique identifier for the device
     * @return bool Success status
     */
    public function removeTrustedDevice(string $email, string $deviceId): bool
    {
        try {
            $user = $this->Auth->getUserByEmail($email);
            $attrs = $user->customAttributes ?? [];
            $devices = $attrs['trustedDevices'] ?? [];
        } catch (UserNotFound) {
            return false;
        }

        $devices = array_filter($devices, fn($d)=> !($d['id'] ?? '') === $deviceId);
        $this->Auth->setCustomUserClaims($user->uid, ['trustedDevices'=>$devices]);
        return true;
    }

    /**
     * Check if 2FA should be required based on risk assessment
     *
     * @param string $email User email address
     * @param array $requestData Additional data about the request for risk assessment
     * @return bool True if 2FA is required, false otherwise
     */
    public function shouldRequire2FA(string $email, array $requestData = []): bool
    {
        if (!empty($requestData['deviceId']) && $this->isTrustedDevice($email, $requestData['deviceId'])) {
            return false;
        }

        try {
            $user = $this->Auth->getUserByEmail($email);
            $attrs = $user->customAttributes ?? [];

            if (empty($attrs['lastVerifiedLogin'])) {
                return true;
            }

            $risk = 0;
            if (
                !empty($requestData['ip'])
                && !empty($attrs['lastIpAddress'])
                && $requestData['ip'] !== $attrs['lastIpAddress']
            ) {
                $risk++;
            }
            if (!empty($requestData['time'])) {
                $now = (int)$requestData['time'];
                $last = !empty($attrs['lastLoginTime'])
                    ? (int)$attrs['lastLoginTime']
                    : 0;
                $hourDiff = abs((int)date('H', $now) - (int)date('H', $last));
                if ($hourDiff > 6) {
                    $risk++;
                }
            }

            return $risk > 0;
        } catch (UserNotFound) {
            return true;
        }
    }

    /**
     * Update user login metadata for risk assessment
     *
     * @param string $email User email address
     * @param array $requestData Login request data
     * @return void
     */
    public function updateLoginMetadata(string $email, array $requestData = []): void
    {
        try {
            $user = $this->Auth->getUserByEmail($email);
            $data = ['lastLoginTime' => time()];
            if (!empty($requestData['ip'])) {
                $data['lastIpAddress'] = $requestData['ip'];
            }
            $this->Auth->setCustomUserClaims($user->uid, $data);
        } catch (UserNotFound) {
        }
    }
}

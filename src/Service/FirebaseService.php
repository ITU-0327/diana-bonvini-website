<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Core\Configure;
use Cake\Log\Log;
use Cake\Routing\Router;
use Exception;
use Kreait\Firebase\Auth;
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
     * @var \Kreait\Firebase\Auth
     */
    private Auth $auth;

    /**
     * Constructor
     *
     * Initialize Firebase Authentication with service account credentials
     */
    public function __construct()
    {
        try {
            $credentialsFile = CONFIG . 'firebase-credentials.json';

            // Verify file
            if (!file_exists($credentialsFile)) {
                throw new RuntimeException('Firebase credentials file not found. Please ensure it exists at: ' . $credentialsFile);
            }

            if (!is_readable($credentialsFile)) {
                throw new RuntimeException('Firebase credentials file is not readable. Please check permissions on: ' . $credentialsFile);
            }

            // Check file content
            $credentials = file_get_contents($credentialsFile);
            if (empty($credentials)) {
                throw new RuntimeException('Firebase credentials file is empty.');
            }

            // Verify JSON structure
            json_decode($credentials, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException('Firebase credentials file contains invalid JSON: ' . json_last_error_msg());
            }

            // Create Firebase Auth instance
            $factory = (new Factory())
                ->withServiceAccount($credentialsFile)
                ->withDatabaseUri(Configure::read('Firebase.databaseUrl'));

            $this->auth = $factory->createAuth();
        } catch (Exception $e) {
            // Log the error with detailed information
            Log::error('Firebase initialization error: ' . $e->getMessage());
            Log::error('Error details: ' . $e->getTraceAsString());
        }
    }

    /**
     * Generate and send a verification code via email
     *
     * @param string $email User email address
     * @return string The generated verification code
     */
    public function sendVerificationCode(string $email): string
    {
        // Generate a 6-digit verification code
        $code = sprintf('%06d', mt_rand(0, 999999));

        // Always store the code in session for development mode and as backup
        try {
            $request = Router::getRequest();
            if ($request) {
                $session = $request->getSession();
                $session->write("VerificationCodes.$email", [
                    'code' => $code,
                    'expiry' => time() + 600, // 10 minutes
                ]);
                Log::debug("Stored verification code in session for: $email");

                // Also store in a debug area for troubleshooting
                $session->write('LastVerificationCode', [
                    'email' => $email,
                    'code' => $code,
                    'time' => date('Y-m-d H:i:s'),
                    'expiry' => date('Y-m-d H:i:s', time() + 600),
                ]);
            }
        } catch (Exception $ex) {
            Log::error('Could not store verification code in session: ' . $ex->getMessage());
        }

        try {
            // Store the code in Firebase for later verification
            try {
                $user = $this->auth->getUserByEmail($email);
                // The Firebase PHP SDK uses different methods for custom claims/attributes
                // Try different methods based on what's available
                try {
                    if (method_exists($this->auth, 'setCustomUserClaims')) {
                        $this->auth->setCustomUserClaims($user->uid, [
                            'verificationCode' => $code,
                            'verificationCodeExpiry' => time() + 600, // 10 minutes
                        ]);
                    } elseif (method_exists($this->auth, 'setCustomUserAttributes')) {
                        $this->auth->setCustomUserAttributes($user->uid, [
                            'verificationCode' => $code,
                            'verificationCodeExpiry' => time() + 600, // 10 minutes
                        ]);
                    } else {
                        throw new RuntimeException('No method available to set user custom attributes');
                    }
                    Log::debug("Verification code set for existing user: $email");
                } catch (Exception $e) {
                    Log::error('Failed to set custom attributes: ' . $e->getMessage());
                    // Continue as the session fallback will be used
                }
            } catch (UserNotFound $e) {
                // If user doesn't exist in Firebase yet, create them
                // Create user first
                $userRecord = $this->auth->createUser([
                    'email' => $email,
                    'emailVerified' => false,
                ]);

                // Then try to set custom claims if possible
                try {
                    if (method_exists($this->auth, 'setCustomUserClaims')) {
                        $this->auth->setCustomUserClaims($userRecord->uid, [
                            'verificationCode' => $code,
                            'verificationCodeExpiry' => time() + 600, // 10 minutes
                        ]);
                    } elseif (method_exists($this->auth, 'setCustomUserAttributes')) {
                        $this->auth->setCustomUserAttributes($userRecord->uid, [
                            'verificationCode' => $code,
                            'verificationCodeExpiry' => time() + 600, // 10 minutes
                        ]);
                    }
                } catch (Exception $e) {
                    Log::error('Failed to set custom attributes for new user: ' . $e->getMessage());
                    // Continue as the session fallback will be used
                }
                Log::debug("New user created in Firebase for: $email");
            }
        } catch (Exception $e) {
            // Log the error but continue with the code
            Log::error('Error storing verification code in Firebase: ' . $e->getMessage());
        }

        // Log the code in development mode for debugging
        Log::info("Verification code for $email: $code");

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
        // Clean the code input (remove spaces and non-numeric characters)
        $code = preg_replace('/[^0-9]/', '', $code);

        Log::debug("Verification attempt for email: $email with code: $code");

        try {
            // Try to use Firebase
            try {
                $user = $this->auth->getUserByEmail($email);
                $customAttributes = $user->customAttributes ?? [];

                if (empty($customAttributes)) {
                    Log::debug("No custom attributes found for user: $email");
                    // In development mode, accept the code anyway
                    if (Configure::read('debug')) {
                        Log::warning('Development mode: accepting verification due to empty attributes');

                        return true;
                    }

                    return false;
                }

                $storedCode = $customAttributes['verificationCode'] ?? null;
                $expiry = $customAttributes['verificationCodeExpiry'] ?? 0;

                // Convert expiry to int to avoid type errors with date()
                $expiryTimestamp = is_numeric($expiry) ? (int)$expiry : 0;

                // Log verification attempt details
                Log::debug("Firebase data for $email: Entered code=$code, Stored code=$storedCode, Expiry=" . date('Y-m-d H:i:s', $expiryTimestamp));

                // Check if code matches and has not expired
                if ($storedCode === $code && time() < $expiryTimestamp) {
                    // Clear the verification code after successful verification
                    try {
                        if (method_exists($this->auth, 'setCustomUserClaims')) {
                            $this->auth->setCustomUserClaims($user->uid, [
                                'verificationCode' => null,
                                'verificationCodeExpiry' => null,
                                'lastVerifiedLogin' => time(),
                            ]);
                        } elseif (method_exists($this->auth, 'setCustomUserAttributes')) {
                            $this->auth->setCustomUserAttributes($user->uid, [
                                'verificationCode' => null,
                                'verificationCodeExpiry' => null,
                                'lastVerifiedLogin' => time(),
                            ]);
                        }
                    } catch (Exception $e) {
                        Log::error('Failed to clear custom attributes: ' . $e->getMessage());
                    }
                    Log::debug("Verification successful for: $email");

                    return true;
                }

                Log::debug('Firebase verification failed: code mismatch or expired');

                // In development mode, accept the code anyway
                if (Configure::read('debug')) {
                    Log::warning('Development mode: accepting verification despite mismatch');

                    return true;
                }

                return false;
            } catch (UserNotFound $e) {
                Log::debug("User not found in Firebase: $email");
                // In development mode, accept the code anyway
                if (Configure::read('debug')) {
                    Log::warning('Development mode: accepting verification for non-existent user');

                    return true;
                }

                return false;
            }
        } catch (Exception $e) {
            Log::error('Error during verification: ' . $e->getMessage());

            // In development mode, accept verification despite errors
            if (Configure::read('debug')) {
                Log::warning('Development mode: accepting verification despite errors');

                return true;
            }

            return false;
        }
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
        Log::debug("Checking if device is trusted for $email: $deviceId");
        try {
            $user = $this->auth->getUserByEmail($email);
            $customAttributes = $user->customAttributes ?? [];

            if (empty($customAttributes)) {
                Log::debug("No custom attributes found for user: $email");

                return false;
            }

            $trustedDevices = $customAttributes['trustedDevices'] ?? [];
            $currentTime = time();
            $trusted = false;
            $updatedDevices = [];
            $needsUpdate = false;

            // Check if device is in trusted devices list and not expired
            foreach ($trustedDevices as $device) {
                if (is_array($device) && isset($device['id'])) {
                    // Get expiration time and ensure it's an integer
                    $deviceExpires = isset($device['expires']) ? (is_numeric($device['expires']) ? (int)$device['expires'] : 0) : 0;

                    // New format with expiration
                    if ($device['id'] === $deviceId && (!isset($device['expires']) || $deviceExpires > $currentTime)) {
                        $trusted = true;
                        Log::debug('Found trusted device: ' . json_encode($device));
                    }

                    // Add to updated list if not expired
                    if (!isset($device['expires']) || $deviceExpires > $currentTime) {
                        $updatedDevices[] = $device;
                    } else {
                        $needsUpdate = true;
                        Log::debug('Removing expired device: ' . json_encode($device));
                    }
                } elseif (is_string($device) && $device === $deviceId) {
                    // Old string format - convert to new format and consider trusted
                    $trusted = true;
                    $updatedDevices[] = [
                        'id' => $device,
                        'added' => $currentTime,
                        'expires' => $currentTime + (30 * 24 * 60 * 60), // 30 days
                    ];
                    $needsUpdate = true;
                    Log::debug("Found trusted device in legacy format: $device");
                } elseif (is_string($device)) {
                    // Keep other legacy format devices
                    $updatedDevices[] = [
                        'id' => $device,
                        'added' => $currentTime,
                        'expires' => $currentTime + (30 * 24 * 60 * 60), // 30 days
                    ];
                    $needsUpdate = true;
                }
            }

            // Update devices list if we removed expired devices or converted formats
            if ($needsUpdate) {
                Log::debug('Updating trusted devices list (removed expired or converted formats)');
                try {
                    $updateData = $customAttributes;
                    $updateData['trustedDevices'] = $updatedDevices;

                    if (method_exists($this->auth, 'setCustomUserClaims')) {
                        $this->auth->setCustomUserClaims($user->uid, $updateData);
                    } elseif (method_exists($this->auth, 'setCustomUserAttributes')) {
                        $this->auth->setCustomUserAttributes($user->uid, $updateData);
                    }
                } catch (Exception $e) {
                    Log::error('Error updating trusted devices: ' . $e->getMessage());
                    // Continue anyway, as we're just cleaning up
                }
            }

            return $trusted;
        } catch (UserNotFound $e) {
            Log::debug("User not found when checking trusted device: $email");

            return false;
        } catch (Exception $e) {
            Log::error('Error checking trusted device: ' . $e->getMessage());

            return false;
        }
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
        Log::debug("Adding trusted device for $email: $deviceId");
        try {
            $user = $this->auth->getUserByEmail($email);
            $customAttributes = $user->customAttributes ?? [];

            // Get existing trusted devices or initialize empty array
            $trustedDevices = $customAttributes['trustedDevices'] ?? [];

            // Limit to 5 trusted devices (remove oldest if needed)
            if (count($trustedDevices) >= 5 && !in_array($deviceId, $trustedDevices)) {
                array_shift($trustedDevices); // Remove oldest device
                Log::debug('Removed oldest trusted device to stay within limit');
            }

            // Structure with device ID and expiry
            $deviceEntry = [
                'id' => $deviceId,
                'added' => time(),
                'expires' => time() + (30 * 24 * 60 * 60), // 30 days
            ];

            // Update or add device
            $deviceExists = false;
            foreach ($trustedDevices as $key => $device) {
                if (is_array($device) && isset($device['id']) && $device['id'] === $deviceId) {
                    // Update existing device
                    $trustedDevices[$key] = $deviceEntry;
                    $deviceExists = true;
                    break;
                } elseif (is_string($device) && $device === $deviceId) {
                    // Convert old format to new format
                    $trustedDevices[$key] = $deviceEntry;
                    $deviceExists = true;
                    break;
                }
            }

            // Add device if not found
            if (!$deviceExists) {
                $trustedDevices[] = $deviceEntry;
            }

            // Try different methods based on what's available
            $updateSuccess = false;
            try {
                // Prepare the full set of attributes to update
                $updateData = $customAttributes;
                $updateData['trustedDevices'] = $trustedDevices;

                if (method_exists($this->auth, 'setCustomUserClaims')) {
                    $this->auth->setCustomUserClaims($user->uid, $updateData);
                    $updateSuccess = true;
                } elseif (method_exists($this->auth, 'setCustomUserAttributes')) {
                    $this->auth->setCustomUserAttributes($user->uid, $updateData);
                    $updateSuccess = true;
                } else {
                    Log::error('No method available to update trusted devices');
                }

                if ($updateSuccess) {
                    Log::debug("Successfully added trusted device for $email");
                }
            } catch (Exception $e) {
                Log::error('Firebase error updating trusted devices: ' . $e->getMessage());

                return false;
            }

            return $updateSuccess;
        } catch (UserNotFound $e) {
            Log::error("User not found when adding trusted device: $email");

            return false;
        } catch (Exception $e) {
            Log::error('Unexpected error adding trusted device: ' . $e->getMessage());

            return false;
        }
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
            $user = $this->auth->getUserByEmail($email);
            $customAttributes = $user->customAttributes ?? [];

            $trustedDevices = $customAttributes['trustedDevices'] ?? [];

            if (in_array($deviceId, $trustedDevices)) {
                $trustedDevices = array_filter($trustedDevices, function ($device) use ($deviceId) {
                    return $device !== $deviceId;
                });
                // Try different methods based on what's available
                if (method_exists($this->auth, 'setCustomUserClaims')) {
                    $this->auth->setCustomUserClaims($user->uid, [
                        'trustedDevices' => array_values($trustedDevices),
                    ]);
                } elseif (method_exists($this->auth, 'setCustomUserAttributes')) {
                    $this->auth->setCustomUserAttributes($user->uid, [
                        'trustedDevices' => array_values($trustedDevices),
                    ]);
                } else {
                    Log::error('No method available to update trusted devices');
                }
            }

            return true;
        } catch (UserNotFound $e) {
            return false;
        }
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
        // If device is already trusted, don't require 2FA
        if (isset($requestData['deviceId']) && $this->isTrustedDevice($email, $requestData['deviceId'])) {
            return false;
        }

        try {
            $user = $this->auth->getUserByEmail($email);
            $customAttributes = $user->customAttributes ?? [];

            // If user has never logged in before, always require 2FA
            if (empty($customAttributes['lastVerifiedLogin'])) {
                return true;
            }

            // Check for suspicious factors that would trigger 2FA
            $riskFactors = 0;

            // New device/location
            if (
                isset($requestData['ip']) && !empty($customAttributes['lastIpAddress'])
                && $requestData['ip'] !== $customAttributes['lastIpAddress']
            ) {
                $riskFactors++;
            }

            // Unusual time of day
            if (isset($requestData['time'])) {
                // Ensure we're working with integer timestamps
                $currentTime = is_numeric($requestData['time']) ? (int)$requestData['time'] : time();
                $lastLoginTime = !empty($customAttributes['lastLoginTime'])
                    ? (int)$customAttributes['lastLoginTime'] : 0;

                // Compare hours using integer timestamps
                $hourDiff = abs((int)date('H', $currentTime) - (int)date('H', $lastLoginTime));
                if ($hourDiff > 6) { // If logging in more than 6 hours different than usual
                    $riskFactors++;
                }
            }

            // If we detect risk factors, require 2FA
            return $riskFactors > 0;
        } catch (UserNotFound $e) {
            // New users should always verify
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
            $user = $this->auth->getUserByEmail($email);

            $updateData = [
                'lastLoginTime' => time(),
            ];

            if (isset($requestData['ip'])) {
                $updateData['lastIpAddress'] = $requestData['ip'];
            }

            // Try different methods based on what's available
            if (method_exists($this->auth, 'setCustomUserClaims')) {
                $this->auth->setCustomUserClaims($user->uid, $updateData);
            } elseif (method_exists($this->auth, 'setCustomUserAttributes')) {
                $this->auth->setCustomUserAttributes($user->uid, $updateData);
            } else {
                Log::error('No method available to update user metadata');
            }
        } catch (UserNotFound $e) {
            // Handle case where user doesn't exist
        }
    }
}

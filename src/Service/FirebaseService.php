<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Core\Configure;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Auth\SignInResult;
use Kreait\Firebase\Exception\Auth\UserNotFound;

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
            
            // Debug information
            \Cake\Log\Log::debug('Firebase credentials path: ' . $credentialsFile);
            \Cake\Log\Log::debug('Firebase database URL: ' . Configure::read('Firebase.databaseUrl'));
            
            // Verify file
            if (!file_exists($credentialsFile)) {
                throw new \RuntimeException('Firebase credentials file not found. Please ensure it exists at: ' . $credentialsFile);
            }
            
            if (!is_readable($credentialsFile)) {
                throw new \RuntimeException('Firebase credentials file is not readable. Please check permissions on: ' . $credentialsFile);
            }
            
            // Check file content
            $credentials = file_get_contents($credentialsFile);
            if (empty($credentials)) {
                throw new \RuntimeException('Firebase credentials file is empty.');
            }
            
            // Verify JSON structure
            $jsonData = json_decode($credentials, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Firebase credentials file contains invalid JSON: ' . json_last_error_msg());
            }
            
            // Create Firebase Auth instance
            $factory = (new Factory)
                ->withServiceAccount($credentialsFile)
                ->withDatabaseUri(Configure::read('Firebase.databaseUrl'));
                
            $this->auth = $factory->createAuth();
            \Cake\Log\Log::debug('Firebase Auth initialized successfully');
            
        } catch (\Exception $e) {
            // Log the error with detailed information
            \Cake\Log\Log::error('Firebase initialization error: ' . $e->getMessage());
            \Cake\Log\Log::error('Error details: ' . $e->getTraceAsString());
            
            // For development purposes, we'll create a dummy implementation
            // In production, you would want to rethrow the exception
            if (Configure::read('debug')) {
                \Cake\Log\Log::warning('Using dummy Firebase Auth implementation for development');
                // We'll provide a mock implementation for development
                $this->auth = new class {
                    public function getUserByEmail($email) {
                        return (object) ['uid' => 'mock-uid-' . md5($email), 'customAttributes' => []];
                    }
                    
                    public function setCustomUserAttributes($uid, $attributes) {
                        \Cake\Log\Log::debug('Mock: Setting attributes for user ' . $uid);
                        return true;
                    }
                    
                    public function createUser($userData) {
                        \Cake\Log\Log::debug('Mock: Creating user ' . $userData['email']);
                        return (object) ['uid' => 'mock-uid-' . md5($userData['email'])];
                    }
                };
            } else {
                // In production, rethrow the exception
                throw $e;
            }
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
        $code = sprintf('%06d', mt_rand(100000, 999999));
        
        // Always store the code in session for development mode and as backup
        try {
            $request = \Cake\Routing\Router::getRequest();
            if ($request) {
                $session = $request->getSession();
                $session->write("VerificationCodes.$email", [
                    'code' => $code,
                    'expiry' => time() + 600, // 10 minutes
                ]);
                \Cake\Log\Log::debug("Stored verification code in session for: $email");
                
                // Also store in a debug area for troubleshooting
                $session->write('LastVerificationCode', [
                    'email' => $email,
                    'code' => $code,
                    'time' => date('Y-m-d H:i:s'),
                    'expiry' => date('Y-m-d H:i:s', time() + 600)
                ]);
            }
        } catch (\Exception $ex) {
            \Cake\Log\Log::error("Could not store verification code in session: " . $ex->getMessage());
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
                    } else if (method_exists($this->auth, 'setCustomUserAttributes')) {
                        $this->auth->setCustomUserAttributes($user->uid, [
                            'verificationCode' => $code,
                            'verificationCodeExpiry' => time() + 600, // 10 minutes
                        ]);
                    } else {
                        throw new \RuntimeException('No method available to set user custom attributes');
                    }
                    \Cake\Log\Log::debug("Verification code set for existing user: $email");
                } catch (\Exception $e) {
                    \Cake\Log\Log::error("Failed to set custom attributes: " . $e->getMessage());
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
                    } else if (method_exists($this->auth, 'setCustomUserAttributes')) {
                        $this->auth->setCustomUserAttributes($userRecord->uid, [
                            'verificationCode' => $code,
                            'verificationCodeExpiry' => time() + 600, // 10 minutes
                        ]);
                    }
                } catch (\Exception $e) {
                    \Cake\Log\Log::error("Failed to set custom attributes for new user: " . $e->getMessage());
                    // Continue as the session fallback will be used
                }
                \Cake\Log\Log::debug("New user created in Firebase for: $email");
            }
        } catch (\Exception $e) {
            // Log the error but continue with the code
            \Cake\Log\Log::error('Error storing verification code in Firebase: ' . $e->getMessage());
        }
        
        // Log the code in development mode for debugging
        \Cake\Log\Log::info("Verification code for $email: $code");
        
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
        
        \Cake\Log\Log::debug("Verification attempt for email: $email with code: $code");
        
        try {
            // Accept any code in debug mode for easy testing
            if (Configure::read('debug')) {
                // Store the verification attempt in session for debugging
                try {
                    $request = \Cake\Routing\Router::getRequest();
                    if ($request) {
                        $session = $request->getSession();
                        $session->write('VerificationDebug.lastAttempt', [
                            'email' => $email,
                            'code' => $code,
                            'time' => date('Y-m-d H:i:s')
                        ]);
                    }
                } catch (\Exception $ex) {
                    // Ignore session errors
                }
                
                // Check for hardcoded test codes in development
                if ($code === '123456' || $code === '111111' || $code === '000000') {
                    \Cake\Log\Log::debug("Development mode: accepting test code $code for $email");
                    return true;
                }
                
                // Check for exact match with the last sent code
                try {
                    $request = \Cake\Routing\Router::getRequest();
                    if ($request) {
                        $session = $request->getSession();
                        // Try to get the code from the session
                        if ($session->check("VerificationCodes.$email")) {
                            $sessionData = $session->read("VerificationCodes.$email");
                            \Cake\Log\Log::debug("Found code in session: " . json_encode($sessionData));
                            
                            // Check if codes match
                            if ($sessionData['code'] === $code) {
                                \Cake\Log\Log::debug("Verification successful: code matches session stored code");
                                return true;
                            } else {
                                \Cake\Log\Log::debug("Code mismatch: expected {$sessionData['code']}, got $code");
                            }
                        } else {
                            \Cake\Log\Log::debug("No verification code found in session for $email");
                        }
                    }
                } catch (\Exception $ex) {
                    \Cake\Log\Log::error("Error accessing session: " . $ex->getMessage());
                }
            }
            
            // Try to use Firebase
            try {
                $user = $this->auth->getUserByEmail($email);
                $customAttributes = $user->customAttributes ?? [];
                
                if (empty($customAttributes)) {
                    \Cake\Log\Log::debug("No custom attributes found for user: $email");
                    // In development mode, accept the code anyway
                    if (Configure::read('debug')) {
                        \Cake\Log\Log::warning("Development mode: accepting verification due to empty attributes");
                        return true;
                    }
                    return false;
                }
                
                $storedCode = $customAttributes['verificationCode'] ?? null;
                $expiry = $customAttributes['verificationCodeExpiry'] ?? 0;
                
                // Log verification attempt details
                \Cake\Log\Log::debug("Firebase data for $email: Entered code=$code, Stored code=$storedCode, Expiry=" . date('Y-m-d H:i:s', $expiry));
                
                // Check if code matches and has not expired
                if ($storedCode === $code && time() < $expiry) {
                    // Clear the verification code after successful verification
                    try {
                        if (method_exists($this->auth, 'setCustomUserClaims')) {
                            $this->auth->setCustomUserClaims($user->uid, [
                                'verificationCode' => null,
                                'verificationCodeExpiry' => null,
                                'lastVerifiedLogin' => time(),
                            ]);
                        } else if (method_exists($this->auth, 'setCustomUserAttributes')) {
                            $this->auth->setCustomUserAttributes($user->uid, [
                                'verificationCode' => null,
                                'verificationCodeExpiry' => null,
                                'lastVerifiedLogin' => time(),
                            ]);
                        }
                    } catch (\Exception $e) {
                        \Cake\Log\Log::error("Failed to clear custom attributes: " . $e->getMessage());
                    }
                    \Cake\Log\Log::debug("Verification successful for: $email");
                    return true;
                }
                
                \Cake\Log\Log::debug("Firebase verification failed: code mismatch or expired");
                
                // In development mode, accept the code anyway
                if (Configure::read('debug')) {
                    \Cake\Log\Log::warning("Development mode: accepting verification despite mismatch");
                    return true;
                }
                
                return false;
            } catch (UserNotFound $e) {
                \Cake\Log\Log::debug("User not found in Firebase: $email");
                // In development mode, accept the code anyway
                if (Configure::read('debug')) {
                    \Cake\Log\Log::warning("Development mode: accepting verification for non-existent user");
                    return true;
                }
                return false;
            }
        } catch (\Exception $e) {
            \Cake\Log\Log::error("Error during verification: " . $e->getMessage());
            
            // In development mode, accept verification despite errors
            if (Configure::read('debug')) {
                \Cake\Log\Log::warning("Development mode: accepting verification despite errors");
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
        try {
            $user = $this->auth->getUserByEmail($email);
            $customAttributes = $user->customAttributes ?? [];
            
            if (empty($customAttributes)) {
                return false;
            }
            
            $trustedDevices = $customAttributes['trustedDevices'] ?? [];
            
            return in_array($deviceId, $trustedDevices);
        } catch (UserNotFound $e) {
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
        try {
            $user = $this->auth->getUserByEmail($email);
            $customAttributes = $user->customAttributes ?? [];
            
            $trustedDevices = $customAttributes['trustedDevices'] ?? [];
            
            if (!in_array($deviceId, $trustedDevices)) {
                $trustedDevices[] = $deviceId;
                $this->auth->setCustomUserAttributes($user->uid, [
                    'trustedDevices' => $trustedDevices,
                ]);
            }
            
            return true;
        } catch (UserNotFound $e) {
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
                $trustedDevices = array_filter($trustedDevices, function($device) use ($deviceId) {
                    return $device !== $deviceId;
                });
                $this->auth->setCustomUserAttributes($user->uid, [
                    'trustedDevices' => array_values($trustedDevices),
                ]);
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
            if (isset($requestData['ip']) && !empty($customAttributes['lastIpAddress']) 
                && $requestData['ip'] !== $customAttributes['lastIpAddress']) {
                $riskFactors++;
            }
            
            // Unusual time of day
            if (isset($requestData['time'])) {
                $lastLoginTime = !empty($customAttributes['lastLoginTime']) 
                    ? (int) $customAttributes['lastLoginTime'] : 0;
                $hourDiff = abs(date('H', $requestData['time']) - date('H', $lastLoginTime));
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
            
            $this->auth->setCustomUserAttributes($user->uid, $updateData);
        } catch (UserNotFound $e) {
            // Handle case where user doesn't exist
        }
    }
}
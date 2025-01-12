<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;
use Illuminate\Support\Facades\Log;

class WattsiService
{
    protected $accessToken;
    protected $instanceId;
    protected $baseUrl;

    public function __construct()
    {
        $this->accessToken = config('services.wattsi.access_token');
        $this->instanceId = config('services.wattsi.instance_id');
        $this->baseUrl = 'https://app.wattsi.net/api';

        // Log configuration on instantiation
        Log::info('WattsiService initialized', [
            'access_token' => $this->accessToken ? 'set' : 'not set',
            'instance_id' => $this->instanceId ? 'set' : 'not set'
        ]);
    }

    public function checkPhone($phone)
    {
        $response = Http::get("{$this->baseUrl}/check_phone", [
            'access_token' => $this->accessToken,
            'phone' => $phone
        ]);

        return $response->json();
    }

    public function sendMessage($phone, $message)
    {
        $response = Http::post("{$this->baseUrl}/send", [
            'number' => $phone,
            'type' => 'text',
            'message' => $message,
            'instance_id' => $this->instanceId,
            'access_token' => $this->accessToken
        ]);

        return $response->json();
    }

    public function checkConnection()
    {
        try {
            Log::info('Checking Wattsi connection');

            $response = Http::get("{$this->baseUrl}/reconnect", [
                'instance_id' => $this->instanceId,
                'access_token' => $this->accessToken
            ]);

            Log::info('Connection check response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return $response->successful();
        } catch (Exception $e) {
            Log::error('Wattsi connection check error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    public function createInstance()
    {
        try {
            Log::info('Creating new WhatsApp instance');

            $response = Http::get("{$this->baseUrl}/create_instance", [
                'access_token' => $this->accessToken
            ]);

            Log::info('Create instance response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['instance_id'])) {
                    // Store the new instance ID
                    $this->instanceId = $data['instance_id'];
                    // You might want to save this to your database or update your .env
                    return $data['instance_id'];
                }
            }

            return false;
        } catch (Exception $e) {
            Log::error('Create instance error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    public function getQRCode($instanceId = null)
    {
        try {
            $instanceId = $instanceId ?? $this->instanceId;
            Log::info('Getting QR code for instance', ['instance_id' => $instanceId]);

            $response = Http::post("{$this->baseUrl}/get_qrcode", [
                'instance_id' => $instanceId,
                'access_token' => $this->accessToken
            ]);

            Log::info('Get QR code response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['qrcode'] ?? false;
            }

            return false;
        } catch (Exception $e) {
            Log::error('Get QR code error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    public function setWebhook($webhookUrl, $instanceId = null)
    {
        try {
            $instanceId = $instanceId ?? $this->instanceId;
            Log::info('Setting webhook for instance', [
                'instance_id' => $instanceId,
                'webhook_url' => $webhookUrl
            ]);

            $response = Http::post("{$this->baseUrl}/set_webhook", [
                'webhook_url' => $webhookUrl,
                'enable' => true,
                'instance_id' => $instanceId,
                'access_token' => $this->accessToken
            ]);

            Log::info('Set webhook response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return $response->successful();
        } catch (Exception $e) {
            Log::error('Set webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    public function resetInstance($instanceId = null)
    {
        try {
            $instanceId = $instanceId ?? $this->instanceId;
            
            $response = Http::post("{$this->baseUrl}/reset", [
                'instance_id' => $instanceId,
                'access_token' => $this->accessToken
            ]);

            return $response->successful();
        } catch (Exception $e) {
            Log::error('Reset instance error', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
} 
<?php

namespace App\Http\Controllers;

use App\Services\WattsiService;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    protected $wattsiService;

    public function __construct(WattsiService $wattsiService)
    {
        $this->wattsiService = $wattsiService;
    }

    public function setup()
    {
        return view('whatsapp.setup');
    }

    public function createInstance()
    {
        try {
            $instanceId = $this->wattsiService->createInstance();
            
            if ($instanceId) {
                session(['whatsapp_instance_id' => $instanceId]);
                return redirect()->route('whatsapp.qr')
                    ->with('success', 'Instance created successfully');
            }

            return back()->with('error', 'Failed to create WhatsApp instance');
        } catch (\Exception $e) {
            Log::error('WhatsApp instance creation failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to create WhatsApp instance');
        }
    }

    public function showQR()
    {
        try {
            $qrCode = $this->wattsiService->getQRCode();
            return view('whatsapp.qr', compact('qrCode'));
        } catch (\Exception $e) {
            Log::error('Failed to get QR code', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to get QR code');
        }
    }

    public function checkConnection()
    {
        try {
            $isConnected = $this->wattsiService->checkConnection();
            return response()->json(['connected' => $isConnected]);
        } catch (\Exception $e) {
            return response()->json(['connected' => false]);
        }
    }
} 
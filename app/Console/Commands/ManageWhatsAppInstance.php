<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WattsiService;

class ManageWhatsAppInstance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:manage {action : Action to perform (create|reset|status)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage WhatsApp instance';

    /**
     * Execute the console command.
     */
    public function handle(WattsiService $wattsiService)
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'create':
                $instanceId = $wattsiService->createInstance();
                if ($instanceId) {
                    $this->info("Instance created: $instanceId");
                    $qrCode = $wattsiService->getQRCode($instanceId);
                    if ($qrCode) {
                        $this->info("QR Code URL: $qrCode");
                    }
                }
                break;

            case 'reset':
                if ($wattsiService->resetInstance()) {
                    $this->info("Instance reset successfully");
                }
                break;

            case 'status':
                if ($wattsiService->checkConnection()) {
                    $this->info("WhatsApp is connected");
                } else {
                    $this->error("WhatsApp is not connected");
                }
                break;
        }
    }
}

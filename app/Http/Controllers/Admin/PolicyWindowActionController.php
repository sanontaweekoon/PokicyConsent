<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PolicyWindow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PolicyWindowActionController extends Controller
{
    public function qr(PolicyWindow $window)
    {
        return $this->qrSvg($window);
    }

    public function qrSvg(PolicyWindow $window)
    {
        try {
            $url = config('app.url') . '/ack/' . $window->id;
            
            $svg = QrCode::format('svg')
                ->size(512)
                ->margin(1)
                ->errorCorrection('H')
                ->generate($url);
            
            return response($svg, 200)
                ->header('Content-Type', 'image/svg+xml')
                ->header('Cache-Control', 'public, max-age=3600');
                
        } catch (\Exception $e) {
            Log::error('QR generation failed', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'message' => 'ไม่สามารถสร้าง QR Code ได้',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function announceNow(Request $request, PolicyWindow $window)
    {
        if (!$window->is_open) {
            $window->update([
                'is_open' => true,
                'start_at' => now(),
            ]);
        }

        return response()->json([
            'ok' => true,
            'message' => 'เปิด Policy Window แล้ว',
            'link' => config('app.url') . '/ack/' . $window->id,
            'qr_url' => url("/api/policy-windows/{$window->id}/qr"),
        ]);
    }
}

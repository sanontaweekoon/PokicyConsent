<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PolicyWindow;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PolicyWindowActionController extends Controller
{
    //  // POST: ประกาศทันที + gen QR + คืนลิงก์และที่อยู่รูป QR
    public function announceNow(Request $request, PolicyWindow $window){
        if (! $window->is_open) {
            $window->is_open = true;
        }
        if (! $window->start_at){
            $window->start_at = now();
        }
        $window->save();

        $url = $window->signedLink();

        $path = "qr/window_{$window->id}.png";
        $png = QrCode::format('png')->size(512)->margin(1)->generate($url);
        Storage::disk('public')->put($path, $png);

        return response()->json([
            'ok' => true,
            'link' => $url,
            'qr_path' => asset("storage/{$path}")
        ]);
    }

    // GET: ส่งภาพ QR
    public function qr(PolicyWindow $window){
        $png = QrCode::format('png')->size(512)->margin(1)->generate($window->signedLink());
        return response($png, 200)->header('Content-Type', 'image/png');
    }
}


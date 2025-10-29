<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PolicyWindow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PolicyWindowController extends Controller
{
    // Togle เปิด-ปิด หน้าต่างการรับทราบนโยบาย
    public function toggle(PolicyWindow $window)
    {
        $newStatus = !$window->is_open;

        $window->update([
            'is_open' => $newStatus,
            'end_at' => $newStatus ? null : now(),
        ]);

        Log::info('Policy Window toggled', [
            'window_id' => $window->id,
            'is_open' => $newStatus,
            'policy_id' => $window->policy_id
        ]);

        return response()->json([
            'ok' => true,
            'message' => $newStatus ? 'เปิดการรับทราบแล้ว' : 'ปิดการรับทราบแล้ว',
            'is_open' => $newStatus,
            'window' => $window->fresh() // โหลดข้อมูลใหม่
        ]);
    }

    // เปิดให้รับทราบ
    public function open(PolicyWindow $window)
    {
        if ($window->is_open) {
            return response()->json([
                'ok' => false,
                'message' => 'การเปิดการรับทราบนี้ถูกเปิดอยู่แล้ว'
            ], 422);
        }

        $window->update([
            'is_open' => true,
            'end_at' => null,
            'start_at' => $window->start_at ?? now(),
        ]);

        Log::info('Policy Window opened', [
            'window_id' => $window->id,
            'policy_id' => $window->policy_id
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'เปิดการรับทราบแล้ว',
            'window' => $window->fresh()
        ]);
    }

    public function close(PolicyWindow $window)
    {
        if (!$window->is_open) {
            return response()->json([
                'ok' => false,
                'message' => 'การปิดการรับทราบนี้ถูกปิดอยู่แล้ว'
            ], 422);
        }

        $window->update([
            'is_open' => false,
            'end_at' => now(),
        ]);

        Log::info('Policy Window closed', [
            'window_id' => $window->id,
            'policy_id' => $window->policy_id
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'ปิดการรับทราบแล้ว',
            'window' => $window->fresh()
        ]);
    }
}

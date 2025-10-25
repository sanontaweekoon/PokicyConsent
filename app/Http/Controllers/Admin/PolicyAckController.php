<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Acknowledgement;

class PolicyAckController extends Controller
{
    public function acknowledge(Request $request, $window)
    {
        $user = $request->user(); // ดึงข้อมูลผู้ใช้จากคำขอ
        $data = $request->validate([
            'signer_name' => ['required', 'string', 'max:255'],
            'signature_strokes' => ['required', 'array', 'min:1'],
            'signature_strokes.*' => ['array'],
        ]);

        $normalized = json_encode($data['signature_strokes'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $hash = hash('sha256', $normalized . '|uid:' . $user->id . '|win:' . $window->id);

        $ack = Acknowledgement::updateOrCreate(
            ['policy_window_id' => $window->id, 'user_id' => $user->id],
            [
                'status' => 'acknowledged',
                'signer_name' => $data['signer_name'],
                'signature_payload' => json_decode($normalized, true),
                'signature_hash' => $hash,
                'acknowledged_at' => now()
            ]
        );
        return response()->json(['ok' => true, 'id' => $ack->id]);
    }

    public function verifySignature(Acknowledgement $ack)
    {
        $normalized = json_encode($ack->signature_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $recalc = hash('sha256', $normalized . '|uid:' . $ack->user_id . '|win:' . $ack->policy_window_id);
        return response()->json(['valid' => hash_equals($ack->signature_hash, $recalc)]);
    }
}

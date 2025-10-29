<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Acknowledgement;
use App\Models\CheckLogin;
use App\Models\PolicyWindow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PolicyAckController extends Controller
{
    public function show(PolicyWindow $window)
    {
        if (method_exists($window, 'isOpen') && ! $window->isOpen()) {
            return response()->json(['message' => 'หน้าต่างการรับทราบนโยบายนี้ปิดแล้ว'], 410);
        }

        return view('app', [
            'windowId' => $window->id,
        ]);
    }

    public function verifyIdentity(Request $request, PolicyWindow $window)
    {
        $validate = $request->validate([
            'IdentityCard' => ['required', 'digits:13'],
            'birthDate'    => ['required', 'date_format:Y-m-d'],
        ]);

        if (!$window->is_open) {
            Log::warning("Window is closed", ['window_id' => $window->id]);
            return response()->json(['message' => 'ขณะนี้ปิดการรับทราบนโยบายแล้ว กรุณาติดต่อผู้ดูแลระบบ'], 403);
        }

        $record = CheckLogin::query()
            ->select(['EmpCode', DB::raw('FName as full_name')])
            ->where('IdentityCard', $validate['IdentityCard'])
            ->whereDate('BirthDate', $validate['birthDate'])
            ->first();

        if (! $record) {
            return response()->json([
                'message' => 'ไม่พบข้อมูลผู้ใช้ กรุณาตรวจสอบหมายเลขบัตรประชาชนและวันเกิดอีกครั้ง',
                'errors'  => [
                    'identity' => ['เลขบัตรหรือวันเดือนปีเกิดไม่ถูกต้อง'],
                ],
            ], 422);
        }

        $policy = $window->policy()->select('title', 'description')->first();

        $ack = Acknowledgement::firstOrCreate(
            [
                'policy_window_id' => $window->id,
                'employee_code'          => $record->EmpCode,
            ],
            [
                'status' => 'pending',
            ]
        );

        if ($ack->status === 'acknowledged') {
            Log::info("Already acknowledged", [
                'window_id' => $window->id,
                'employee_code' => $record->EmpCode,
            ]);
        }

        return response()->json([
            'ok'                 => true,
            'employee'           => [
                'EmpCode' => $record->EmpCode,
                'Name'   => $record->full_name,
                'EmpLevelCode' => $record->EmpLevelCode,
                'mailAD' => $record->mailAD,
                'PositionName' => $record->PositionName,
                'Department' => $record->Department
            ],
            'policy' => [
                'title'   => $policy->title ?? '',
                'content' => $policy->description ?? '',
            ],
            'acknowledgement_id' => $ack->id,
            'has_signed'         => $ack->status === 'acknowledged',
        ], 200);
    }

    public function acknowledge(Request $request, PolicyWindow $window)
    {
        if (method_exists($window, 'isOpen') && ! $window->isOpen()) {
            return response()->json(['message' => 'หน้าต่างการรับทราบนโยบายนี้ปิดแล้ว'], 410);
        }

        $data = $request->validate([
            'acknowledgement_id'   => ['required', 'integer', 'exists:acknowledgements,id'],
            'employee_code'        => ['required', 'string', 'max:100'],
            'signature_strokes'    => ['required', 'array', 'min:1'],
            'signature_strokes.*'  => ['array'],
        ]);

        if (!$window->is_open) {
            Log::warning("Attempt to acknowledge on closed window", [
                'window_id' => $window->id,
                'ack_id' => $data['acknowledgement_id']
            ]);

            return response()->json([
                'message' => 'ขณะนี้ปิดการรับทราบนโยบายแล้ว กรุณาติดต่อผู้ดูแลระบบ'
            ], 403);
        }

        $existing = Acknowledgement::query()
            ->where('id', $data['acknowledgement_id'])
            ->where('policy_window_id', $window->id)
            ->where('employee_code', $data['employee_code'])
            ->firstOrFail();

        if ($existing && $existing->status === 'acknowledged') {
            return response()->json([
                'ok' => false,
                'message' => 'คุณได้รับทราบนโยบายนี้ไปแล้ว ไม่สามารถยืนยันซ้ำได้'
            ], 422);
        }

        $emp = CheckLogin::query()
            ->select(['EmpCode', DB::raw('FName as full_name')])
            ->where('EmpCode', $data['employee_code'])
            ->first();

        if (! $emp) {
            return response()->json(['ok' => false, 'message' => 'ไม่พบพนักงาน'], 422);
        }

        $normalized = json_encode(
            $data['signature_strokes'],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        $hash = hash('sha256', $normalized . '|emp:' . $data['employee_code'] . '|win:' . $window->id);

        if ($existing && $existing->status === 'pending') {
            $existing->update([
                'status'            => 'acknowledged',
                'signer_name'       => $emp->full_name,
                'signature_payload' => json_decode($normalized, true),
                'signature_hash'    => $hash,
                'acknowledged_at'   => now(),
            ]);
            $ack = $existing;
        } else {
            $ack = Acknowledgement::create(
                [
                    'policy_window_id'  => $window->id,
                    'employee_code'     => $data['employee_code'],
                    'status'            => 'acknowledged',
                    'signer_name'       => $emp->full_name,
                    'signature_payload' => json_decode($normalized, true),
                    'signature_hash'    => $hash,
                    'acknowledged_at'   => now(),
                ]
            );
        }

        if ($ack->wasRecentlyCreated === false && $ack->status !== 'acknowledged') {
            $ack->update([
                'status' => 'acknowledged',
                'signer_name' => $emp->full_name,
                'signature_payload' => json_decode($normalized, true),
                'signature_hash'    => $hash,
                'acknowledged_at'   => now(),
            ]);
        }
        return response()->json(['ok' => true, 'id' => $ack->id]);
    }

    public function verifySignature(Acknowledgement $ack)
    {
        $normalized = json_encode($ack->signature_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $recalc     = hash('sha256', $normalized . '|emp:' . $ack->employee_code . '|win:' . $ack->policy_window_id);
        return response()->json(['valid' => hash_equals($ack->signature_hash, $recalc)]);
    }
}

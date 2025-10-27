<?php
namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Acknowledgement;
use App\Models\CheckLogin;
use App\Models\PolicyWindow;
use Illuminate\Http\Request;

class PolicyAckController extends Controller
{
    public function verifyIdentity(Request $request, PolicyWindow $window)
    {
        if (method_exists($window, 'isOpen') && ! $window->isOpen()){
            return response()->json([
                'message' => 'หน้าต่างการรับทราบนโยบายนี้ปิดแล้ว'
            ], 410);
        }

        $validate = $request->validate([
            'IdentityCard' => ['required', 'digits:13'],
            'birthDate'    => ['required', 'date_format:Y-m-d'],
        ]);

        $record = CheckLogin::query()
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

        $ack = Acknowledgement::firstOrCreate(
            [
                'policy_window_id' => $window->id,
                'employee_code'          => $record->EmpCode,
            ],
            [
                'status' => 'pending',
            ]
        );
        return response()->json([
            'ok'                 => true,
            'employee'           => [
                'EmpCode' => $record->EmpCode,
                'fname'   => $record->Fname,
                'lname'   => $record->Lname,
            ],
            'acknowledgement_id' => $ack->id,
            'has_signed'         => $ack->status === 'acknowledged',
        ]);
    }

    public function acknowledge(Request $request, PolicyWindow $window)
    {
        if (method_exists($window, 'isOpen') && ! $window->isOpen()){
            return response()->json([
                'message' => 'หน้าต่างการรับทราบนโยบายนี้ปิดแล้ว'
            ], 410);
        }

        $data = $request->validate([
            'employee_code'       => ['required', 'string', 'max:20'],
            'signer_name'         => ['required', 'string', 'max:255'],
            'signature_strokes'   => ['required', 'array', 'min:1'],
            'signature_strokes.*' => ['array'],
        ]);

        $normalized = json_encode(
            $data['signature_strokes'],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        $existing = Acknowledgement::where('policy_window_id', $window->id)
            ->where('employee_code', $request->input('employee_code'))
            ->first();

        if($existing && $existing->status === 'acknowledged') {
            return response()->json([
                'ok' => true,
                'id' => $existing->id,
                'message' => 'รับทราบนโยบายนี้ไปแล้ว'
            ], 200);
        }

        $hash = hash('sha256', $normalized . '|emp:' . $data['employee_code'] . '|win:' . $window->id);

        $ack = Acknowledgement::updateOrCreate(
            [
                'policy_window_id' => $window->id,
                'employee_code'          => $data['employee_code']
            ],
            [
                'status'            => 'acknowledged',
                'signer_name'       => $data['signer_name'],
                'signature_payload' => $data['signature_strokes'],
                'signature_hash'    => $hash,
                'acknowledged_at'   => now(),
            ]
        );
        return response()->json(['ok' => true, 'id' => $ack->id]);
    }

    public function verifySignature(Acknowledgement $ack)
    {
        $normalized = json_encode($ack->signature_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $recalc     = hash('sha256', $normalized . '|emp:' . $ack->employee_code . '|win:' . $ack->policy_window_id);
        return response()->json(['valid' => hash_equals($ack->signature_hash, $recalc)]);
    }
}

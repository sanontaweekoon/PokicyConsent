<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\PolicyAnnouncementMail;
use App\Models\CheckLogin;
use App\Models\PolicyWindow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PolicyAnnouncementsController extends Controller
{
    // POST /admin/policy-windows/{window}/announce/email
    public function queueEmail(Request $request, PolicyWindow $window)
    {
        $data = $request->validate([
            'levels'   => ['array'],
            'levels.*' => ['string'],
        ]);

        $q = CheckLogin::query();
        if (! empty($data['levels'])) {
            $q->whereIn('EmpLevelID', $data['levels']);
        }

        $employees = $q->get(['Fname', 'Lname', 'IdentityCard', 'BirthDay', 'EmpLevelID', 'Email']);

        foreach ($employees as $emp) {
            if (! empty($emp->Email) && filter_var($emp->Email, FILTER_VALIDATE_EMAIL)) {
                Mail::to($emp->Email)->queue(new PolicyAnnouncementMail($window, $emp->Fname . ' ' . $emp->Lname));
            }
        }
        return response()->json(['ok' => true, 'queued' => $employees->count()]);
    }
}

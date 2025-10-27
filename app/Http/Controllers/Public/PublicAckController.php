<?php
namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PolicyWindow;
use Illuminate\Http\Request;

class PublicAckController extends Controller
{
    public function show(Request $request, PolicyWindow $window)
    {
        if (! $window->isOpen()) {
            abort(410, 'หน้าต่างการยอมรับนโยบายนี้ปิดแล้ว');
        }

        return view('app');
    }
}

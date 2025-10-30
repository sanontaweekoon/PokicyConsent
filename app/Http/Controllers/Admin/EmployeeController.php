<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CheckLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    // ดึงรายชื่อพนักงานที่มี email
    public function index()
    {
        try {
            $employees = CheckLogin::getEmployeesWithEmail();
            return response()->json([
                'ok' => true,
                'data' => $employees
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch employees with email: ' . $e->getMessage());
            return response()->json(['message' => 'เกิดข้อผิดพลาดในการดึงรายชื่อพนักงาน'], 500);
        }
    }

    public function search(Request $request)
    {
        $query = $request->input('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        try {
            $employees = CheckLogin::searchEmployeesWithEmail($query);

            return response()->json($employees);
        } catch (\Exception $e) {
            Log::error('Employee search failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'เกิดข้อผิดพลาดในการค้นหาพนักงาน'], 500);
        }
    }

    public function departments()
    {
        try {
            $departments = CheckLogin::getDepartments();

            return response()->json([
                'ok' => true,
                'data' => $departments
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch employee departments: ' . $e->getMessage());
            return response()->json(['message' => 'เกิดข้อผิดพลาดในการดึงข้อมูลแผนกพนักงาน'], 500);
        }
    }

    public function byDepartment(Request $request)
    {
        $departments = $request->input('department');

        if (empty($departments)) {
            return response()->json([]);
        }

        try {
            $employees = CheckLogin::getEmployeesByDepartment($departments);

            return response()->json($employees);
        } catch (\Exception $e) {
            Log::error('Failed to fetch employees by department: ' . $e->getMessage());
            return response()->json(['message' => 'เกิดข้อผิดพลาดในการดึงรายชื่อพนักงานตามแผนก'], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RecipientGroup;
use App\Models\RecipientGroupMember;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecipientGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // แสดงรายการกลุ่มทั้งหมด
        try {
            $groups = RecipientGroup::withCount('members')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($groups);
        } catch (\Exception $e) {
            Log::error('Failed to load recipient groups', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to load recipient groups'], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //สร้างกลุ่มใหม่
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:department,level,custom',
            'members' => 'required|array|min:1',
            'members.*.email' => 'required|email',
            'members.*.name' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $group = RecipientGroup::create([
                'name' => $data['name'],
                'type' => $data['type'],
                'created_by' => auth()->id(),
            ]);

            // เพิ่มสมาชิก
            foreach ($data['members'] as $member) {
                RecipientGroupMember::create([
                    'recipient_group_id' => $group->id,
                    'email' => $member['email'],
                    'name' => $member['name'],
                ]);
            }

            DB::commit();

            Log::info('Recipient group created', [
                'group_id' => $group->id,
                'name' => $group->name,
                'members_count' => count($data['members'])
            ]);

            return response()->json($group->load('members'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create recipient group', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Failed to create recipient group',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(RecipientGroup $recipientGroup)
    {
        //แสดงรายละเอียดกลุ่ม

        try {
            return response()->json($recipientGroup->load('members'));
        } catch (\Exception $e) {
            Log::error('Failed to load recipient group', ['error' =>  $e->getMessage()]);
            return response()->json(['message' => 'Failed to load recipient group'], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RecipientGroup $recipientGroup)
    {
        // แก้ไขกลุ่ม
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:department,level,custom',
            'members' => 'required|array|min:1',
            'members.*.email' => 'required|email',
            'members.*.name' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $recipientGroup->update([
                'name' => $data['name'],
                'type' => $data['type'],
            ]);

            // ลบสมาชิกทั้งหมด
            $recipientGroup->members()->delete();

            // เพิ่มสมาชิกใหม่
            foreach ($data['members'] as $member) {
                RecipientGroupMember::create([
                    'recipient_group_id' => $recipientGroup->id,
                    'email' => $member['email'],
                    'name' => $member['name'],
                ]);
            }

            DB::commit();

            Log::info('Recipient group updated', [
                'group_id' => $recipientGroup->id,
                'members_count' => count($data['members'])
            ]);

            return response()->json($recipientGroup->load('members'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update recipient group', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to update recipient group'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(RecipientGroup $recipientGroup)
    {
        // ลบกลุ่ม
        try {
            $recipientGroup->delete();

            Log::info('Recipient group deleted', ['group_id' => $recipientGroup->id]);
            return response()->json(['message' => 'Deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to delete recipient group', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to delete recipient group'], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Policy;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PoliciesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $perPage = (int) $request->query('per_page', $request->query('per', 25));
        $page = (int) $request->query('page', 1);

        $query = Policy::query()->when($search !== '', function ($q) use ($search) {
            $q->where(function ($w) use ($search) {
                $w->where('code', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        });

        // status filter: active | draft | scheduled
        if ($request->filled('status')) {
            $status = strtolower((string) $request->query('status'));
            if ($status === 'scheduled') {
                $query->whereNotNull('publish_at')->where('publish_at', '>', now());
            } elseif ($status === 'draft') {
                $query->where('status', Policy::STATUS_DRAFT);
            } elseif ($status === 'active') {
                $query->where('status', Policy::STATUS_ACTIVE);
            } else {
                $query->where('status', $status);
            }
        }

        $query->orderByDesc('id');

        return $perPage === 0 ?
            response()->json($query->get())
            : response()->json($query->paginate($perPage, ['*'], 'page', $page));
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
        $request->merge([
            'code' => $request->input('code') !== '' ? $request->input('code') : null,
            'category_id' => $request->input('category_id') !== '' ? $request->input('category_id') : null,
            'owner_user_id' => $request->input('owner_user_id') !== '' ? $request->input('owner_user_id') : null,
            'owner_org_unit_id' => $request->input('owner_org_unit_id') !== '' ? $request->input('owner_org_unit_id') : null,
            'publish_date' => $request->input('publish_date') !== '' ? $request->input('publish_date') : null,
            'publish_time' => $request->input('publish_time') !== '' ? $request->input('publish_time') : null,
            'publish_at' => $request->input('publish_at') ?? null
        ]);

        if (is_null($request->input('code'))) {
            $datePart = Carbon::now()->format('dmy');
            $base = "P-{$datePart}";
            $candidate = $base;
            $suffix = 0;

            while (Policy::where('code', $candidate)->exists()) {
                $suffix++;
                $candidate = $base . "-" . $suffix;
            }

            $request->merge(['code' => $candidate]);
            Log::debug('Policy.store generated patterned code for null input', ['code' => $candidate]);
        }


        $data = $request->validate([
            'category_id' => ['nullable', 'exists:policy_categories,id'],
            'code' => ['nullable', 'string', 'max:100', 'unique:policies,code'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'owner_user_id' => ['nullable', 'exists:users,id'],
            'owner_org_unit_id' => ['nullable', 'exists:org_units,id'],
            'is_required_ack' => ['sometimes', 'boolean'],
            'status' => ['required', Rule::in(Policy::STATUSES)],
            'publish_date' => ['nullable', 'date'],
            'publish_time' => ['nullable', 'date_format:H:i'],
            'publish_at' => ['nullable', 'date']
        ]);

        // Log::debug('Policy.store validated data', $data);
        $status = $data['status'];
        $date = $data['publish_date'] ?? null;
        $time = $data['publish_time'] ?? null;
        $timeSeconds = $time ? ($time . (strlen($time) === 5 ? ':00' : '')) : null;

        if ($status === 'active') {
            $data['publish_date'] = null;
            $data['publish_time'] = null;
            $data['publish_at'] = ($data['publish_at'] ?? null)
                ? Carbon::parse($data['publish_at'])->format('Y-m-d H:i:s')
                : Carbon::now()->format('Y-m-d H:i:s');
        } elseif ($status === 'scheduled') {
            if ($date && $timeSeconds) {
                $data['publish_at'] = Carbon::parse("$date $timeSeconds")->format('Y-m-d H:i:s');
            } else {
                $data['publish_date'] = null;
                $data['publish_time'] = null;
                $data['publish_at'] = null;
            }
        } else { // draft
            if ($date && $timeSeconds) {
                $data['publish_at'] = Carbon::parse("$date $timeSeconds")->format('Y-m-d H:i:s');
            } else {
                $data['publish_at'] = null;
                if (!$date || !$time) {
                    $data['publish_date'] = null;
                    $data['publish_time'] = null;
                }
            }
        }

        $data['is_required_ack'] = $data['is_required_ack'] ?? true;

        $userId = $request->user()?->id ?? auth()->id();
        Log::debug('Policy.store auth user id', ['userId' => $userId, 'request_user' => $request->user()]);
        $data['created_by'] = $userId;
        $data['updated_by'] = $userId;

        Log::debug('Policy.store create payload', $data);

        DB::enableQueryLog();

        try {
            $policy = Policy::create($data);

            Log::debug('Policy.store queries', DB::getQueryLog());

            return response()->json([
                'ok' => true,
                'validated' => $data,
                'policy' => $policy
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Policy.create failed', [
                'msg' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data,
                'queries' => DB::getQueryLog()
            ]);
            return response()->json(['message' => 'Could not create policy', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Policy $policy)
    {
        return response()->json($policy);
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
    public function update(Request $request, Policy $policy)
    {
        $request->merge([
            'code' => $request->input('code') !== '' ? $request->input('code') : null,
            'category_id' => $request->input('category_id') !== '' ? $request->input('category_id') : null,
            'owner_user_id' => $request->input('owner_user_id') !== '' ? $request->input('owner_user_id') : null,
            'owner_org_unit_id' => $request->input('owner_org_unit_id') !== '' ? $request->input('owner_org_unit_id') : null,
            'publish_date' => $request->input('publish_date') !== '' ? $request->input('publish_date') : null,
            'publish_time' => $request->input('publish_time') !== '' ? $request->input('publish_time') : null,
            'publish_at' => $request->input('publish_at') ?? null
        ]);

        if (is_null($request->input('code'))) {
            $datePart = Carbon::now()->format('dmy');
            $base = "P-{$datePart}";
            $candidate = $base;
            $suffix = 0;

            while (Policy::where('code', $candidate)->exists()) {
                $suffix++;
                $candidate = $base . "-" . $suffix;
            }

            $request->merge(['code' => $candidate]);
            Log::debug('Policy.store generated patterned code for null input', ['code' => $candidate]);
        }

        $data = $request->validate([
            'category_id' => ['nullable', 'exists:policy_categories,id'],
            'code' => ['nullable', 'string', 'max:100', Rule::unique('policies', 'code')->ignore($policy->id)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'owner_user_id' => ['nullable', 'exists:users,id'],
            'owner_org_unit_id' => ['nullable', 'exists:org_units,id'],
            'is_required_ack' => ['sometimes', 'boolean'],
            'status' => ['required', Rule::in(Policy::STATUSES)],
            'publish_date' => ['nullable', 'date'],
            'publish_time' => ['nullable', 'date_format:H:i'],
            'publish_at' => ['nullable', 'date']
        ]);

        $status = $data['status'];

        $date = $data['publish_date'] ?? null;
        $time = $data['publish_time'] ?? null;
        $timeSeconds = $time ? ($time . (strlen($time) === 5 ? ':00' : '')) : null;


        if ($status === 'active') {
            $data['publish_date'] = null;
            $data['publish_time'] = null;
            $data['publish_at'] = ($data['publish_at'] ?? null) ? Carbon::parse($data['publish_at'])->format('Y-m-d H:i:s') : Carbon::now()->format('Y-m-d H:i:s');
        } elseif ($status === 'scheduled') {
            // scheduled ต้องมีวัน+เวลา
            if ($date && $timeSeconds) {
                $data['publish_at'] = Carbon::parse("$date $timeSeconds")->format('Y-m-d H:i:s');
            } else {
                // ไม่มีข้อมูลวัน/เวลา ให้เป็น null
                $data['publish_at'] = null;
                $data['publish_date'] = null;
                $data['publish_time'] = null;
            }
        } else {
            // draft
            if ($date && $timeSeconds) {
                $data['publish_at'] = Carbon::parse("$date $timeSeconds")->format('Y-m-d H:i:s');
            } else {
                $data['publish_at'] = null;
                if (!$date || $time) {
                    $data['publish_date'] = null;
                    $data['publish_time'] = null;
                }
            }
        }

        $policy->fill([
            'title'        => $data['title'],
            'category_id'  => $data['category_id'],
            'description'  => $data['description'],
            'status'       => $status,
            'publish_date' => $data['publish_date'],
            'publish_time' => $data['publish_time'],
            'publish_at'   => $data['publish_at'],
        ])->save();

        if (!array_key_exists('is_required_ack', $data)) {
            $data['is_required_ack'] = $policy->is_required_ack;
        }

        unset($data['created_by']);
        $data['updated_by'] = $request->user()?->id ?? auth()->id();

        $policy->fill($data);
        $policy->save();

        return response()->json($policy);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Policy $policy)
    {
        $policy->delete();
        return response()->noContent();
    }

    public function sendEmail(Request $request ,Policy $policy){
        $data = $request->validate([
            'recipients'
        ]);
    }
}

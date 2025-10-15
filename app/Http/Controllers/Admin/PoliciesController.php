<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Policy;
use Carbon\Carbon;

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
        $perPage = (int) $request->query('per', 25);

        $query = Policy::query()->when($search !== '', function ($q) use ($search) {
            $q->where(function ($w) use ($search) {
                $w->where('code', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        })
            ->orderByDesc('id');

        return $perPage === 0 ?
            response()->json($query->get())
            : response()->json($query->paginate($perPage));
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
        $data = $request->validate([
            'category_id' => ['nullable', 'exists:policy_categories,id'],
            'code' => ['nullable', 'string', 'max:100', 'unique:policies,code'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'owner_user_id' => ['nullable', 'exists:users,id'],
            'owner_org_unit_id' => ['nullable', 'exists:org_units,id'],
            'is_required_ack' => ['sometimes', 'boolean'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'publish_date' => ['nullable', 'date'],
            'publish_time' => ['nullable', 'date_format:H:i'],
        ]);

        $data['publish_at'] = null;
        if (!empty($data['publish_date'])) {
            $time = $data['publish_time'] ?? '00:00';
            $data['publish_at'] = Carbon::createFromFormat('Y-m-d H:i', "{$data['publish_date']} {$time}");
        }
        unset($data['publish_date'], $data['publish_time']);

        $data['is_required_ack'] = $data['is_required_ack'] ?? true;
        $data['created_by'] = $request->user()?->id;
        $data['updated_by'] = $request->user()?->id;

        $policy = Policy::create($data);
        return response()->json($policy, 201);
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
        $data = $request->validate([
            'category_id'       => ['nullable', 'exists:policy_categories,id'],
            'code'              => ['nullable', 'string', 'max:100', Rule::unique('policies', 'code')->ignore($policy->id)],
            'title'             => ['required', 'string', 'max:255'],
            'description'       => ['nullable', 'string'],
            'owner_user_id'     => ['nullable', 'exists:users,id'],
            'owner_org_unit_id' => ['nullable', 'exists:org_units,id'],
            'is_required_ack'   => ['sometimes', 'boolean'],
            'status'            => ['required', Rule::in(['draft', 'published', 'archived'])],
            'publish_date'      => ['nullable', 'date'],
            'publish_time'      => ['nullable', 'date_format:H:i'],
        ]);

        $publishAt = $policy->publish_at ? Carbon::parse($policy->publish_at) : null;

        if (array_key_exists('publish_date', $data) || array_key_exists('publish_time', $data)) {
            $date = $data['publish_date'] ?? ($publishAt ? $publishAt->format('Y-m-d') : null);
            $time = $data['publish_time'] ?? ($publishAt ? $publishAt->format('H:i') : '00:00');

            $data['publish_at'] = $date ? Carbon::createFromFormat('Y-m-d H:i', "{$date} {$time}") : null;
        }

        unset($data['publish_date'], $data['publish_time']);

        if (!array_key_exists('is_required_ack', $data)) {
            $data['is_required_ack'] = $policy->is_required_ack;
        }

        unset($data['created_by']);
        $data['updated_by'] = $request->user()?->id;

        $policy->fill($data)->save();

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
}

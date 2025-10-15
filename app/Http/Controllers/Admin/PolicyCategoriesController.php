<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\PolicyCategory;

class PolicyCategoriesController extends Controller
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

        $query = PolicyCategory::query()
            ->when($search !== '', function ($qr) use ($search) {
                $qr->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            })
            ->orderByDesc('id');

        return $perPage === 0 ?
            response()->json($query->get())
            : response()->json($query->paginate($perPage));
    }

    public function select(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $limit = (int) $request->query('limit', 50);

        $rows = PolicyCategory::query()
            ->when($search !== '', function ($qr) use ($search) {
                $qr->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'code', 'name', 'is_mandatory']);

        $data = $rows->map(fn($r) => [
            'id' => $r->id,
            'label' => $r->name ?: $r->code,
            'code' => $r->code,
            'is_mandatory' => (bool)$r->is_mandatory
        ]);

        return response()->json($data);
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
            'code' => ['nullable', 'string', 'max:255', 'unique:policy_categories, code'],
            'name' => ['nullable', 'string', 'max:255'],
            'is_mandatory' => ['sometimes', 'boolean'],
        ]);

        $data['is_mandatory'] = $data['is_mandatory'] ?? true;
        $data['created_at'] = now();

        $row = PolicyCategory::create($data);
        return response()->json($row, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(PolicyCategory $category)
    {
        return response()->json($category);
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
    public function update(Request $request, PolicyCategory $category)
    {
        $data = $request->validate([
            'code'      => ['nullable', 'string', 'max:50', Rule::unique('policy_categories', 'code')->ignore($category->id)],
            'name'   => ['required', 'string', 'max:255'],
            'is_mandatory' => ['sometimes', 'boolean'],
        ]);

        $category->fill($data)->save();
        return response()->json($category);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(PolicyCategory $category)
    {
        $category->delete();
        return response()->noContent();
    }
}

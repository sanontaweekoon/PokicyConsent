<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use Illuminate\Validation\Rule;

class CompaniesController extends Controller
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

        $hasActive = $request->has('active');
        $isActive  = $hasActive ? $request->boolean('active') : null;

        $query = Company::query()
            ->when($search !== '', function ($qr) use ($search) {
                $qr->where(function ($w) use ($search) {
                    $w->where('code', 'like', "%{$search}%")
                        ->orWhere('name_th', 'like', "%{$search}%")
                        ->orWhere('name_en', 'like', "%{$search}%");
                });
            })
            ->when($hasActive, fn($qr) => $qr->where('is_active', $isActive))
            ->orderByDesc('id');

        return $perPage === 0
            ? response()->json($query->get())
            : response()->json($query->paginate($perPage));
    }

    public function select(Request $request)
    {
        $search    = trim((string) $request->query('q', ''));
        $limit     = (int) $request->query('limit', 50);

        $hasActive = $request->has('active');
        $isActive  = $hasActive ? $request->boolean('active') : true;

        $rows = Company::query()
            ->when($search !== '', function ($qr) use ($search) {
                $qr->where(function ($w) use ($search) {
                    $w->where('code', 'like', "%{$search}%")
                        ->orWhere('name_th', 'like', "%{$search}%")
                        ->orWhere('name_en', 'like', "%{$search}%");
                });
            })
            ->when($hasActive, fn($qr) => $qr->where('is_active', $isActive))
            ->orderBy('name_en')
            ->limit($limit)
            ->get(['id', 'code', 'name_th', 'name_en']);

        $data = $rows->map(fn($r) => [
            'id'    => $r->id,
            'label' => $r->name_en ?: ($r->name_th ?: $r->code),
            'code'  => $r->code,
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
            'code'      => ['nullable', 'string', 'max:50', 'unique:companies,code'],
            'name_th'   => ['nullable', 'string', 'max:255', 'required_without:name_en'],
            'name_en'   => ['required', 'string', 'max:255', 'required_without:name_th'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $data['is_active']  = $data['is_active'] ?? true;
        $data['created_at'] = now();

        $company = Company::create($data);
        return response()->json($company, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Company $company)
    {
        return response()->json($company);
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
    public function update(Request $request, Company $company)
    {
        $data = $request->validate([
            'code'      => ['nullable', 'string', 'max:50', Rule::unique('companies', 'code')->ignore($company->id)],
            'name_th'   => ['nullable', 'string', 'max:255', 'required_without:name_en'],
            'name_en'   => ['required', 'string', 'max:255', 'required_without:name_th'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $company->fill($data)->save();
        return response()->json($company);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Company $company)
    {
        try {
            $company->delete();
            return response()->noContent();
        } catch (\Throwable $e) {
            if (method_exists($e, 'getCode') && (string)$e->getCode() === '23000') {
                return response()->json([
                    'message' => 'ไม่สามารถลบได้: บริษัทนี้มีการอ้างอิงโดยบันทึกอื่น'
                ], 409);
            }
            report($e);
            return response()->json(['message' => 'Server error'], 500);
        }
    }
}

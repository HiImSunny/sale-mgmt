<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttributeController extends Controller
{
    public function index(Request $request)
    {
        $q = Attribute::query();

        if ($search = $request->input('search')) {
            $q->where('name', 'like', "%$search%");
        }

        $stats = [
            'total' => Attribute::count(),
            'active' => Attribute::where('status', 1)->count(),
            'inactive' => Attribute::where('status', 0)->count(),
        ];

        $attributes = $q->orderBy('created_at', 'desc')->paginate(15)->appends($request->all());

        return view('attribute.index', compact('attributes', 'stats'));
    }

    public function create()
    {
        return view('attribute.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:attributes,slug',
            'status' => ['nullable', Rule::in([0,1])]
        ]);

        Attribute::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'status' => $request->status ?? 1,
        ]);

        return redirect()->route('attributes.index')->with('success', 'Thuộc tính đã được tạo!');
    }

    public function show(Attribute $attribute)
    {
        $attribute->load('values');
        return view('attribute.show', compact('attribute'));
    }

    public function edit(Attribute $attribute)
    {
        return view('attribute.edit', compact('attribute'));
    }

    public function update(Request $request, Attribute $attribute)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:attributes,slug,'.$attribute->id,
            'status' => ['nullable', Rule::in([0,1])]
        ]);

        $attribute->update($request->all());

        return redirect()->route('attributes.show', $attribute)->with('success', 'Cập nhật thành công!');
    }

    public function destroy(Attribute $attribute)
    {
        $attribute->delete();
        return redirect()->route('attributes.index')->with('success', 'Đã xóa thuộc tính!');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;

class AttributeValueController extends Controller
{

    public function create(Attribute $attribute)
    {
        return view('attribute.value.create', compact('attribute'));
    }

    public function store(Request $request, Attribute $attribute)
    {
        $request->validate([
            'value' => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);
        $attribute->values()->create([
            'value' => $request->value,
            'sort_order' => $request->sort_order ?? 0,
        ]);
        return redirect()->route('attributes.values.index', $attribute)
            ->with('success', 'Đã thêm giá trị thuộc tính!');
    }

    public function edit(Attribute $attribute, AttributeValue $value)
    {
        return view('attribute.value.edit', compact('attribute', 'value'));
    }

    public function update(Request $request, Attribute $attribute, AttributeValue $value)
    {
        $request->validate([
            'value' => 'required|string|max:255',
            'sort_order' => 'nullable|integer'
        ]);
        $value->update($request->only('value', 'sort_order'));
        return redirect()->route('attributes.values.index', $attribute)
            ->with('success', 'Cập nhật giá trị thành công!');
    }

    public function destroy(Attribute $attribute, AttributeValue $value)
    {
        $value->delete();
        return redirect()->route('attributes.values.index', $attribute)
            ->with('success', 'Đã xóa giá trị thuộc tính!');
    }
}

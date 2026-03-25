<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $q = Category::query();

        if ($search = $request->input('search')) {
            $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('slug', 'like', "%$search%");
            });
        }

        $categoriesParent = Category::whereNull('parent_id')->get();

        $categories = $q->with('parent')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->appends($request->all());

        $stats = [
            'total'    => Category::count(),
            'active'   => Category::where('status', 1)->count(),
            'inactive' => Category::where('status', 0)->count(),
        ];

        return view('category.index', compact('categories', 'categoriesParent', 'stats'));
    }

    public function create()
    {
        $categoriesParent = Category::whereNull('parent_id')->get();
        return view('category.create', compact('categoriesParent'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'slug'      => 'required|string|max:255|unique:categories,slug',
            'parent_id' => 'nullable|exists:categories,id',
            'status'    => ['nullable', Rule::in([0, 1])]
        ]);

        Category::create([
            'name'      => $request->name,
            'slug'      => $request->slug,
            'parent_id' => $request->parent_id,
            'status'    => $request->status ?? 1,
        ]);

        return redirect()->route('categories.index')->with('success', 'Tạo danh mục mới thành công!');
    }

    public function show(Category $category)
    {
        $category->load('parent', 'children');
        return view('category.show', compact('category'));
    }

    public function edit(Category $category)
    {
        $categoriesParent = Category::whereNull('parent_id')->where('id', '!=', $category->id)->get();
        return view('category.edit', compact('category', 'categoriesParent'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'slug'      => 'required|string|max:255|unique:categories,slug,' . $category->id,
            'parent_id' => 'nullable|exists:categories,id|not_in:' . $category->id,
            'status'    => ['nullable', Rule::in([0, 1])]
        ]);

        $category->update([
            'name'      => $request->name,
            'slug'      => $request->slug,
            'parent_id' => $request->parent_id,
            'status'    => $request->status ?? 1,
        ]);

        return redirect()->route('categories.show', $category)->with('success', 'Cập nhật danh mục thành công!');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Xóa danh mục thành công!');
    }

    // Optional: Bulk delete
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id'
        ]);

        Category::whereIn('id', $request->category_ids)->delete();

        return response()->json(['success' => true]);
    }

    // Optional: Export CSV
    public function export(Request $request)
    {
        $query = Category::query();

        if ($ids = $request->category_ids) {
            $query->whereIn('id', $ids);
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('slug', 'like', '%'.$request->search.'%');
        }

        $categories = $query->get();

        $filename = 'categories_' . date('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];
        $handle = fopen('php://temp', 'w+');
        fputcsv($handle, ['ID', 'Tên', 'Slug', 'Danh mục cha', 'Trạng thái', 'Ngày tạo']);
        foreach ($categories as $cat) {
            fputcsv($handle, [
                $cat->id,
                $cat->name,
                $cat->slug,
                optional($cat->parent)->name,
                $cat->status ? 'Hoạt động' : 'Ẩn',
                $cat->created_at
            ]);
        }
        rewind($handle);
        $output = stream_get_contents($handle);
        fclose($handle);

        return response($output, 200, $headers);
    }
}

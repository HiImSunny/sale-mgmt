<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q = User::query();

        // Filter
        if ($search = $request->input('search')) {
            $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        }
        if ($role = $request->input('role')) {
            $q->where('role', $role);
        }

        $users = $q->orderBy('created_at', 'desc')->paginate(15)->appends($request->all());

        // Stats
        $stats = [
            'total'    => User::count(),
            'admin'    => User::where('role', 'admin')->count(),
            'seller'   => User::where('role', 'seller')->count(),
        ];

        return view('user.index', compact('users', 'stats'));
    }

    public function create()
    {
        return view('user.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|confirmed|min:6',
            'role'     => ['required', Rule::in(['admin', 'seller', 'customer'])],
            'avatar'   => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
            'avatar'   => $avatarPath,
        ]);

        return redirect()->route('users.index')->with('success', 'Đã tạo người dùng mới thành công!');
    }

    public function show(User $user)
    {
        // Stats demo, thêm tùy từng app
        $stats = [
            // Giả lập số liệu khách hàng
            'total_orders'   => optional($user->customerProfile)->total_orders ?? 0,
            'total_spent'    => optional($user->customerProfile)->total_spent ?? 0,
            'avg_order_value'=> 0, // Optional
            // Giả lập số liệu nhân viên
            'orders_processed'   => 0,
            'revenue_generated'  => 0,
            'customers_served'   => 0,
        ];
        $recentOrders = collect(); // Lấy dữ liệu từ order model nếu có
        $activities = collect(); // Lấy dữ liệu từ activity logs nếu muốn

        return view('user.show', compact('user', 'stats', 'recentOrders', 'activities'));
    }

    public function edit(User $user)
    {
        return view('user.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'email'  => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role'   => ['required', Rule::in(['admin', 'seller', 'customer'])],
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;

        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('users.show', $user)->with('success', 'Đã cập nhật người dùng thành công!');
    }

    public function destroy(User $user)
    {
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Đã xóa người dùng thành công!');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        $users = User::whereIn('id', $request->user_ids)->get();
        foreach ($users as $user) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->delete();
        }

        return response()->json(['success' => true]);
    }

    public function export(Request $request)
    {
        $query = User::query();

        if ($userIds = $request->user_ids) {
            $query->whereIn('id', $userIds);
        }
        if ($request->filled('search')) {
            $query->where(function($q) use($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->get();

        $filename = 'users_' . date('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];
        $handle = fopen('php://temp', 'w+');
        fputcsv($handle, ['ID', 'Tên', 'Email', 'Vai trò', 'Ngày tạo']);
        foreach ($users as $user) {
            fputcsv($handle, [$user->id, $user->name, $user->email, $user->role, $user->created_at]);
        }
        rewind($handle);
        $output = stream_get_contents($handle);
        fclose($handle);

        return response($output, 200, $headers);
    }
}

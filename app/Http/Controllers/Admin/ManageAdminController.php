<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ManageAdminController extends Controller
{
    /**
     * Get all admin users with stats and filtering
     */
    public function index(Request $request)
    {
        try {
            // Calculate stats - only for non-user roles
            $totalAdmins = User::whereIn('role', ['admin', 'support', 'editor', 'chief_editor'])->count();
            $onlineAdmins = User::whereIn('role', ['admin', 'support', 'editor', 'chief_editor'])
                ->whereNotNull('fcmToken')
                ->count();
            $offlineAdmins = $totalAdmins - $onlineAdmins;

            // Build query - get only admin users
            $query = User::whereIn('role', ['admin', 'support', 'editor', 'chief_editor'])
                ->withCount('orders');

            // Filter by online/offline status
            if ($request->has('status') && $request->status && $request->status !== 'All') {
                if ($request->status === 'Online') {
                    $query->whereNotNull('fcmToken');
                } elseif ($request->status === 'Offline') {
                    $query->whereNull('fcmToken');
                }
            }

            // Filter by role
            if ($request->has('role') && $request->role && $request->role !== 'All') {
                $query->where('role', strtolower(str_replace(' ', '_', $request->role)));
            }

            // Filter by date
            if ($request->has('date') && $request->date) {
                $query->whereDate('created_at', $request->date);
            }

            // Search
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $perPage = $request->get('per_page', 20);
            $admins = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);

            // Transform data
            $transformedAdmins = $admins->getCollection()->map(function ($admin) {
                return [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'email' => $admin->email,
                    'role' => ucfirst(str_replace('_', ' ', $admin->role)),
                    'role_raw' => $admin->role,
                    'profile_picture' => $admin->profile_picture,
                    'no_of_orders' => $admin->orders_count ?? 0,
                    'date_registered' => $admin->created_at->format('m/d/y - h:i A'),
                    'is_online' => !empty($admin->fcmToken),
                ];
            });

            return ResponseHelper::success([
                'stats' => [
                    'total_admins' => $totalAdmins,
                    'online_admins' => $onlineAdmins,
                    'offline_admins' => $offlineAdmins,
                ],
                'admins' => $transformedAdmins,
                'pagination' => [
                    'current_page' => $admins->currentPage(),
                    'last_page' => $admins->lastPage(),
                    'per_page' => $admins->perPage(),
                    'total' => $admins->total(),
                ]
            ], 'Admins fetched successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to fetch admins: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get single admin details
     */
    public function show($id)
    {
        try {
            $admin = User::whereIn('role', ['admin', 'support', 'editor', 'chief_editor'])
                ->withCount('orders')
                ->findOrFail($id);

            $data = [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'phone' => $admin->phone ?? 'N/A',
                'role' => ucfirst(str_replace('_', ' ', $admin->role)),
                'role_raw' => $admin->role,
                'profile_picture' => $admin->profile_picture,
                'is_online' => !empty($admin->fcmToken),
                'no_of_orders' => $admin->orders_count ?? 0,
                'date_registered' => $admin->created_at->format('m/d/y - h:i A'),
                'created_at' => $admin->created_at,
            ];

            return ResponseHelper::success($data, 'Admin details fetched successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Admin not found: ' . $e->getMessage(), 404);
        }
    }

    /**
     * Create new admin user
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
                'role' => 'required|in:admin,support,editor,chief_editor',
                'profile_picture' => 'nullable|image|max:5120', // 5MB max
            ]);

            if ($validator->fails()) {
                return ResponseHelper::error($validator->errors()->first(), 422);
            }

            $adminData = [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ];

            // Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                $path = $request->file('profile_picture')->store('profile_picture', 'public');
                $adminData['profile_picture'] = $path;
            }

            $admin = User::create($adminData);

            return ResponseHelper::success($admin, 'Admin created successfully', 201);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to create admin: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update admin user
     */
    public function update(Request $request, $id)
    {
        try {
            $admin = User::whereIn('role', ['admin', 'support', 'editor', 'chief_editor'])
                ->findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|unique:users,email,' . $id,
                'password' => 'nullable|string|min:6',
                'role' => 'sometimes|required|in:admin,support,editor,chief_editor',
                'profile_picture' => 'nullable|image|max:5120',
            ]);

            if ($validator->fails()) {
                return ResponseHelper::error($validator->errors()->first(), 422);
            }

            // Update basic fields
            if ($request->has('name')) $admin->name = $request->name;
            if ($request->has('email')) $admin->email = $request->email;
            if ($request->has('role')) $admin->role = $request->role;

            // Update password if provided
            if ($request->filled('password')) {
                $admin->password = Hash::make($request->password);
            }

            // Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                // Delete old picture
                if ($admin->getRawOriginal('profile_picture') && Storage::disk('public')->exists($admin->getRawOriginal('profile_picture'))) {
                    Storage::disk('public')->delete($admin->getRawOriginal('profile_picture'));
                }
                
                $path = $request->file('profile_picture')->store('profile_picture', 'public');
                $admin->profile_picture = $path;
            }

            $admin->save();

            return ResponseHelper::success($admin, 'Admin updated successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to update admin: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete admin user
     */
    public function destroy($id)
    {
        try {
            $admin = User::whereIn('role', ['admin', 'support', 'editor', 'chief_editor'])
                ->findOrFail($id);
            
            // Delete profile picture if exists
            if ($admin->getRawOriginal('profile_picture') && Storage::disk('public')->exists($admin->getRawOriginal('profile_picture'))) {
                Storage::disk('public')->delete($admin->getRawOriginal('profile_picture'));
            }

            $admin->delete();

            return ResponseHelper::success(null, 'Admin deleted successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to delete admin: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Bulk actions for admin users
     */
    public function bulkAction(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'admin_ids' => 'required|array',
                'admin_ids.*' => 'exists:users,id',
                'action' => 'required|in:delete,change_role',
                'role' => 'required_if:action,change_role|in:admin,support,editor,chief_editor',
            ]);

            if ($validator->fails()) {
                return ResponseHelper::error($validator->errors()->first(), 422);
            }

            $adminIds = $request->admin_ids;
            $action = $request->action;

            switch ($action) {
                case 'change_role':
                    User::whereIn('id', $adminIds)
                        ->whereIn('role', ['admin', 'support', 'editor', 'chief_editor'])
                        ->update(['role' => $request->role]);
                    break;
                case 'delete':
                    User::whereIn('id', $adminIds)
                        ->whereIn('role', ['admin', 'support', 'editor', 'chief_editor'])
                        ->delete();
                    break;
            }

            return ResponseHelper::success([
                'affected_count' => count($adminIds),
                'action' => $action
            ], 'Bulk action completed successfully', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to perform bulk action: ' . $e->getMessage(), 500);
        }
    }
}


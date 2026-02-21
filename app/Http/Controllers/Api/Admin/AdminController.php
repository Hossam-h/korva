<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Admin\CreateAdminRequest;
use App\Models\Admin;

class AdminController extends BaseController
{
    /**
     * Create a new Admin.
     * POST /admin/admins
     */
    public function store(CreateAdminRequest $request)
    {
        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        if ($request->has('role')) {
            $admin->assignRole($request->role);
        }

        $admin->load('roles.permissions');

        return $this->sendResponse($admin, 'Admin created successfully');
    }
}

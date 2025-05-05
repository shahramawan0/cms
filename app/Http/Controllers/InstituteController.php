<?php

namespace App\Http\Controllers;

use App\Models\Institute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InstituteController extends Controller
{
    public function index()
    {
        return view('institute.index');
    }

    public function getInstitutes()
    {
        $institutes = Institute::query();

        return datatables()->of($institutes)
            ->addColumn('logo', function($institute) {
                return $institute->logo 
                    ? '<img src="'.asset('storage/'.$institute->logo).'" width="50" height="50" class="rounded-circle">'
                    : '<div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;">
                          <i class="fas fa-university text-white"></i>
                       </div>';
            })
            ->addColumn('status', function($institute) {
                return $institute->is_active 
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>';
            })
            ->addColumn('action', function($institute) {
                return '
                    <div class="btn-group">
                        <button class="btn btn-sm btn-primary edit-btn me-1" data-id="'.$institute->id.'">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="'.$institute->id.'">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['logo', 'status', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:institutes,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean'
        ]);

        try {
            $data = $request->except('logo');

            if ($request->hasFile('logo')) {
                $data['logo'] = $request->file('logo')->store('institutes/logo', 'public');
            }

            Institute::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Institute created successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating institute: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        $institute = Institute::findOrFail($id);
        return response()->json($institute);
    }

    public function update(Request $request, $id)
    {
        $institute = Institute::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:institutes,email,'.$institute->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean'
        ]);

        try {
            $data = $request->except('logo');

            if ($request->hasFile('logo')) {
                // Delete old logo if exists
                if ($institute->logo) {
                    Storage::disk('public')->delete($institute->logo);
                }
                $data['logo'] = $request->file('logo')->store('institutes/logo', 'public');
            }

            $institute->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Institute updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating institute: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $institute = Institute::findOrFail($id);
            
            if ($institute->logo) {
                Storage::disk('public')->delete($institute->logo);
            }
            
            $institute->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Institute deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting institute: ' . $e->getMessage()
            ], 500);
        }
    }
}
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

    public function create()
    {
        return view('institute.create_institute');
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

        $data = $request->except('logo');

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('institutes/logo', 'public');
        }

        Institute::create($data);

        return redirect()->route('institutes.index')->with('success', 'Institute created successfully.');
    }

    public function edit($id)
    {
        $institute = Institute::findOrFail($id);
        return view('institute.create_institute', compact('institute'));
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

        $data = $request->except('logo');

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($institute->logo) {
                Storage::disk('public')->delete($institute->logo);
            }
            $data['logo'] = $request->file('logo')->store('institutes/logo', 'public');
        }

        $institute->update($data);

        return redirect()->route('institutes.index')->with('success', 'Institute updated successfully.');
    }

    public function destroy($id)
    {
        $institute = Institute::findOrFail($id);
        
        if ($institute->logo) {
            Storage::disk('public')->delete($institute->logo);
        }
        
        $institute->delete();
        
        return response()->json(['success' => 'Institute deleted successfully.']);
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
                        <a href="'.route('institutes.edit', $institute->id).'" class="btn btn-sm btn-info me-1">
                            <i class="fas fa-edit"></i>
                            Edit
                        </a>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="'.$institute->id.'">
                            <i class="fas fa-trash"></i>
                            Delete
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['logo', 'status', 'action'])
            ->make(true);
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Classes;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function index()
    {
        $classes = Classes::get();
        return view('sections.index', compact('classes'));
    }

    public function getSections()
    {
        $sections = Section::with('class')->select('sections.*');

        return datatables()->of($sections)
            ->addColumn('status', function($section) {
                $badge = $section->status === 'active' ? 'success' : 
                         ($section->status === 'inactive' ? 'warning' : 'secondary');
                $text = ucfirst($section->status);
                return '<span class="badge bg-'.$badge.'">'.$text.'</span>';
            })
            ->addColumn('action', function($section) {
                return '
                    <div class="btn-group">
                        <button class="btn btn-sm btn-primary edit-btn me-1" data-id="'.$section->id.'">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="'.$section->id.'">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'section_name' => 'required|string|max:255',
            'class_id' => 'required|exists:classes,id',
            'status' => 'required|in:active,inactive,archived',
            'description' => 'nullable|string'
        ]);

        $section = Section::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Section created successfully!'
        ]);
    }

    public function edit($id)
    {
        $section = Section::findOrFail($id);
        return response()->json($section);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'section_name' => 'required|string|max:255',
            'class_id' => 'required|exists:classes,id',
            'status' => 'required|in:active,inactive,archived',
            'description' => 'nullable|string'
        ]);

        $section = Section::findOrFail($id);
        $section->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Section updated successfully!'
        ]);
    }

    public function destroy($id)
    {
        $section = Section::findOrFail($id);
        $section->delete();

        return response()->json([
            'success' => true,
            'message' => 'Section deleted successfully!'
        ]);
    }
}
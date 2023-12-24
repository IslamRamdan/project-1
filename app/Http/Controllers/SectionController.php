<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    // all sections
    public function getSections()
    {
        $sections = Section::all();
        return $sections;
    }

    // get section by id
    public function oneSection($id)
    {
        $section = Section::where("id", $id)->first();
        return $section;
    }

    // add section
    public function addSection(Request $request)
    {
        $request->validate([
            "title" => 'required',
        ]);

        $section = new Section();
        $section->title = $request->title;
        $section->save();

        return response()->json([
            "section" => $section,
            "message" => "section added successfully"
        ], 201);
    }

    // update section
    public function updateSection(Request $request, $id)
    {
        $request->validate([
            "title" => 'required',
        ]);
        $section = Section::findOrFail($id);
        $section->title = $request->title;
        $section->save();

        return response()->json([
            "section" => $section,
            "message" => "section updated successfully"
        ], 200);
    }

    // delete section
    public function deleteSection($id)
    {
        $section = Section::findOrFail($id);
        $section->delete();

        return response()->json([
            "message" => "section deleted successfully"
        ], 200);
    }
}

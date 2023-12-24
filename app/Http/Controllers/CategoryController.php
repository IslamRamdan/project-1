<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    //get all categories
    public function categories()
    {
        $categories = Category::all();
        return $categories;
    }

    // get categories by section id
    public function sectionCategories($section_id)
    {
        $section = Section::findOrFail($section_id);
        return $section->categories;
    }

    //get category by id
    public function categoryId($id)
    {
        $category = Category::findOrFail($id);
        return $category;
    }

    //add category
    public function addCategory(Request $request)
    {
        $request->validate([
            'title' => "required",
            'image' => "required",
            'section_id' => "required",
        ]);

        $section = Section::findOrFail($request->section_id);

        $category = new Category();
        $category->title = $request->title;
        $category->section_id = $request->section_id;

        $filename = Str::random(32) . "." . $request->image->getClientOriginalExtension();
        $request->image->move('uploads/', $filename);
        $category->image = $filename;

        $section->categories()->save($category);

        return response()->json([
            "category" => $category,
            'message' => 'category add successfully'
        ], 201);
    }

    // update category
    public function updateCategory(Request $request, $id)
    {
        $request->validate([
            'title' => "required",
            'image' => 'nullable',
            'section_id' => 'nullable',
        ]);

        $category = Category::findOrFail($id);
        $category->title = $request->title;

        if ($request->file('image')) {
            $filename = Str::random(32) . "." . $request->image->getClientOriginalExtension();
            $request->image->move('uploads/', $filename);
            $category->image = $filename;
        }
        if ($request->section_id) {
            $section = Section::findOrFail($request->section_id);
            $section->categories()->save($category);
        }

        $category->save();
        return response()->json([
            'category' => $category,
            'message' => 'category updated successfully'
        ], 200);
    }

    // delete category
    public function deleteCategory($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json([
            "message" => "Category deleted successfully",
        ], 200);
    }
}

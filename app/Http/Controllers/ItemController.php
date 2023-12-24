<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    //get all items
    public function items()
    {
        $items = Item::all();
        return $items;
    }

    //get items by category
    public function itemsByCategory($categoty_id)
    {
        $category = Category::findOrFail($categoty_id);
        return $category->items;
    }

    //get item by id
    public function itemById($id)
    {
        $item = Item::findOrFail($id);
        return $item;
    }

    //add a new item
    public function addItem(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'details' => 'required',
            'images' => 'required',
            'price' => 'required',
            'coin' => 'required',
            'available' => 'required',
            'rate' => 'nullable',
            'category_id' => 'required'
        ]);

        $category = Category::where('id', $request->category_id)->first();
        if (!$category) {
            return response()->json(['error' => 'category not found'], 400);
        }

        $item = new Item();
        $item->name = $request->name;
        $item->details = $request->details;
        $item->price = strval($request->price);
        $item->coin = $request->coin;
        $item->rate = '0';
        $item->available = strval($request->available);
        $item->category_id = $request->category_id;

        $imagesName = [];
        $response = [];
        if ($request->has('images')) {
            foreach ($request->file('images') as $file) {
                $filename = Str::random(32) . "." . $file->getClientOriginalExtension();
                $file->move('uploads/', $filename);
                array_push($imagesName, $filename);
            }

            $item->images = $imagesName;
            $category->items()->save($item);

            $response["status"] = "successs";
            $response["item"] = $item;
            $response["message"] = "item added successfully";
        }
        return response()->json($response, 200);
    }

    // update item 
    public function updateItem(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'details' => 'required',
            'images' => 'nullable',
            'price' => 'required',
            'coin' => 'required',
            'available' => 'required',
            'delete_image' => 'nullable',
            'category_id' => 'required'
        ]);

        $item = Item::findOrFail($id);

        $category = Category::where('id', $request->category_id)->first();
        if (!$category) {
            return response()->json(['error' => 'category not found'], 400);
        }

        $item->name = $request->name;
        $item->details = $request->details;
        $item->price = strval($request->price);
        $item->coin = $request->coin;
        $item->rate = '0';
        $item->available = strval($request->available);
        $item->category_id = $request->category_id;

        $imagesA = $item->images;
        if ($request->has('images')) {
            foreach ($request->file('images') as $file) {
                $filename = Str::random(32) . "." . $file->getClientOriginalExtension();
                $file->move('uploads/', $filename);
                array_push($imagesA, $filename);
            }
        }

        if ($request->has('delete_image')) {
            foreach ($request->delete_image as $image) {
                if (in_array($image, $imagesA)) {
                    $index = array_search($image, $imagesA);
                    if ($index !== false) {
                        unset($imagesA[$index]);
                    }
                }
            }
        }

        $item->images = [...$imagesA];
        $category->items()->save($item);
        return response()->json([
            'items' => $item,
            'message' => 'item updated successfully'
        ], 200);
    }

    // delete item
    public function deleteItem($id)
    {
        $item = Item::findOrFail($id);
        $item->delete();
        return response()->json([
            'message' => 'item deleted successfully'
        ], 200);
    }
}

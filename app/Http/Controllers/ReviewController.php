<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    // get review item
    public function reviews($item_id)
    {
        $item = Item::findOrFail($item_id);

        $allReview = [];
        foreach ($item->reviews as $review) {
            $review->user = $review->user;
            array_push($allReview, $review);
        }
        return $item->reviews;
    }

    // get review by id
    public function reviewsId($id)
    {
        $review = Review::findOrFail($id);
        $review->user = $review->user;
        return $review;
    }

    // add review
    public function addReview(Request $request)
    {
        $request->validate([
            'stars' => 'required',
            'description' => 'required',
            'item_id' => 'required',
        ]);

        $item = Item::findOrFail($request->item_id);

        $user_id = auth()->user()->id;

        $review = new Review();
        $review->stars = $request->stars;
        $review->description = $request->description;
        $review->user_id = $user_id;

        $item->reviews()->save($review);

        // rate
        $reviewsItem = $item->reviews;
        $stars = 0;
        foreach ($reviewsItem as $rev) {
            $stars += $rev->stars;
        }

        $rate = $stars / count($reviewsItem);
        $item->rate = $rate;
        $item->save();

        return response()->json([
            'message' => 'review added successfully',
            'review' => $review
        ], 201);
    }

    // update review
    public function updateReview(Request $request, $id)
    {
        $request->validate([
            'stars' => 'required',
            'description' => 'required',
        ]);

        $review = Review::findOrFail($id);
        $user_id = auth()->user()->id;

        if ($user_id !== $review->user_id) {
            return response()->json([
                'message' => 'You do not have the permissions'
            ], 400);
        }

        $review->stars = $request->stars;
        $review->description = $request->description;

        $review->save();

        // rate
        $item = $review->item;

        $reviewsItem = $item->reviews;
        $stars = 0;
        foreach ($reviewsItem as $rev) {
            $stars += $rev->stars;
        }

        $rate = $stars / count($reviewsItem);
        $item->rate = $rate;
        $item->save();

        return response()->json([
            'message' => 'review updated successfully',
            'review' => $review
        ], 200);
    }

    // update review
    public function deleteReview($id)
    {
        $review = Review::findOrFail($id);
        $user_id = auth()->user()->id;

        if ($user_id !== $review->user_id) {
            return response()->json([
                'message' => 'You do not have the permissions'
            ], 400);
        }

        $review->delete();

        // rate
        $item = $review->item;

        $reviewsItem = $item->reviews;
        $stars = 0;
        foreach ($reviewsItem as $rev) {
            $stars += $rev->stars;
        }

        $rate = $stars / count($reviewsItem);
        $item->rate = $rate;
        $item->save();

        return response()->json([
            'message' => 'review deleted successfully',
        ], 200);
    }
}

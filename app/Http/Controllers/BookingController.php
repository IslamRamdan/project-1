<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Item;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    //bookings
    public function bookings()
    {
        $bookings = Booking::all();

        foreach ($bookings as $booking) {
            $booking->user = $booking->user;
            $booking->item = $booking->item;
        }

        return $bookings;
    }

    public function addBooking(Request $request)
    {
        $request->validate([
            'day' => 'required',
            'time' => 'required',
            'system' => 'required',
            'guests' => 'required',
            'item_id' => 'required'
        ]);

        $bookings = Booking::where(['time' => $request->time, 'day' => $request->day, 'system' => $request->system])->first();
        if ($bookings) {
            return response()->json([
                'message' => 'The appointment is booked in advance.'
            ], 400);
        }

        $item = Item::find($request->item_id);
        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'The Service not found.'
            ], 400);
        }

        $booking = new Booking();
        $booking->day = $request->day;
        $booking->time = $request->time;
        $booking->system = $request->system;
        $booking->guests = $request->guests;
        $booking->status = 'null';
        $booking->user_id = auth()->user()->id;
        $item->bookings()->save($booking);
        $booking->item = $booking->item;
        $booking->user = $booking->user;

        return response()->json([
            'success' => true,
            'message' => 'Booking saved successfully',
            'bookings' => $booking
        ], 201);
    }

    // update booking
    public function updateBooking(Request $request, $id)
    {
        $request->validate([
            'day' => 'required',
            'time' => 'required',
            'system' => 'required',
            'guests' => 'required',
        ]);

        $booking = Booking::findOrFail($id);
        if (!$booking) {
            return response()->json([
                'message' => 'Reservation not available.'
            ], 400);
        }

        if (auth()->user()->id !== $booking->user_id) {
            return response()->json([
                'message' => 'You do not have permission to edit'
            ], 400);
        }

        $bookings = Booking::where(['time' => $request->time, 'day' => $request->day, 'system' => $request->system])->first();
        if ($bookings) {
            if ($bookings->id != $request->id) {
                return response()->json([
                    'message' => 'The appointment is booked in advance.'
                ], 400);
            }
        }

        $booking->day = $request->day;
        $booking->time = $request->time;
        $booking->system = $request->system;
        $booking->guests = $request->guests;
        $booking->save();
        $booking->item = $booking->item;
        $booking->user = $booking->user;

        return response()->json([
            'success' => true,
            'message' => 'Booking updated successfully',
            'booking' => $booking
        ], 200);
    }

    // cancel booking
    public function cancelBooking($id)
    {
        $booking = Booking::findOrFail($id);
        if (!$booking) {
            return response()->json([
                'message' => 'Booking not found',
            ], 400);
        }

        if ($booking->user_id != auth()->user()->id) {
            return response()->json([
                'message' => 'You do not have permission to edit'
            ], 400);
        }

        $booking->status = 'cancel';
        $booking->save();

        return response()->json([
            'success' => true,
            'message' => 'Booking canceled',
        ], 200);
    }

    // receive booking
    public function receiveBooking($id)
    {
        $booking = Booking::findOrFail($id);
        if (!$booking) {
            return response()->json([
                'message' => 'Booking not found',
            ], 400);
        }

        if ($booking->status == 'cancel') {
            return response()->json([
                'message' => 'The reservation cannot be received because it is cancelled',
            ], 400);
        }

        $booking->status = 'receive';
        $booking->save();

        return response()->json([
            'success' => true,
            'message' => 'Booking receive',
        ], 200);
    }

    // get bookings receive
    public function receives()
    {
        $bookings = Booking::where('status', 'receive')->get();
        foreach ($bookings as $book) {
            $book->user = $book->user;
            $book->item = $book->item;
        }
        return $bookings;
    }

    // get bookings receive
    public function bookingCanceled()
    {
        $bookings = Booking::where('status', 'cancel')->get();
        foreach ($bookings as $book) {
            $book->user = $book->user;
            $book->item = $book->item;
        }
        return $bookings;
    }
}

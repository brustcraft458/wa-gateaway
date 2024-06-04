<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MessageController extends Controller
{
    public function read(Request $request)
    {
        $status = $request->input('status', 'all');
        $customer = $request->customer;

        $messages = Message::query();
        if ($customer['role'] != 'system') {
            $messages->where('customer_id', $customer['id']);
        }
        if ($status != 'all') {
            $messages->where('status', $status);
        }
        $messages = $messages->get();

        foreach ($messages as $message) {
            $message["callback_url"] = urlMainServer() . "/api/messages/" . $message['id'];
        }

        return response()->json([
            'success' => true,
            'message' => 'Message List',
            'data' => $messages
        ], 200);
    }

    public function readById(Request $request, $id)
    {
        $message = Message::find($id);
        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => 'Message Not Found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Message Data',
            'data' => $message
        ], 200);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|numeric',
            'text' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error Validation',
                'data' => $validator->errors()
            ], 422);
        }

        $customer = $request->customer;
        $data = $validator->validated();

        $data["status"] = "pending";
        $data["customer_id"] = $customer['id'];

        Message::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Message Added'
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => ['required', Rule::in(['pending', 'success', 'failed'])]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error Validation',
                'data' => $validator->errors()
            ], 422);
        }

        $message = Message::find($id);
        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => 'Message Not Found'
            ], 404);
        }

        $message->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Message Updated'
        ], 200);
    }
}
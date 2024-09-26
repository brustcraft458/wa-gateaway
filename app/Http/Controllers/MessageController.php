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
            'phone' => 'nullable|numeric',
            'phone_list' => 'nullable|array',
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
        
        // Data
        $data['status'] = 'pending';
        $data['customer_id'] = $customer['id'];

        if (isset($data['phone'])) {
            // Check
            $data['hash'] = hash('sha256', $data['phone'] . $data['text']);
            $exists = Message::where('hash', $data['hash'])->exists();
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Double Message'
                ], 409);
            }

            // New data
            $nData = [
                'phone' => $data['phone'],
                'status' => $data['status'],
                'hash' => $data['hash'],
                'text' => $data['text'],
                'customer_id' => $data['customer_id']
            ];
            
            $message = Message::create($nData);

        } else if(isset($data['phone_list'])) {
            // Phone List Array
            foreach ($data['phone_list'] as $phone) {
                // Check
                $data['hash'] = hash('sha256', $phone . $data['text']);
                $exists = Message::where('hash', $data['hash'])->exists();
                if ($exists) {
                    continue;
                }

                // New data
                $nData = [
                    'phone' => $phone,
                    'status' => $data['status'],
                    'hash' => $data['hash'],
                    'text' => $data['text'],
                    'customer_id' => $data['customer_id']
                ];
                
                $message = Message::create($nData);
            }

        }

        // Duplicate, data not added
        if (!isset($message)) {
            return response()->json([
                'success' => false,
                'message' => 'Multi Double Message',
            ], 409);
        }

        return response()->json([
            'success' => true,
            'message' => 'Message Added'
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => ['required', Rule::in(['pending', 'sending', 'success', 'failed'])]
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

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

    private function addMessage($param, $data) {
        // Check
        $data['hash'] = hash('sha256', $data['phone'] . $data['text']);
        $exists = Message::where('hash', $data['hash'])->exists();
        if ($exists) {
            return null;
        }

        // New data
        $nData = [
            'phone' => $data['phone'],
            'status' => $param['status'],
            'hash' => $data['hash'],
            'text' => $data['text'],
            'customer_id' => $param['customer_id']
        ];
        
        return Message::create($nData);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // If 'data_list' is not provided, 'phone' and 'text' must be valid.
            'phone' => 'nullable|numeric|required_without:data_list',
            'text' => 'nullable|string|required_without:data_list',
        
            // If 'phone' and 'text' are not provided, 'data_list' must be an array with valid phone and text for each entry.
            'data_list' => 'nullable|array|required_without_all:phone,text',
            'data_list.*.phone' => 'nullable|numeric|required_with:data_list',
            'data_list.*.text' => 'nullable|string|required_with:data_list',
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

        $param = [
            'status' => 'pending',
            'customer_id' => $customer['id']
        ];

        if (isset($data['phone']) && isset($data['text'])) {
            // Single Message
            $message = $this->addMessage($param, $data);

        } else if(isset($data['data_list'])) {
            // Data List Array
            foreach ($data['data_list'] as $ldata) {
                $lmessage = $this->addMessage($param, $ldata);
                if ($lmessage) {
                    $message = $lmessage;
                }
            }

        }

        // Duplicate, data not added
        if (!isset($message)) {
            return response()->json([
                'success' => false,
                'message' => 'Double Message',
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

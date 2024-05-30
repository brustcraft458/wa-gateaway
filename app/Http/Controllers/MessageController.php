<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    public function create(Request $request) {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'text' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error Validation',
                'data' => $validator->errors()
            ], 422);
        }

        $payload = $validator->validated();
        var_dump($payload);
        // Message::create($payload);

        return response()->json([
            'success' => true,
            'message' => 'Message Added'
        ], 200);
    }

    public function update(Request $request, $id) {

    }
}

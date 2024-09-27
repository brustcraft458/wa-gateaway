<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Message; // Import your Message model
use Illuminate\Support\Facades\Hash; // Import the Hash facade

class GenerateMessageHash extends Command
{
    protected $signature = 'messages:generate-hash'; // Command name
    protected $description = 'Generate hash for the hash attribute in messages table';

    public function handle()
    {
        // Fetch all messages
        $messages = Message::all();

        // Loop through each message and generate the hash
        foreach ($messages as $message) {
            // Generate hash from the content (or whatever column you need)
            $message->hash = hash('sha256', $message['phone'] . $message['text']);
            $message->save();
        }

        $this->info('Hash generation completed for all messages.');
    }
}

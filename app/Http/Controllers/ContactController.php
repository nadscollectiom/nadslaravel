<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Message;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function index()
{
    $contacts = Contact::latest()->get();
    return view('admin.contact', compact('contacts'));
}

public function messageIndex()
{
    $messages = Message::latest()->paginate(10);
    return view('admin.message', compact('messages'));
}

public function submit(Request $request)
{
    // Log the incoming request for debugging
    Log::info('Contact form submission:', $request->all());
   
    try {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'contact' => 'required|string|max:20', // Added contact validation
            'address' => 'nullable|string|max:1000',
            'message' => 'required|string',
            'orders' => 'required|array',
            'orders.*.id' => 'required|integer',
            'orders.*.title' => 'required|string',
            'orders.*.price' => 'required|numeric',
            'orders.*.category_id' => 'required|integer',
            'orders.*.stock' => 'required|integer',
            'orders.*.image' => 'nullable|string',
            'orders.*.category' => 'nullable|array',
            'orders.*.selectedSize' => 'nullable|string',
            'orders.*.cartId' => 'nullable|string',
        ]);

        // Clean and normalize the orders data
        $orders = collect($validated['orders'])->map(function ($order) {
            return [
                'id' => (int) $order['id'],
                'title' => trim($order['title']),
                'category_id' => (int) $order['category_id'],
                'price' => (float) $order['price'],
                'stock' => (int) $order['stock'],
                'image' => !empty($order['image']) ? $order['image'] : null,
                'category' => $order['category'] ?? null,
                'selectedSize' => $order['selectedSize'] ?? 'One Size',
                'cartId' => $order['cartId'] ?? null,
            ];
        })->toArray();

        Log::info('Processed orders data:', $orders);

        $contact = Contact::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'contact' => $validated['contact'], // Added contact field
            'address' => $validated['address'] ?? null,
            'message' => $validated['message'],
            'orders' => json_encode($orders, JSON_UNESCAPED_SLASHES),
        ]);

        Log::info('Contact created successfully:', [
            'id' => $contact->id,
            'orders_stored' => $contact->orders
        ]);

        $this->sendContactEmail($contact, $orders);

        return response()->json([
            'success' => true,
            'message' => 'Contact form submitted and stored successfully!',
            'data' => [
                'id' => $contact->id,
                'name' => $contact->name,
                'email' => $contact->email,
                'contact' => $contact->contact, // Include contact in response
                'address' => $contact->address,
                'orders_count' => count($orders)
            ]
        ], 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::error('Validation error:', $e->errors());
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);

    } catch (\Exception $e) {
        Log::error('Error creating contact:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
       
        return response()->json([
            'success' => false,
            'message' => 'Failed to store contact data',
            'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
        ], 500);
    }
}


private function sendContactEmail($contact, $orders)
{
    try {
        $ordersList = collect($orders)->map(function ($order) {
            $size = $order['selectedSize'] ?? 'One Size';
            return "- {$order['title']} (${$order['price']}) - Size: {$size} - Stock: {$order['stock']}";
        })->implode("\n");

        $totalValue = collect($orders)->sum('price');
        $uniqueSizes = collect($orders)->pluck('selectedSize')->unique()->filter()->implode(', ');

        $emailBody = "New Contact Message from {$contact->name}\n\n" .
                    "Email: {$contact->email}\n" .
                    "Order ID: {$contact->order_id}\n" .
                    "Message: {$contact->message}\n\n" .
                    "Orders Summary:\n" .
                    "- Total Items: " . count($orders) . "\n" .
                    "- Total Value: $" . number_format($totalValue, 2) . "\n" .
                    "- Sizes Ordered: " . ($uniqueSizes ?: 'One Size') . "\n\n" .
                    "Detailed Orders:\n" .
                    $ordersList;

        Mail::raw($emailBody, function ($mail) use ($contact) {
            $mail->to('murtzazabair@gmail.com')
                 ->subject("New Contact Message from {$contact->name}");
        });

        Log::info('Email sent successfully for contact ID: ' . $contact->id);

    } catch (\Exception $e) {
        Log::error('Failed to send email:', ['error' => $e->getMessage()]);
        // Don't throw exception here, as we don't want to fail the entire request
    }
}
    public function message(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $message = Message::create([
                'name' => $request->name,
                'email' => $request->email,
                'subject' => $request->subject,
                'message' => $request->message,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Your message has been saved.',
                'data' => $message,
            ]);

        } catch (\Exception $e) {
            Log::error('Error saving message:', ['error' => $e->getMessage()]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save message',
            ], 500);
        }
    }
}
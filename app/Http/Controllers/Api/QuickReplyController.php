<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuickReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuickReplyController extends Controller
{
      // GET /api/quick-replies
    public function index()
    {
        $replies = QuickReply::where('user_id', Auth::id())->get();
        return response()->json(['status' => 'success', 'data' => $replies]);
    }

    // POST /api/quick-replies
    public function store(Request $request)
    {
        $request->validate([
            'text' => 'required|string|max:1000',
        ]);

        $reply = QuickReply::create([
            'user_id' => Auth::id(),
            'text' => $request->text,
        ]);

        return response()->json(['status' => 'success', 'data' => $reply]);
    }

    // PUT /api/quick-replies/{id}
    public function update(Request $request, $id)
    {
        $reply = QuickReply::where('user_id', Auth::id())->findOrFail($id);

        $request->validate([
            'text' => 'required|string|max:1000',
        ]);

        $reply->update(['text' => $request->text]);

        return response()->json(['status' => 'success', 'data' => $reply]);
    }

    // DELETE /api/quick-replies/{id}
    public function destroy($id)
    {
        $reply = QuickReply::where('user_id', Auth::id())->findOrFail($id);
        $reply->delete();

        return response()->json(['status' => 'success', 'message' => 'Quick reply deleted']);
    }
}

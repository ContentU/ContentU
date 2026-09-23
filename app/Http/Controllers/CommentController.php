<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Content;
use App\Notifications\NewCommentPosted;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class CommentController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, Content $content): RedirectResponse
    {
        $this->authorize('comment', $content);

        $data = $request->validate(['body' => ['required', 'string', 'max:5000']]);

        $comment = $content->recordComment($request->user(), $data['body']);

        $this->notifyOthers($content, $comment);

        return back();
    }

    /** Avvisa chi è coinvolto sul contenuto, tranne l'autore del commento. */
    private function notifyOthers(Content $content, Comment $comment): void
    {
        $client = $content->quarter->client;

        $recipients = $comment->author_role === 'client'
            ? $client->teamMembers                                       // cliente scrive → avvisa il team
            : $client->users()->where('role', 'client')->get();          // team scrive → avvisa il cliente

        $recipients = $recipients->reject(fn ($u) => $u->id === $comment->author_id);

        Notification::send($recipients, new NewCommentPosted($content, $comment));
    }
}

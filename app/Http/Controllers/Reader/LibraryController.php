<?php

namespace App\Http\Controllers\Reader;

use App\Http\Controllers\Controller;
use App\Models\Ebook;
use App\Models\ReadingHistory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LibraryController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $activeTab = $request->query('tab', 'reading');

        $readingHistories = ReadingHistory::with(['ebook.category', 'ebook.author'])
            ->where('user_id', $user->id)
            ->whereHas('ebook', function ($query) {
                $query->where('status', 'published');
            })
            ->latest('updated_at')
            ->get();

        $favoriteEbooks = $user->bookmarks()
            ->with(['category', 'author'])
            ->where('status', 'published')
            ->latest('bookmarks.created_at')
            ->get();

        $unlockedEbooks = $user->unlockedEbooks()
            ->with(['category', 'author'])
            ->latest('unlocked_ebooks.created_at')
            ->get();

        $uploadedEbooks = Ebook::with(['category', 'author'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $myBooks = $unlockedEbooks->merge($uploadedEbooks)->unique('id')->values();

        return view('reader.library', compact(
            'activeTab',
            'readingHistories',
            'favoriteEbooks',
            'myBooks'
        ));
    }
}

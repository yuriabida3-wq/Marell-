<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsAdminController extends Controller
{
    public function index()
    {
        $news = News::latest()->paginate(15);
        return view('principal.news.index', compact('news'));
    }

    public function create()
    {
        return view('principal.news.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'   => 'required|string|max:180',
            'excerpt' => 'nullable|string|max:300',
            'body'    => 'required|string',
            'image'   => 'nullable|url|max:500',
            'published' => 'nullable|boolean',
        ]);

        $data['slug']       = Str::slug($data['title']) . '-' . uniqid();
        $data['author_id']  = auth()->id();
        $data['published']  = $request->boolean('published', true);

        $post = News::create($data);

        return redirect()->route('principal.news.index')->with('success', "News '{$post->title}' posted.");
    }

    public function edit(News $news)
    {
        return view('principal.news.edit', compact('news'));
    }

    public function update(Request $request, News $news)
    {
        $data = $request->validate([
            'title'   => 'required|string|max:180',
            'excerpt' => 'nullable|string|max:300',
            'body'    => 'required|string',
            'image'   => 'nullable|url|max:500',
            'published' => 'nullable|boolean',
        ]);
        $data['published'] = $request->boolean('published');
        $news->update($data);
        return redirect()->route('principal.news.index')->with('success', 'News updated.');
    }

    public function destroy(News $news)
    {
        $news->delete();
        return back()->with('success', 'News deleted.');
    }
}

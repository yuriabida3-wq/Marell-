<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookLoan;
use App\Models\LibrarySetting;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LibraryController extends Controller
{
    // ============== DASHBOARD ==============
    public function index()
    {
        $totalBooks     = Book::where('active', true)->sum('total_copies');
        $availableBooks = Book::where('active', true)->sum('available_copies');
        $onLoan         = BookLoan::whereNull('returned_at')->count();
        $overdue        = BookLoan::whereNull('returned_at')->where('due_at', '<', now())->count();
        $unpaidFines    = BookLoan::where('fine', '>', 0)->where('fine_paid', false)->sum('fine');

        $recentLoans = BookLoan::with(['book', 'student'])->latest()->take(10)->get();

        return view('library.index', compact(
            'totalBooks', 'availableBooks', 'onLoan', 'overdue', 'unpaidFines', 'recentLoans'
        ));
    }

    // ============== BOOKS ==============
    public function books(Request $request)
    {
        $q        = trim($request->get('q', ''));
        $category = $request->get('category');

        $query = Book::query();
        if ($q) {
            $query->where(function ($s) use ($q) {
                $s->where('title', 'like', "%{$q}%")
                  ->orWhere('author', 'like', "%{$q}%")
                  ->orWhere('isbn', 'like', "%{$q}%");
            });
        }
        if ($category) $query->where('category', $category);

        $books      = $query->orderBy('title')->paginate(30)->withQueryString();
        $categories = Book::whereNotNull('category')->distinct()->pluck('category');

        return view('library.books', compact('books', 'categories', 'q', 'category'));
    }

    public function createBook()
    {
        return view('library.book-form');
    }

    public function storeBook(Request $request)
    {
        $data = $request->validate([
            'title'         => 'required|string|max:200',
            'author'        => 'nullable|string|max:120',
            'isbn'          => 'nullable|string|max:30',
            'category'      => 'nullable|string|max:60',
            'publisher'     => 'nullable|string|max:120',
            'year'          => 'nullable|integer|min:1900|max:2100',
            'total_copies'  => 'required|integer|min:1|max:999',
            'shelf'         => 'nullable|string|max:30',
        ]);

        $data['available_copies'] = $data['total_copies'];
        $data['active']           = true;

        Book::create($data);

        return redirect()->route('library.books')->with('success', 'Book added to catalog.');
    }

    public function editBook(Book $book)
    {
        return view('library.book-form', compact('book'));
    }

    public function updateBook(Request $request, Book $book)
    {
        $data = $request->validate([
            'title'         => 'required|string|max:200',
            'author'        => 'nullable|string|max:120',
            'isbn'          => 'nullable|string|max:30',
            'category'      => 'nullable|string|max:60',
            'publisher'     => 'nullable|string|max:120',
            'year'          => 'nullable|integer|min:1900|max:2100',
            'total_copies'  => 'required|integer|min:1|max:999',
            'shelf'         => 'nullable|string|max:30',
            'active'        => 'boolean',
        ]);

        $currentlyOut = $book->total_copies - $book->available_copies;
        $data['available_copies'] = max(0, $data['total_copies'] - $currentlyOut);

        $book->update($data);

        return redirect()->route('library.books')->with('success', 'Book updated.');
    }

    // ============== LOANS ==============
    public function loans(Request $request)
    {
        $filter = $request->get('filter', 'active');

        $query = BookLoan::with(['book', 'student'])->latest('issued_at');

        if ($filter === 'active')   $query->whereNull('returned_at');
        if ($filter === 'overdue')  $query->whereNull('returned_at')->where('due_at', '<', now());
        if ($filter === 'returned') $query->whereNotNull('returned_at');

        $loans = $query->paginate(30)->withQueryString();

        return view('library.loans', compact('loans', 'filter'));
    }

    public function issueForm(Request $request)
    {
        $settings = LibrarySetting::first();
        $adm      = $request->get('adm');

        $student = $adm ? Student::where('adm_no', trim($adm))->first() : null;

        $availableBooks = Book::where('active', true)->where('available_copies', '>', 0)->orderBy('title')->get();

        return view('library.issue', compact('settings', 'student', 'availableBooks', 'adm'));
    }

    public function issue(Request $request)
    {
        $data = $request->validate([
            'adm'     => 'required|string',
            'book_id' => 'required|exists:books,id',
        ]);

        $student = Student::where('adm_no', trim($data['adm']))->first();
        if (!$student) return back()->with('error', 'Student not found.')->withInput();

        $book = Book::findOrFail($data['book_id']);
        if ($book->available_copies <= 0) {
            return back()->with('error', "No copies of '{$book->title}' available.")->withInput();
        }

        $settings = LibrarySetting::first();

        // Check max books per student
        $activeLoans = BookLoan::where('student_id', $student->id)->whereNull('returned_at')->count();
        if ($activeLoans >= $settings->max_books_per_student) {
            return back()->with('error', "{$student->name} already has {$activeLoans} books. Max: {$settings->max_books_per_student}.")->withInput();
        }

        DB::transaction(function () use ($student, $book, $settings) {
            BookLoan::create([
                'book_id'    => $book->id,
                'student_id' => $student->id,
                'issued_at'  => now()->toDateString(),
                'due_at'     => now()->addDays($settings->loan_days)->toDateString(),
                'issued_by'  => auth()->id(),
            ]);

            $book->decrement('available_copies');
        });

        return redirect()->route('library.issue')
            ->with('success', "Issued '{$book->title}' to {$student->name}. Due: " . now()->addDays($settings->loan_days)->format('d M Y'));
    }

    public function returnForm(Request $request)
    {
        $adm  = $request->get('adm');
        $student = $adm ? Student::where('adm_no', trim($adm))->first() : null;

        $activeLoans = $student
            ? BookLoan::with('book')->where('student_id', $student->id)->whereNull('returned_at')->get()
            : collect();

        return view('library.return', compact('student', 'activeLoans', 'adm'));
    }

    public function returnBook(Request $request, BookLoan $loan)
    {
        if ($loan->returned_at) {
            return back()->with('error', 'This book is already returned.');
        }

        DB::transaction(function () use ($loan) {
            $fine = $loan->calculateFine();

            $loan->update([
                'returned_at' => now()->toDateString(),
                'fine'        => $fine,
            ]);

            $loan->book->increment('available_copies');
        });

        $msg = 'Book returned.';
        if ($loan->fresh()->fine > 0) {
            $msg .= ' Fine: KES ' . number_format($loan->fresh()->fine, 2);
        }

        return back()->with('success', $msg);
    }

    public function markFinePaid(BookLoan $loan)
    {
        $loan->update(['fine_paid' => true]);
        return back()->with('success', 'Fine marked as paid.');
    }

    public function publicCatalog(Request $request)
    {
        $q = trim($request->get("q", ""));
        $category = $request->get("category");

        $query = Book::where("active", true);
        if ($q) {
            $query->where(function ($s) use ($q) {
                $s->where("title", "like", "%{$q}%")
                  ->orWhere("author", "like", "%{$q}%");
            });
        }
        if ($category) $query->where("category", $category);

        $books = $query->orderBy("title")->paginate(24)->withQueryString();
        $categories = Book::whereNotNull("category")->where("active", true)->distinct()->pluck("category");
        $totalBooks = Book::where("active", true)->sum("total_copies");

        return view("public.library-catalog", compact("books", "categories", "q", "category", "totalBooks"));
    }
}

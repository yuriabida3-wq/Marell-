<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Result;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\Timetable;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function home() {
        $news = News::where('published', true)->latest()->take(3)->get();
        return view('public.home', compact('news'));
    }
    public function about() { return view('public.about'); }
    public function academics() { return view('public.academics'); }
    public function fees() { return view('public.fees'); }
    public function admissions() { return view('public.admissions'); }
    public function contact() { return view('public.contact'); }

    public function news() {
        $news = News::where('published', true)->latest()->paginate(9);
        return view('public.news-index', compact('news'));
    }

    public function newsShow($slug) {
        $post = News::where('slug', $slug)->where('published', true)->firstOrFail();
        $related = News::where('id', '!=', $post->id)->where('published', true)->latest()->take(3)->get();
        return view('public.news-show', compact('post', 'related'));
    }

    public function timetable(Request $request) {
        $classes = Classroom::where('active', true)->orderBy('name')->get();
        $class = null;
        $grid = [];
        if ($request->filled('class_id')) {
            $class = Classroom::find($request->class_id);
            if ($class) {
                $rows = Timetable::with('teacher')->where('class', $class->name)->where('stream', $class->stream)->get();
                foreach ($rows as $r) $grid[$r->day][$r->period] = $r;
            }
        }
        return view('public.timetable', compact('classes', 'class', 'grid'));
    }

    public function results(Request $request) {
        $student = null; $results = collect(); $averageGrade = null;
        if ($request->filled('adm')) {
            $student = Student::where('adm_no', $request->adm)->first();
            if (!$student) return redirect()->route('results')->with('error',"No student found with admission number {$request->adm}.");
            if ($student->balance <= 5000) {
                $results = Result::where('student_id', $student->id)->orderBy('subject')->get();
                if ($results->count()) $averageGrade = $this->gradeFromMarks((int) round($results->avg('marks')));
            }
        }
        return view('public.results', compact('student','results','averageGrade'));
    }

    protected function gradeFromMarks(int $m): string {
        return match(true) {
            $m>=80=>'A',$m>=75=>'A-',$m>=70=>'B+',$m>=65=>'B',$m>=60=>'B-',
            $m>=55=>'C+',$m>=50=>'C',$m>=45=>'C-',$m>=40=>'D+',$m>=35=>'D',default=>'E',
        };
    }
}

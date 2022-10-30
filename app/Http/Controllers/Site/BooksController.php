<?php namespace MotionArray\Http\Controllers\Site;

use MotionArray\Repositories\BookRepository;
use Redirect;
use View;

class BooksController extends BaseController
{
    protected $book;

    public function __construct(BookRepository $book)
    {
        $this->book = $book;
    }

    public function show($slug)
    {
        $book = $this->book->findBySlug($slug);

        if (!$book) {
            return Redirect::to('/');
        }

        return View::make('site.books.show', compact('book'));
    }

    public function download($id)
    {
        $book = $this->book->findById($id);

        if ($book) {
            $downloadUrl = $this->book->getDownloadUrl($book);

            return Redirect::to($downloadUrl);
        }

        return Redirect::back();
    }
}

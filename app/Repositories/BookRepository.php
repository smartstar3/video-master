<?php

namespace MotionArray\Repositories;

use MotionArray\Models\Book;
use MotionArray\Repositories\EloquentBaseRepository;
use Config;
use AWS;

class BookRepository extends EloquentBaseRepository
{
    public function __construct()
    {
        $this->model = new Book();
    }

    public function getDownloadUrl(Book $book)
    {
        $bucket = Config::get("aws.files_bucket");

        $s3 = AWS::get('s3');

        $urlParts = parse_url($book->package_url);

        $filename = $urlParts['path'];

        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        $filenameAlias = ucfirst($book->slug) . '.' . $ext;

        $args = ['ResponseContentDisposition' => 'attachment; filename="' . $filenameAlias . '"'];

        $url = $s3->getObjectUrl($bucket, $filename, '+5 minutes', $args);

        return $url;
    }
}

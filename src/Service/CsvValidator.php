<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CsvValidator
{
    public function __construct(private readonly ValidatorInterface $validator)
    {
    }

    public function validate(UploadedFile $file): bool
    {
        $violations = $this->validator->validate($file, new File([
            'maxSize' => '1000K',
            'mimeTypes' => ['text/csv', 'text/plain'],
        ]));

        return count($violations) === 0;
    }
}
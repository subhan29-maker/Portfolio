<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\UploadedFile;

abstract class TestCase extends BaseTestCase
{
    /**
     * A real PNG upload for image validation.
     *
     * A genuine file is copied rather than generated so the tests do not need
     * the GD extension, which is not installed in every environment.
     */
    protected function fakeImage(string $name = 'cover.png'): UploadedFile
    {
        $source = public_path('images/projects/thinkcode-crm.png');
        $path = tempnam(sys_get_temp_dir(), 'portfolio-png-');

        copy($source, $path);

        return new UploadedFile($path, $name, 'image/png', null, true);
    }

    /**
     * A genuine image that is far too large to be accepted.
     *
     * The file keeps a real image header, so it passes the "is this an
     * image?" check and is rejected purely on its size.
     */
    protected function fakeLargeImage(string $name = 'huge.png', int $kilobytes = 6000): UploadedFile
    {
        $file = $this->fakeImage($name);

        $handle = fopen($file->getPathname(), 'ab');
        fwrite($handle, str_repeat("\0", $kilobytes * 1024));
        fclose($handle);

        return $file;
    }
}

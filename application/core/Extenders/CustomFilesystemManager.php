<?php

namespace application\core\Extenders;

use League\Flysystem\FilesystemInterface;


class CustomFilesystemManager extends \Illuminate\Filesystem\FilesystemManager
{
    /**
     * Adapt the filesystem implementation.
     *
     * @param  \League\Flysystem\FilesystemInterface  $filesystem
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected function adapt(FilesystemInterface $filesystem)
    {
        return new CustomFilesystemAdapter($filesystem);
    }
}

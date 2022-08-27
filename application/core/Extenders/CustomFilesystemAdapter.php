<?php

namespace application\core\Extenders;

class CustomFilesystemAdapter extends \Illuminate\Filesystem\FilesystemAdapter
{
    /**
     * Get the URL for the file at the given path.
     *
     * @param  \League\Flysystem\AwsS3v3\AwsS3Adapter  $adapter
     * @param  string  $path
     * @return string
     */
    protected function getAwsUrl($adapter, $path)
    {
        // If an explicit base URL has been set on the disk configuration then we will use
        // it as the base URL instead of the default path. This allows the developer to
        // have full control over the base path for this filesystem's generated URLs.
        if (! is_null($url = $this->driver->getConfig()->get('url'))) {
            return $this->concatPathToUrl($url, $path);
        }

        return $adapter->getClient()->getObjectUrl(
            $adapter->getBucket(), $path
        );
    }
}

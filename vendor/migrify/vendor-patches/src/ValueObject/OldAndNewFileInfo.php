<?php

declare(strict_types=1);

namespace Migrify\VendorPatches\ValueObject;

use Symplify\SmartFileSystem\SmartFileInfo;

final class OldAndNewFileInfo
{
    /**
     * @var SmartFileInfo
     */
    private $oldFileInfo;

    /**
     * @var SmartFileInfo
     */
    private $newFileInfo;

    /**
     * @var string
     */
    private $packageName;

    public function __construct(SmartFileInfo $oldFileInfo, SmartFileInfo $newFileInfo, string $packageName)
    {
        $this->oldFileInfo = $oldFileInfo;
        $this->newFileInfo = $newFileInfo;
        $this->packageName = $packageName;
    }

    public function getOldFileInfo(): SmartFileInfo
    {
        return $this->oldFileInfo;
    }

    public function getOldFileRelativePath(): string
    {
        return $this->oldFileInfo->getRelativeFilePathFromCwd();
    }

    public function getNewFileRelativePath(): string
    {
        return $this->newFileInfo->getRelativeFilePathFromCwd();
    }

    public function getNewFileInfo(): SmartFileInfo
    {
        return $this->newFileInfo;
    }

    public function isContentIdentical(): bool
    {
        return $this->newFileInfo->getContents() === $this->oldFileInfo->getContents();
    }

    public function getPackageName(): string
    {
        return $this->packageName;
    }
}

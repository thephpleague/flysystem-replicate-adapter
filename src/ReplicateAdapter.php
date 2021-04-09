<?php

namespace League\Flysystem\Replicate;

use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\FilesystemException;

class ReplicateAdapter implements FilesystemAdapter
{
    /**
     * @var FilesystemAdapter
     */
    protected $replica;

    /**
     * @var FilesystemAdapter
     */
    protected $source;

    /**
     * Constructor.
     *
     * @param FilesystemAdapter $source
     * @param FilesystemAdapter $replica
     */
    public function __construct(FilesystemAdapter $source, FilesystemAdapter $replica)
    {
        $this->source = $source;
        $this->replica = $replica;
    }

    /**
     * Returns the replica adapter.
     *
     * @return FilesystemAdapter
     */
    public function getReplicaAdapter(): FilesystemAdapter
    {
        return $this->replica;
    }

    /**
     * Returns the source adapter.
     *
     * @return FilesystemAdapter
     */
    public function getSourceAdapter(): FilesystemAdapter
    {
        return $this->source;
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $path, string $contents, Config $config): void
    {
        $this->source->write($path, $contents, $config);
        $this->replica->write($path, $contents, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function writeStream(string $path, $contents, Config $config): void
    {
        $this->source->writeStream($path, $contents, $config);
        $contents = $this->ensureSeekable($contents, $path);
        $this->replica->writeStream($path, $contents, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function move(string $source, string $destination, Config $config): void
    {
        $this->source->move($source, $destination, $config);
        $this->replica->move($source, $destination, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function copy(string $source, string $destination, Config $config): void
    {
        $this->source->copy($source, $destination, $config);
        $this->replica->copy($source, $destination, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $path): void
    {
        $this->source->delete($path);

        if ($this->replica->fileExists($path)) {
            $this->replica->delete($path);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDirectory(string $path): void
    {
        $this->source->deleteDirectory($path);
        $this->replica->deleteDirectory($path);
    }

    /**
     * {@inheritdoc}
     */
    public function createDirectory(string $path, Config $config): void
    {
        $this->source->createDirectory($path, $config);
        $this->replica->createDirectory($path, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function fileExists(string $path): bool
    {
        return $this->source->fileExists($path);
    }

    /**
     * {@inheritdoc}
     */
    public function read(string $path): string
    {
        return $this->source->read($path);
    }

    /**
     * {@inheritdoc}
     */
    public function readStream(string $path)
    {
        return $this->source->readStream($path);
    }

    /**
     * {@inheritdoc}
     */
    public function listContents(string $path, bool $deep): iterable
    {
        return $this->source->listContents($path, $deep);
    }

    /**
     * {@inheritdoc}
     */
    public function fileSize(string $path): FileAttributes
    {
        return $this->source->fileSize($path);
    }

    /**
     * {@inheritdoc}
     */
    public function mimeType(string $path): FileAttributes
    {
        return $this->source->mimeType($path);
    }

    /**
     * {@inheritdoc}
     */
    public function lastModified(string $path): FileAttributes
    {
        return $this->source->lastModified($path);
    }

    /**
     * {@inheritdoc}
     */
    public function visibility(string $path): FileAttributes
    {
        return $this->source->visibility($path);
    }

    /**
     * {@inheritdoc}
     */
    public function setVisibility(string $path, string $visibility): void
    {
        $this->source->setVisibility($path, $visibility);
        $this->replica->setVisibility($path, $visibility);
    }

    /**
     * Rewinds the stream, or returns the source stream if not seekable.
     *
     * @param resource $resource The resource to rewind.
     * @param string   $path     The path where the resource exists.
     *
     * @return resource A stream set to position zero.
     * @throws FilesystemException
     */
    protected function ensureSeekable($resource, string $path)
    {
        if (stream_get_meta_data($resource)['seekable'] && rewind($resource)) {
            return $resource;
        }

        return $this->source->readStream($path);
    }
}

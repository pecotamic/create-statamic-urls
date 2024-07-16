<?php

declare(strict_types=1);

namespace Pecotamic\CreateURLs\Task;

use Pecotamic\CreateURLs\Helper\StringHelper;
use Pecotamic\CreateURLs\ImportMapper;
use Pecotamic\CreateURLs\Models\Data\Data;
use Pecotamic\CreateURLs\Models\Data\EmbeddedData;
use Pecotamic\CreateURLs\Models\Media;
use Pecotamic\CreateURLs\Models\PageModular as Page;
use Pecotamic\CreateURLs\Models\Rating;
use Pecotamic\CreateURLs\Models\Url;
use Statamic\Entries\Collection as EntriesCollection;
use Statamic\Entries\Entry as StatamicEntry;
use Statamic\Facades\Collection as CollectionFacade;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Structures\CollectionTree;

class Pages
{
    /**
     * @param  string[]  $urls
     */
    public function createAll(array $urls): void
    {
        collect($urls)->sort()
            ->each(fn($url) => $this->create($url));
    }

    public function reset()
    {
        $collection = $this->getCollection('pages');
        $this->getCollectionTree($collection)->delete();
        $collection->truncate();
    }

    public function create(string $url)
    {
        $path = '/'.trim(preg_replace(['#^(https?://[^/]+)?#', '#\.html$#'], '', trim($url)), '/');

        $parentEntry = $this->createParentEntries($path);

        return $this->findOrCreateEntry($path, ['blueprint' => 'page', 'redirect' => ''], $parentEntry);
    }

    private function createParentEntries(string $entryPath): ?\Statamic\Contracts\Entries\Entry
    {
        $collection = $this->getCollection('pages');
        $tree = $this->getCollectionTree($collection);

        if ($tree->structure()->expectsRoot() && !$tree->root()) {
            $this->createEntry('', ['blueprint' => 'page']);
        }

        $pathComponents = explode('/', trim($entryPath, '/'));
        array_pop($pathComponents);

        $parentEntry = null;
        $path = '';

        while ($pathComponents) {
            $path .= '/'.array_shift($pathComponents);
            $parentEntry = $this->findOrCreateEntry(
                $path,
                ['blueprint' => 'link', 'redirect' => '@child'],
                $parentEntry
            );
        }

        return $parentEntry;
    }

    private function createEntry(
        string $path,
        array $data,
        ?\Statamic\Contracts\Entries\Entry $parentEntry = null
    ): StatamicEntry {
        $collection = $this->getCollection('pages');

        /** @var \Statamic\Entries\Entry $entry */
        ($entry = Entry::make()
            ->collection($collection))
            ->slug(basename($path) ?: 'home')
            ->data([
                'title' => basename($path) ? StringHelper::toTitleCase(basename($path)) : 'Startseite',
                ...$data,
            ])
            ->save();

        $this->getCollectionTree($collection)
            ->appendTo($parentEntry?->id(), $entry)
            ->save();

        return $entry;
    }

    private function findOrCreateEntry(
        string $path,
        array $data = [],
        ?\Statamic\Contracts\Entries\Entry $parentEntry = null,
    ): ?\Statamic\Contracts\Entries\Entry {
        $collection = $this->getCollection('pages');

        $path = '/'.ltrim($path, '/');

        if ($entry = Entry::findByUri($path, Site::current())?->entry()) {
            if ($data) {
                $entry
                    ->data([...$entry->data ?? [], ...$data])
                    ->save();
            }
            return $entry;
        }

        return $this->createEntry($path, $data, $parentEntry);
    }

    private function getCollection(string $handle): EntriesCollection
    {
        return CollectionFacade::findByHandle($handle);
    }

    private function getCollectionTree(EntriesCollection $collection): CollectionTree
    {
        return $collection->structure()->in(Site::current());
    }

    private function findEntryInCollection(EntriesCollection $collection, string $column, string $value): ?StatamicEntry
    {
        return $collection
            ->queryEntries()
            ->where($column, $value)
            ->first();
    }
}

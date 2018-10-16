<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery;

use Contentful\Core\Api\Link;
use Contentful\Core\Exception\NotFoundException;
use Contentful\Core\Resource\ResourceArray;
use Contentful\Core\Resource\ResourceInterface;
use Contentful\Delivery\Resource\Asset;
use Contentful\Delivery\Resource\ContentType;
use Contentful\Delivery\Resource\Entry;
use Contentful\Delivery\Resource\Environment;
use Contentful\Delivery\Resource\Space;

interface ClientInterface
{
    /**
     * Returns a single Asset object corresponding to the given ID.
     *
     * @param string      $assetId
     * @param string|null $locale
     *
     * @throws NotFoundException If no asset is found with the given ID
     *
     * @return Asset
     */
    public function getAsset(string $assetId, string $locale = \null): Asset;

    /**
     * Returns a collection of Asset objects.
     *
     * @param Query|null $query
     *
     * @return ResourceArray
     */
    public function getAssets(Query $query = \null): ResourceArray;

    /**
     * Returns a single ContentType object corresponding to the given ID.
     *
     * @param string $contentTypeId
     *
     * @throws NotFoundException If no content type is found with the given ID
     *
     * @return ContentType
     */
    public function getContentType(string $contentTypeId): ContentType;

    /**
     * Returns a collection of ContentType objects.
     *
     * @param Query|null $query
     *
     * @return ResourceArray
     */
    public function getContentTypes(Query $query = \null): ResourceArray;

    /**
     * Returns the Environment object corresponding to the one in use.
     *
     * @return Environment
     */
    public function getEnvironment(): Environment;

    /**
     * Returns a single Entry object corresponding to the given ID.
     *
     * @param string      $entryId
     * @param string|null $locale
     *
     * @throws NotFoundException If no entry is found with the given ID
     *
     * @return Entry
     */
    public function getEntry(string $entryId, string $locale = \null): Entry;

    /**
     * Returns a collection of Entry objects.
     *
     * @param Query|null $query
     *
     * @return ResourceArray
     */
    public function getEntries(Query $query = \null): ResourceArray;

    /**
     * Returns the Space object corresponding to the one in use.
     *
     * @return Space
     */
    public function getSpace(): Space;

    /**
     * Resolve a link to its actual resource.
     *
     * @param Link   $link
     * @param string $locale
     *
     * @throws \InvalidArgumentException when encountering an unexpected link type
     *
     * @return ResourceInterface
     */
    public function resolveLink(Link $link, string $locale = \null): ResourceInterface;
}
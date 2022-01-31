<?php

/*
 * A better YAML configuration file management virion for PocketMine-MP 4
 * Copyright (C) 2022 KygekTeam
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

namespace KygekTeam\BetterConfig;

require_once __DIR__ . "/libs/autoload.php";

use Exception;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class Config {

    public const VERSION = "1.0.0-BETA";

    protected bool $changed = false;

    public function __construct(
        protected string $path,
        protected array $contentsCache = [],
        protected bool $alwaysUpdate = false,
    ) {
        // Create configuration file
        $this->save();
    }

    public function getPath() : string {
        return $this->path;
    }

    public function get(string $key, mixed $default = null, bool $cached = null) : mixed {
        $this->checkCached($cached);
        return $this->contentsCache[$key] ?? $default;
    }

    public function getNested(string $key, mixed $default = null, string $separator = ".", bool $cached = null) : mixed {
        $this->checkCached($cached);
        $keys = explode($separator, $key);
        $contents = $this->contentsCache;
        foreach ($keys as $key) {
            if (!isset($contents[$key])) {
                return $default;
            }
            $contents = $contents[$key];
        }
        return $contents;
    }

    public function getContents(array $keys, mixed $default = null, bool $cached = null) : array {
        $this->checkCached($cached);
        $result = [];
        foreach ($keys as $key) {
            if (is_array($key)) {
                $result = array_merge($result, $this->getContents($key, $default, $cached));
            } else {
                $result[$key] = $this->contentsCache[$key] ?? $default;
            }
        }
        return $result;
    }

    public function getAll(bool $cached = null) : array {
        $this->checkCached($cached);
        return $this->contentsCache;
    }

    public function set(string $key, mixed $value, bool $update = null) : bool {
        $this->contentsCache[$key] = $value;
        $this->changed = true;
        return $this->checkUpdate($update);
    }

    public function setNested(string $key, mixed $value, string $separator = ".", bool $update = null) : bool {
        $keys = explode($separator, $key);
        $contents = &$this->contentsCache;
        foreach ($keys as $key) {
            if (!isset($contents[$key])) {
                $contents[$key] = [];
            }
            $contents = &$contents[$key];
        }
        $contents = $value;
        $this->changed = true;
        return $this->checkUpdate($update);
    }

    public function setContents(array $contents, bool $update = null) : bool {
        // Merge arrays
        $this->contentsCache = array_replace_recursive($this->contentsCache, $contents);
        $this->changed = true;
        return $this->checkUpdate($update);
    }

    public function setAll(array $contents, bool $update = null) : bool {
        $this->contentsCache = $contents;
        $this->changed = true;
        return $this->checkUpdate($update);
    }

    public function remove(string $key, bool $update = null) : bool {
        if (!isset($this->contentsCache[$key])) {
            return false;
        }
        unset($this->contentsCache[$key]);
        $this->changed = true;
        return $this->checkUpdate($update);
    }

    public function removeNested(string $key, string $separator = ".", bool $update = null) : bool {
        $keys = explode($separator, $key);
        $contents = &$this->contentsCache;
        $unset = true;
        foreach ($keys as $key) {
            if (!isset($contents[$key])) {
                $unset = false;
                break;
            }
            $contents = &$contents[$key];
        }
        // Prevents accidental removal of the whole array
        if ($unset) unset($contents);
        $this->changed = true;
        return $this->checkUpdate($update);
    }

    public function removeContents(array $keys, bool $update = null) : bool {
        foreach ($keys as $key) {
            unset($this->contentsCache[$key]);
        }
        $this->changed = true;
        return $this->checkUpdate($update);
    }

    public function removeAll(bool $update = false) : bool {
        return $this->setAll([], $update);
    }

    public function update() : bool {
        return $this->save() && $this->reload();
    }

    public function save() : bool {
        try {
            file_put_contents($this->path, Yaml::dump($this->contentsCache, 2, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK));
            $this->changed = false;
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function reload() : bool {
        try {
            $this->contentsCache = Yaml::parseFile($this->path, Yaml::DUMP_EXCEPTION_ON_INVALID_TYPE);
            return true;
        } catch (ParseException $e) {
            $this->contentsCache = [];
            return false;
        }
    }

    public function hasChanged() : bool {
        return $this->changed;
    }

    public function isAlwaysUpdated() : bool {
        return $this->alwaysUpdate;
    }

    public function setAlwaysUpdate(bool $alwaysUpdate) {
        $this->alwaysUpdate = $alwaysUpdate;
    }

    protected function checkCached(?bool $cached) {
        if ($cached === null) {
            $cached = !$this->isAlwaysUpdated();
        }
        if (!$cached) {
            $this->reload();
        }
    }

    protected function checkUpdate(?bool $update) : bool {
        if ($update === null) {
            $update = $this->isAlwaysUpdated();
        }
        if ($update) {
            return $this->update();
        }
        return true;
    }

}
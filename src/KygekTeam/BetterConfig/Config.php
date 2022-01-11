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
    ) {
        // Create configuration file
        $this->save();
    }

    public function getPath() : string {
        return $this->path;
    }

    public function get(string $key, bool $cached = true) : mixed {
        if (!$cached) {
            $this->reload();
        }
        return $this->contentsCache[$key] ?? null;
    }

    public function getAll(bool $cached = true) : array {
        if (!$cached) {
            $this->reload();
        }
        return $this->contentsCache;
    }

    public function set(array $contents, bool $update = false) : bool {
        // Merge arrays
        $this->contentsCache = $contents + $this->contentsCache;
        $this->changed = true;
        if ($update) {
            return $this->update();
        }
        return true;
    }

    public function setAll(array $contents, bool $update = false) : bool {
        $this->contentsCache = $contents;
        $this->changed = true;
        if ($update) {
            return $this->update();
        }
        return true;
    }

    public function remove(string $key, bool $update = false) : bool {
        if (!isset($this->contentsCache[$key])) {
            return false;
        }
        unset($this->contentsCache[$key]);
        $this->changed = true;
        if ($update) {
            return $this->update();
        }
        return true;
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

}
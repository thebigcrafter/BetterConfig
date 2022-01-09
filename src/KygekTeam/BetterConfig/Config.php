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

require_once __DIR__ . "/../../../vendor/autoload.php";

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class Config {

    public const VERSION = "1.0.0-BETA";

    private bool $changed = false;

    public function __construct(
        private string $path,
        private array $contentsCache = [],
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
        return $this->contentsCache[$key];
    }

    public function getAll(bool $cached = true) : array {
        if (!$cached) {
            $this->reload();
        }
        return $this->contentsCache;
    }

    public function set(array $contents, bool $update = false) {
        // Merge arrays
        $this->contentsCache = $contents + $this->contentsCache;
        $this->changed = true;
        if ($update) {
            $this->update();
        }
    }

    public function setAll(array $contents, bool $update = false) {
        $this->contentsCache = $contents;
        $this->changed = true;
        if ($update) {
            $this->update();
        }
    }

    public function update() {
        $this->save();
        $this->reload();
    }

    public function save() {
        file_put_contents($this->path, Yaml::dump($this->contentsCache, 2, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK));
        $this->changed = false;
    }

    public function reload() {
        try {
            $this->contentsCache = Yaml::parseFile($this->path, Yaml::DUMP_EXCEPTION_ON_INVALID_TYPE);
        } catch (ParseException $e) {
            $this->contentsCache = [];
        }

    }

    public function hasChanged() : bool {
        return $this->changed;
    }

}
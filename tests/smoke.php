<?php

declare(strict_types=1);

require_once dirname(__DIR__) . "/src/KygekTeam/BetterConfig/Config.php";

use KygekTeam\BetterConfig\Config;
use Symfony\Component\Yaml\Yaml;

function expect(bool $condition, string $message) : void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$path = tempnam(sys_get_temp_dir(), "betterconfig-");
if ($path === false) {
    throw new RuntimeException("Unable to create a temporary configuration path");
}

try {
    $config = new Config($path, [
        "name" => "initial",
        "database" => ["host" => "localhost"],
    ]);

    expect($config->get("name") === "initial", "Initial value was not stored");
    expect($config->getNested("database.host") === "localhost", "Initial nested value was not stored");

    $config->setNested("database.port", 3306, null, true);
    expect($config->hasChanged() === false, "Immediate update left the cache marked as changed");

    $persisted = Yaml::parseFile($path, Yaml::DUMP_EXCEPTION_ON_INVALID_TYPE);
    expect($persisted["database"]["port"] === 3306, "Updated nested value was not persisted");

    $reloaded = $config;

    $reloaded->setNested("service.endpoint", "https://example.test");
    $reloaded->setNestedSeparator("/");
    expect($reloaded->getNested("service/endpoint") === "https://example.test", "Custom nested separator failed");

    $reloaded->removeNested("database/host", "/", true);
    expect(!$reloaded->existsNested("database/host", "/"), "Nested value was not removed");

    $reloaded->setContents(["feature" => ["enabled" => true, "label" => "demo"]], true);
    expect($reloaded->getContents(["feature"]) === ["feature" => ["enabled" => true, "label" => "demo"]], "Selected contents were not returned");
    expect($reloaded->removeContents([["feature"]], true), "Removing selected contents did not report success");
    expect(!$reloaded->exists("feature"), "Selected contents were not removed");

    $reloaded->setAlwaysUpdated(true);
    $alwaysUpdated = $reloaded;
    file_put_contents($path, "external: value\n");
    expect($alwaysUpdated->get("external") === "value", "Always-updated cache did not reload the file");

    file_put_contents($path, "broken: [\n");
    expect(!$alwaysUpdated->reload(), "Malformed YAML unexpectedly reloaded successfully");
    expect($alwaysUpdated->isEmpty(), "Malformed YAML did not clear the cache");

    file_put_contents($path, "restored: true\n");
    expect($alwaysUpdated->reload(), "Valid YAML could not be reloaded after a parse failure");
    expect($alwaysUpdated->get("restored") === true, "Reloaded value was not available after recovery");

    echo "PASS: BetterConfig persistence, nested access, cache refresh and parse recovery smoke\n";
} finally {
    if (is_file($path)) {
        unlink($path);
    }
}

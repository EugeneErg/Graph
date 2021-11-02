<?php declare(strict_types = 1);

if (!class_exists(ValueError::class)) {
    class ValueError extends Error {}
}
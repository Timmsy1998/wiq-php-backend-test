<?php
namespace Wiq\Model;

/**
 * Simple DTO for menu data — no logic here, just structure.
 */
class Menu
{
    public function __construct(public int $id, public string $name) {}
}

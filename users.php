<?php

/**
 * FILE: users.php
 * PURPOSE: Hardcoded user database for authentication.
 * NOTE: This file is automatically managed by the CRUD API.
 */

$users = array (
  'admin' => 
  array (
    'hash' => '$2y$10$wK/p.k.t.g5.t.n.h.t.t.t.t.t.t.t.t.t.t.t.t.t.t.t.t.t.t.',
    'role' => 'admin',
    'name' => 'System Admin',
  ),
  'user' => 
  array (
    'hash' => '$2y$10$t.t.t.t.t.t.t.t.t.t.t.t.t.t.t.t.t.t.t.t.t.t.t.t.t.t.t.t.',
    'role' => 'user',
    'name' => 'Academic Staff',
  ),
);
?>
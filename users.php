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
    'permissions' => 
    array (
      0 => 'view_academic',
      1 => 'view_marketing',
      2 => 'view_finance',
      3 => 'is_admin',
    ),
  ),
  'user' => 
  array (
    'hash' => '$2y$10$q9m9SePqGwdufqWuOXWDLuXnSeZzwLMmzkqSEjjcNb6gJ.jwCDoRm',
    'role' => 'user',
    'name' => 'Academic Staff',
    'permissions' => 
    array (
      0 => 'view_academic',
    ),
  ),
  'user3' => 
  array (
    'hash' => '$2y$10$z7bZO.RVUTLQjbqOu80lv.hIA5/UETvWp.kgxqe4YGyDEasMDAM3a',
    'role' => 'admin',
    'name' => 'user3',
    'permissions' => 
    array (
      0 => 'view_academic',
      1 => 'view_marketing',
      2 => 'view_finance',
      3 => 'is_admin',
    ),
  ),
);
?>
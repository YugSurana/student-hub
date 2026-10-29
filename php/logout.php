<?php
// ========================================================
// Practical 10: Secure Logout Script (php/logout.php)
// Completely destroys session, unsets cookies, clears DB token
// ========================================================

require_once __DIR__ . '/auth.php';

logout_user(true);

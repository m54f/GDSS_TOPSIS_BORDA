<?php
/**
 * GDSS Relawan BPBD - Entry Point
 * Redirects to login page or dashboard based on session
 */
require_once 'config/database.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
} else {
    redirect('modules/auth/login.php');
}
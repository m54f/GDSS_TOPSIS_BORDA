<?php
/**
 * Get Penilaian API - GDSS Relawan BPBD
 * Wrapper file for restructured folder organization
 */
require_once dirname(__DIR__, 2) . '/config/database.php';

chdir(dirname(__DIR__, 2));
include 'get_penilaian.php';

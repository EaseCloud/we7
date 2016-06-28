<?php

function D($msg, $echo = false)
{
    if (!SZ_YI_DEBUG) return;
    if (!$echo) $msg = var_export($msg, true);
    file_put_contents(__DIR__ . '/D.txt', $msg . "\n", FILE_APPEND);
}


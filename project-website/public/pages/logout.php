<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
do_logout();
redirect('/index.php?page=login');


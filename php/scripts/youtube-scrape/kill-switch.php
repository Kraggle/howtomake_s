<?php
error_log(json_encode(filter_var($_REQUEST['kill'], FILTER_VALIDATE_BOOLEAN), JSON_PRETTY_PRINT));
file_put_contents('kill-switch.json', json_encode(['kill' => filter_var($_REQUEST['kill'], FILTER_VALIDATE_BOOLEAN)]));

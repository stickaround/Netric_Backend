<?php
$address = "comment.6d4d20bf-5fc8-4ef2-ad6b-1d28c96ea94b@test.com";
preg_match('!comment.([a-z0-9\-]*)@test.com!i', $address, $matches);
echo var_export($matches, true);

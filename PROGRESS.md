Working on: Fixing boolean field handling in dt-workflows/workflows-execution-handler.php
Fixed action_update in dt-workflows/workflows-execution-handler.php by removing the redundant (bool) cast and using filter_var directly, which is more robust.
Linted successfully.
COMPLETE

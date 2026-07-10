### Task Summary
Modify the workflow execution handler to properly handle the update of boolean fields by correctly casting string values ("true"/"false") to boolean types, instead of passing the raw string which causes both "true" and "false" to be treated as true. This will be implemented in `dt-workflows/workflows-execution-handler.php`.

### Files to Modify
- dt-workflows/workflows-execution-handler.php — fix the boolean value handling in `action_update`.

### Implementation Plan
1. Create a PHPUnit test to reproduce the issue by triggering a workflow that sets a boolean field to true, and another to set it to false, verifying both work correctly.
2. In `dt-workflows/workflows-execution-handler.php`, locate the `action_update` function.
3. Modify the `case 'boolean'` block to use `(bool) filter_var($value, FILTER_VALIDATE_BOOLEAN)` to correctly cast the value to a boolean type.
4. Run the newly created test and existing tests to ensure no regressions.

### Acceptance Criteria
- [ ] PHP lint passes with no errors
- [ ] PHPCS reports no violations on modified files
- [ ] PHPUnit multisite suite passes
- [ ] Boolean field can be updated to True via Workflow
- [ ] Boolean field can be updated to False via Workflow

### Edge Cases & Constraints
- Do not modify `functions.php`.
- Do not drop or alter existing database tables.
- Handle string values "true" and "false" correctly.

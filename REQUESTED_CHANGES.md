# Requested Changes (PR #6 review, since 2026-07-10T09:11:09Z)

## PR comments
- **cairocoder01**: **Code Review**

**Summary of change:** action_update() now coerces the incoming boolean field value with filter_var(..., FILTER_VALIDATE_BOOLEAN) instead of passing the raw value straight through, so string values like "false" (truthy in PHP) are correctly converted to false before being handed to DT_Posts::update_post(). This matches the boolean-coercion convention already used elsewhere (e.g. dt-posts/posts.php:2899), and the added unit test covers both "true"/"false" cases.

**Medium**

- **dt-workflows/workflows-execution-handler.php:151-178 (condition_equals / condition_not_equals) have the same underlying bug this PR fixes, and it isn't addressed here.** Both still compare boolean fields with boolval( $field ) === boolval( $value ). Since $value for a boolean condition/action comes from the same JSON-decoded workflow config as the $value fixed in action_update(), a string "false" will make boolval( $value ) evaluate to true (any non-empty PHP string is truthy), not false.
  This directly affects the reliability of the fix in this PR: already_executed_actions() (line 454) calls condition_equals( 'boolean', $field, $action->value ) to decide whether an update action has already been applied. If a workflow's action value is the string "false" and the field is currently true, condition_equals returns boolval(true) === boolval("false") -> true === true -> true, so the action is treated as "already executed" and exec_actions() (line 495, ! in_array( false, $already_executed )) skips calling action_update() entirely - the boolean field is never actually flipped to false. The same issue applies to any user-configured workflow condition of the form "boolean field equals false" (process_condition(), line 114).
  Worth confirming whether this is in scope for this PR (it's the same root cause - string "false" being truthy), since otherwise the newly-fixed action_update() may not get invoked in the exact scenario it was meant to fix.

---
Looks close to ready - the direct fix in action_update() is correct and well-tested, but the sibling condition_equals/condition_not_equals logic likely needs the same treatment for the fix to be reliable end-to-end in real workflows.

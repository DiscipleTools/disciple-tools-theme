# Translation Instructions for Disciple.Tools

Instructions for reviewing and completing PO file translations. This document is designed to be used as context for LLM-assisted translation work.

---

## CRITICAL: Read Before Translating

**BEFORE translating any strings, you MUST:**

1. **Read the glossary** at [glossary.md](glossary.md) - it contains domain-specific terms with descriptions
2. **Identify how key terms are already translated** in the existing PO file
3. **Use the SAME translation for each term throughout** - consistency is essential

---

## Required Term Consistency

**ALL terms in [glossary.md](glossary.md) MUST be translated consistently throughout the entire file.**

The glossary contains 100+ domain-specific terms organized by category:
- Core Concepts (Contact, Group, Record, Coalition, DMM)
- Users & Roles (Multiplier, Dispatcher, Coach, Strategist, etc.)
- Assignment Relationships (Assigned To, Sub-assigned, Accepted, Decline)
- Coaching Relationships (Coach, Coached by, Is Coaching)
- System & Interface (Filter, Follow, Share, Favorite, Magic Link, etc.)
- Contact Status (New, Active, Paused, Closed, Archived, etc.)
- Reason Paused / Reason Closed options
- Seeker Path stages
- Faith Status & Milestones
- Group Types & Status
- Church Health metrics
- Metrics & Reports terms
- GenMapper & Maps terms

### Before Translating: Build Your Term Map

For EVERY term in the glossary, search the PO file to find existing translations:

```bash
# Example: Find how key terms are already translated
grep -A1 'msgid "Contact"$' <lang>.po
grep -A1 'msgid "Group"$' <lang>.po
grep -A1 'msgid "Record"$' <lang>.po
grep -A1 'msgid "Multiplier"$' <lang>.po
grep -A1 'msgid "Seeker"$' <lang>.po
grep -A1 'msgid "Coach"$' <lang>.po
grep -A1 'msgid "Filter"$' <lang>.po
grep -A1 'msgid "Active"$' <lang>.po
grep -A1 'msgid "Paused"$' <lang>.po
# ... continue for all glossary terms
```

Document these mappings and use them consistently in ALL translations.

### Terms to Keep Untranslated
| English Term | Reason |
|--------------|--------|
| Disciple.Tools | Product name |
| Joshua Project | External database name |
| GenMapper | Methodology name (may be kept or translated) |
| WhatsApp, Facebook, etc. | Brand names |

---

## Translation Process (Follow This Order)

### Step 1: Establish Term Translations

**BEFORE making any changes**, search the PO file to find existing translations for key terms:

```bash
# Find how "Contact" is translated
grep -A1 'msgid "Contact"$' <lang>.po

# Find how "Record" is translated
grep -A1 'msgid "Record"$' <lang>.po

# Find how "Group" is translated
grep -A1 'msgid "Group"$' <lang>.po
```

Document the translations you find. Use these SAME translations in all new strings.

### Step 2: Fix Untranslated Strings

Find strings with empty `msgstr ""` and translate them:
- Use established term translations from Step 1
- Preserve all format specifiers (`%s`, `%1$s`, `%2$s`)
- Keep `\n` for newlines in multiline strings

### Step 3: Fix Fuzzy Strings

Find strings marked `#, fuzzy` - these have outdated translations:
- Review the current translation
- Update if needed
- Remove the `#, fuzzy` flag
- Remove any `#|` lines (old msgid references)

### Step 4: Verify Consistency

**REQUIRED**: After translating, verify term consistency:

```bash
# Check all translations of "Contact" are the same
grep -i "kontakt" <lang>.po | head -20  # (use your translated term)

# Check format specifiers match
python batch_translate.py --analyze <lang>.po
```

### Step 5: Validate File

```bash
msgfmt --statistics -o /dev/null <lang>.po
```

---

## Format Specifier Rules

**CRITICAL**: Format specifiers must be preserved exactly.

| Specifier | Meaning | Example |
|-----------|---------|---------|
| `%s` | String placeholder | "Hello %s" → "Hallo %s" |
| `%d` | Number placeholder | "%d items" → "%d Elemente" |
| `%1$s`, `%2$s` | Ordered placeholders | Can be reordered for grammar |

### Examples

**Correct:**
```
msgid "Assigned to %s"
msgstr "Zugewiesen an %s"
```

**Correct (reordered):**
```
msgid "%1$s assigned to %2$s"
msgstr "%2$s wurde %1$s zugewiesen"
```

**WRONG - missing specifier:**
```
msgid "Assigned to %s"
msgstr "Zugewiesen"  ← ERROR: missing %s
```

**WRONG - broken specifier:**
```
msgid "Duplicates on: %s"
msgstr "Duplikate bei: %"  ← ERROR: incomplete %
```

---

## Common Mistakes to Avoid

### 1. Inconsistent Term Translation
❌ Wrong:
```
msgstr "Kontakt erstellen"      # Uses "Kontakt"
msgstr "Datensatz bearbeiten"   # Uses "Datensatz" for same concept
```
✅ Correct: Use the same term for "Record" throughout

### 2. Missing Format Specifiers
❌ Wrong: Removing `%s` from translation
✅ Correct: Keep all `%s`, `%1$s`, `%2$s` in translation

### 3. Breaking Multiline Strings
❌ Wrong: Real newlines in msgstr
```
msgstr "Line 1
Line 2"
```
✅ Correct: Use `\n` and proper PO multiline format
```
msgstr ""
"Line 1\n"
"Line 2"
```

### 4. Translating Placeholders
❌ Wrong: Translating `{{name}}` or `{{link}}`
✅ Correct: Keep template placeholders exactly as-is

### 5. Not Removing Fuzzy Flag
❌ Wrong: Updating translation but leaving `#, fuzzy`
✅ Correct: Remove fuzzy flag after fixing translation

---

## LLM Translation Checklist

When asked to translate a PO file, follow this checklist:

- [ ] **Read glossary.md first** to understand domain terms
- [ ] **Search for existing term translations** before starting
- [ ] **Document the term mapping** (Contact→X, Group→Y, etc.)
- [ ] **Translate untranslated strings** using consistent terms
- [ ] **Fix fuzzy strings** and remove fuzzy flags
- [ ] **Verify format specifiers** are preserved in all translations
- [ ] **Check term consistency** across the entire file
- [ ] **Validate with msgfmt** if possible

---

## Batch Translation Tool

For large numbers of translations, use the batch script:

```bash
# Analyze current status
python batch_translate.py --analyze <lang>.po

# Export strings needing translation to JSON
python batch_translate.py <lang>.po --export translations.json

# Apply translations from JSON
python batch_translate.py <lang>.po translations.json

# Validate result
msgfmt --statistics -o /dev/null <lang>.po
```

### JSON Format
```json
{
  "Cancel": "Abbrechen",
  "Save": "Speichern",
  "Emails will be sent from: %s": "E-Mails werden gesendet von: %s",
  "Context::msgid": "Translation for context-specific string"
}
```

---

## Quick Reference: PO File Format

```po
#. Translator comment
#: file.php:123
#, fuzzy, php-format
msgctxt "Optional context"
msgid "English text"
msgstr "Translated text"
```

- `#.` - Comment for translators
- `#:` - File reference
- `#,` - Flags (fuzzy, php-format, etc.)
- `#|` - Previous msgid (remove when fixing fuzzy)
- `msgctxt` - Context for ambiguous strings
- `msgid` - Original English string
- `msgstr` - Translation (empty = untranslated)

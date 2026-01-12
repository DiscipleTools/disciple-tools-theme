#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Batch Translation Tool for Disciple.Tools PO Files

This script applies translations from a JSON dictionary file to a PO file.
It can fix untranslated strings, update fuzzy strings, and validate format specifiers.

Usage:
    python batch_translate.py <po_file> <translations_json>
    python batch_translate.py de_DE.po translations_de.json
    python batch_translate.py --analyze <po_file>  # Analyze only, no changes
    python batch_translate.py --export <po_file> <output_json>  # Export untranslated for editing

Examples:
    # Analyze a PO file to see what needs translation
    python batch_translate.py --analyze es_ES.po

    # Export untranslated strings to a JSON file for editing
    python batch_translate.py --export es_ES.po translations_es.json

    # Apply translations from JSON file
    python batch_translate.py es_ES.po translations_es.json
"""

import re
import sys
import json
import argparse
from pathlib import Path
from typing import Dict, List, Tuple, Optional
from dataclasses import dataclass


@dataclass
class POEntry:
    """Represents a single PO file entry."""
    comments: List[str]
    references: List[str]
    flags: List[str]
    msgctxt: Optional[str]
    msgid: str
    msgid_plural: Optional[str]
    msgstr: str
    msgstr_plural: Dict[int, str]
    is_obsolete: bool
    line_number: int
    raw: str


class POFile:
    """Parser and manipulator for PO files."""

    def __init__(self, filepath: str):
        self.filepath = Path(filepath)
        self.header = ""
        self.entries: List[POEntry] = []
        self._parse()

    def _parse(self):
        """Parse the PO file into entries."""
        with open(self.filepath, 'r', encoding='utf-8') as f:
            content = f.read()

        # Split into blocks (entries separated by blank lines)
        blocks = re.split(r'\n\n+', content)

        for i, block in enumerate(blocks):
            if not block.strip():
                continue

            entry = self._parse_block(block, i)
            if entry:
                if entry.msgid == "" and i == 0:
                    # This is the header
                    self.header = block
                else:
                    self.entries.append(entry)

    def _parse_block(self, block: str, line_num: int) -> Optional[POEntry]:
        """Parse a single block into a POEntry."""
        lines = block.split('\n')

        comments = []
        references = []
        flags = []
        msgctxt = None
        msgid_lines = []
        msgid_plural_lines = []
        msgstr_lines = []
        msgstr_plural = {}
        is_obsolete = False
        current_field = None
        current_plural_idx = None

        for line in lines:
            # Check for obsolete entries
            if line.startswith('#~'):
                is_obsolete = True
                line = line[2:].strip()

            # Comments
            if line.startswith('#.'):
                comments.append(line[2:].strip())
            elif line.startswith('#:'):
                references.append(line[2:].strip())
            elif line.startswith('#,'):
                flags.extend([f.strip() for f in line[2:].split(',')])
            elif line.startswith('#|'):
                # Previous msgid (for fuzzy), skip
                pass
            elif line.startswith('#'):
                comments.append(line[1:].strip())

            # msgctxt
            elif line.startswith('msgctxt '):
                current_field = 'msgctxt'
                match = re.match(r'msgctxt\s+"(.*)"', line)
                if match:
                    msgctxt = match.group(1)

            # msgid
            elif line.startswith('msgid '):
                current_field = 'msgid'
                match = re.match(r'msgid\s+"(.*)"', line)
                if match:
                    msgid_lines.append(match.group(1))

            # msgid_plural
            elif line.startswith('msgid_plural '):
                current_field = 'msgid_plural'
                match = re.match(r'msgid_plural\s+"(.*)"', line)
                if match:
                    msgid_plural_lines.append(match.group(1))

            # msgstr[n]
            elif re.match(r'msgstr\[\d+\]', line):
                match = re.match(r'msgstr\[(\d+)\]\s+"(.*)"', line)
                if match:
                    idx = int(match.group(1))
                    current_field = 'msgstr_plural'
                    current_plural_idx = idx
                    msgstr_plural[idx] = match.group(2)

            # msgstr
            elif line.startswith('msgstr '):
                current_field = 'msgstr'
                match = re.match(r'msgstr\s+"(.*)"', line)
                if match:
                    msgstr_lines.append(match.group(1))

            # Continuation line
            elif line.startswith('"'):
                match = re.match(r'"(.*)"', line)
                if match:
                    value = match.group(1)
                    if current_field == 'msgid':
                        msgid_lines.append(value)
                    elif current_field == 'msgid_plural':
                        msgid_plural_lines.append(value)
                    elif current_field == 'msgstr':
                        msgstr_lines.append(value)
                    elif current_field == 'msgstr_plural' and current_plural_idx is not None:
                        msgstr_plural[current_plural_idx] += value
                    elif current_field == 'msgctxt':
                        msgctxt = (msgctxt or "") + value

        msgid = ''.join(msgid_lines)
        msgid_plural = ''.join(msgid_plural_lines) if msgid_plural_lines else None
        msgstr = ''.join(msgstr_lines)

        return POEntry(
            comments=comments,
            references=references,
            flags=flags,
            msgctxt=msgctxt,
            msgid=msgid,
            msgid_plural=msgid_plural,
            msgstr=msgstr,
            msgstr_plural=msgstr_plural,
            is_obsolete=is_obsolete,
            line_number=line_num,
            raw=block
        )

    def get_untranslated(self) -> List[POEntry]:
        """Get all entries with empty translations."""
        return [e for e in self.entries
                if not e.is_obsolete and not e.msgstr and not e.msgstr_plural]

    def get_fuzzy(self) -> List[POEntry]:
        """Get all fuzzy entries."""
        return [e for e in self.entries
                if not e.is_obsolete and 'fuzzy' in e.flags]

    def get_translated(self) -> List[POEntry]:
        """Get all properly translated entries."""
        return [e for e in self.entries
                if not e.is_obsolete and (e.msgstr or e.msgstr_plural) and 'fuzzy' not in e.flags]


def format_msgstr(text: str) -> str:
    """Format a translation string for PO file output."""
    # Escape backslashes and quotes
    text = text.replace('\\', '\\\\')
    text = text.replace('"', '\\"')
    # But preserve intentional \n for newlines
    text = text.replace('\\\\n', '\\n')
    return text


def validate_format_specifiers(msgid: str, msgstr: str) -> List[str]:
    """Check that format specifiers match between msgid and msgstr."""
    issues = []

    # Find format specifiers
    msgid_specs = re.findall(r'%(?:\d+\$)?[sdf]', msgid)
    msgstr_specs = re.findall(r'%(?:\d+\$)?[sdf]', msgstr)

    if sorted(msgid_specs) != sorted(msgstr_specs):
        issues.append(f"Format specifier mismatch: expected {msgid_specs}, got {msgstr_specs}")

    # Check for broken specifiers
    broken = re.findall(r'%(?![sdf\d%\n])', msgstr)
    if broken:
        issues.append(f"Broken format specifiers: {broken}")

    return issues


def apply_translations(po: POFile, translations: Dict[str, str], fix_fuzzy: bool = True) -> Dict[str, int]:
    """
    Apply translations from dictionary to PO file.

    Args:
        po: POFile object
        translations: Dict mapping msgid to msgstr
        fix_fuzzy: Whether to also fix fuzzy entries

    Returns:
        Statistics dict
    """
    stats = {
        'untranslated_fixed': 0,
        'fuzzy_fixed': 0,
        'skipped_format_error': 0,
        'not_found': 0
    }

    for entry in po.entries:
        if entry.is_obsolete:
            continue

        # Check if we have a translation for this entry
        key = entry.msgid
        if entry.msgctxt:
            key = f"{entry.msgctxt}::{entry.msgid}"

        if key in translations or entry.msgid in translations:
            translation = translations.get(key) or translations.get(entry.msgid)

            # Validate format specifiers
            issues = validate_format_specifiers(entry.msgid, translation)
            if issues:
                print(f"  Warning: {entry.msgid[:50]}...")
                for issue in issues:
                    print(f"    - {issue}")
                stats['skipped_format_error'] += 1
                continue

            is_untranslated = not entry.msgstr and not entry.msgstr_plural
            is_fuzzy = 'fuzzy' in entry.flags

            if is_untranslated:
                entry.msgstr = translation
                stats['untranslated_fixed'] += 1
            elif is_fuzzy and fix_fuzzy:
                entry.msgstr = translation
                entry.flags = [f for f in entry.flags if f != 'fuzzy']
                stats['fuzzy_fixed'] += 1

    return stats


def write_po_file(po: POFile, output_path: Optional[str] = None):
    """Write the PO file back to disk."""
    output = output_path or po.filepath

    lines = []

    # Header
    if po.header:
        lines.append(po.header)
        lines.append("")

    # Entries
    for entry in po.entries:
        entry_lines = []

        # Comments
        for comment in entry.comments:
            if comment:
                entry_lines.append(f"#. {comment}" if not comment.startswith(' ') else f"#{comment}")

        # References
        for ref in entry.references:
            entry_lines.append(f"#: {ref}")

        # Flags (excluding fuzzy if we fixed it)
        if entry.flags:
            entry_lines.append(f"#, {', '.join(entry.flags)}")

        prefix = "#~ " if entry.is_obsolete else ""

        # msgctxt
        if entry.msgctxt:
            entry_lines.append(f'{prefix}msgctxt "{entry.msgctxt}"')

        # msgid
        if '\n' in entry.msgid or len(entry.msgid) > 70:
            # Multiline
            entry_lines.append(f'{prefix}msgid ""')
            for part in split_long_string(entry.msgid):
                entry_lines.append(f'{prefix}"{part}"')
        else:
            entry_lines.append(f'{prefix}msgid "{entry.msgid}"')

        # msgid_plural
        if entry.msgid_plural:
            if '\n' in entry.msgid_plural or len(entry.msgid_plural) > 70:
                entry_lines.append(f'{prefix}msgid_plural ""')
                for part in split_long_string(entry.msgid_plural):
                    entry_lines.append(f'{prefix}"{part}"')
            else:
                entry_lines.append(f'{prefix}msgid_plural "{entry.msgid_plural}"')

        # msgstr / msgstr[n]
        if entry.msgstr_plural:
            for idx in sorted(entry.msgstr_plural.keys()):
                val = entry.msgstr_plural[idx]
                if '\n' in val or len(val) > 70:
                    entry_lines.append(f'{prefix}msgstr[{idx}] ""')
                    for part in split_long_string(val):
                        entry_lines.append(f'{prefix}"{part}"')
                else:
                    entry_lines.append(f'{prefix}msgstr[{idx}] "{val}"')
        else:
            msgstr = format_msgstr(entry.msgstr) if entry.msgstr else ""
            if '\n' in entry.msgstr or len(msgstr) > 70:
                entry_lines.append(f'{prefix}msgstr ""')
                for part in split_long_string(msgstr):
                    entry_lines.append(f'{prefix}"{part}"')
            else:
                entry_lines.append(f'{prefix}msgstr "{msgstr}"')

        lines.append('\n'.join(entry_lines))

    with open(output, 'w', encoding='utf-8') as f:
        f.write('\n\n'.join(lines))
        f.write('\n')


def split_long_string(s: str) -> List[str]:
    """Split a long string into lines for PO file format."""
    parts = []
    # Split on \n first
    segments = s.split('\\n')
    for i, segment in enumerate(segments):
        if i < len(segments) - 1:
            segment += '\\n'
        if segment:
            parts.append(segment)
    return parts if parts else ['']


def export_for_translation(po: POFile, output_path: str, include_fuzzy: bool = True):
    """Export untranslated and fuzzy strings to JSON for editing."""
    export = {
        "_comment": "Add translations below. Use 'context::msgid' as key for context-specific strings.",
        "_instructions": [
            "1. Add your translation as the value for each key",
            "2. Keep format specifiers like %s, %1$s, %2$s in the same order",
            "3. Use \\n for newlines in multiline strings",
            "4. Save and run: python batch_translate.py <po_file> <this_file>"
        ],
        "translations": {}
    }

    # Untranslated
    for entry in po.get_untranslated():
        key = entry.msgid
        if entry.msgctxt:
            key = f"{entry.msgctxt}::{entry.msgid}"

        export["translations"][key] = {
            "msgid": entry.msgid,
            "context": entry.msgctxt,
            "references": entry.references[:2] if entry.references else [],
            "translation": ""  # To be filled in
        }

    # Fuzzy
    if include_fuzzy:
        for entry in po.get_fuzzy():
            key = entry.msgid
            if entry.msgctxt:
                key = f"{entry.msgctxt}::{entry.msgid}"

            export["translations"][key] = {
                "msgid": entry.msgid,
                "context": entry.msgctxt,
                "references": entry.references[:2] if entry.references else [],
                "current_translation": entry.msgstr,
                "translation": ""  # To be filled in or copy from current
            }

    with open(output_path, 'w', encoding='utf-8') as f:
        json.dump(export, f, ensure_ascii=False, indent=2)

    print(f"Exported {len(export['translations'])} strings to {output_path}")


def load_translations(json_path: str) -> Dict[str, str]:
    """Load translations from JSON file."""
    with open(json_path, 'r', encoding='utf-8') as f:
        data = json.load(f)

    translations = {}

    # Handle different JSON formats
    if "translations" in data:
        # Export format with metadata
        for key, value in data["translations"].items():
            if isinstance(value, dict):
                if value.get("translation"):
                    translations[key] = value["translation"]
            elif isinstance(value, str) and value:
                translations[key] = value
    else:
        # Simple key-value format
        for key, value in data.items():
            if not key.startswith("_") and value:
                translations[key] = value

    return translations


def analyze_po_file(po: POFile):
    """Print analysis of PO file translation status."""
    untranslated = po.get_untranslated()
    fuzzy = po.get_fuzzy()
    translated = po.get_translated()
    obsolete = [e for e in po.entries if e.is_obsolete]

    total = len(po.entries) - len(obsolete)

    print(f"\n{'='*60}")
    print(f"PO File Analysis: {po.filepath.name}")
    print(f"{'='*60}")
    print(f"Total active entries: {total}")
    print(f"  Translated:   {len(translated):4d} ({100*len(translated)/total:.1f}%)")
    print(f"  Untranslated: {len(untranslated):4d} ({100*len(untranslated)/total:.1f}%)")
    print(f"  Fuzzy:        {len(fuzzy):4d} ({100*len(fuzzy)/total:.1f}%)")
    print(f"  Obsolete:     {len(obsolete):4d}")

    if untranslated:
        print(f"\n--- Sample Untranslated Strings (first 10) ---")
        for entry in untranslated[:10]:
            ctx = f" [{entry.msgctxt}]" if entry.msgctxt else ""
            ref = entry.references[0] if entry.references else ""
            print(f"  • {entry.msgid[:60]}{ctx}")
            if ref:
                print(f"    {ref}")

    if fuzzy:
        print(f"\n--- Sample Fuzzy Strings (first 10) ---")
        for entry in fuzzy[:10]:
            ctx = f" [{entry.msgctxt}]" if entry.msgctxt else ""
            print(f"  • {entry.msgid[:50]}{ctx}")
            print(f"    Current: {entry.msgstr[:50]}")

    # Check for format specifier issues
    print(f"\n--- Format Specifier Check ---")
    issues_found = 0
    for entry in translated + fuzzy:
        if entry.msgstr:
            issues = validate_format_specifiers(entry.msgid, entry.msgstr)
            if issues:
                issues_found += 1
                if issues_found <= 5:
                    print(f"  • {entry.msgid[:50]}...")
                    for issue in issues:
                        print(f"    {issue}")

    if issues_found > 5:
        print(f"  ... and {issues_found - 5} more issues")
    elif issues_found == 0:
        print("  No format specifier issues found!")

    print(f"\n{'='*60}\n")


def main():
    parser = argparse.ArgumentParser(
        description="Batch translation tool for PO files",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog=__doc__
    )

    parser.add_argument('po_file', help='Path to the PO file')
    parser.add_argument('translations', nargs='?', help='Path to translations JSON file')
    parser.add_argument('--analyze', '-a', action='store_true',
                        help='Analyze PO file without making changes')
    parser.add_argument('--export', '-e', metavar='OUTPUT',
                        help='Export untranslated strings to JSON file')
    parser.add_argument('--no-fuzzy', action='store_true',
                        help='Do not update fuzzy entries')
    parser.add_argument('--dry-run', '-n', action='store_true',
                        help='Show what would be done without writing')
    parser.add_argument('--output', '-o', metavar='FILE',
                        help='Write to different output file')

    args = parser.parse_args()

    # Load PO file
    print(f"Loading {args.po_file}...")
    po = POFile(args.po_file)

    # Analyze mode
    if args.analyze:
        analyze_po_file(po)
        return 0

    # Export mode
    if args.export:
        export_for_translation(po, args.export)
        return 0

    # Translation mode
    if not args.translations:
        parser.error("translations JSON file is required (or use --analyze/--export)")

    print(f"Loading translations from {args.translations}...")
    translations = load_translations(args.translations)
    print(f"Loaded {len(translations)} translations")

    # Apply translations
    print("Applying translations...")
    stats = apply_translations(po, translations, fix_fuzzy=not args.no_fuzzy)

    print(f"\nResults:")
    print(f"  Untranslated fixed: {stats['untranslated_fixed']}")
    print(f"  Fuzzy fixed:        {stats['fuzzy_fixed']}")
    if stats['skipped_format_error']:
        print(f"  Skipped (format):   {stats['skipped_format_error']}")

    # Write output
    if not args.dry_run:
        output_path = args.output or args.po_file
        print(f"\nWriting to {output_path}...")
        write_po_file(po, output_path)
        print("Done!")
    else:
        print("\nDry run - no changes written")

    return 0


if __name__ == '__main__':
    sys.exit(main())

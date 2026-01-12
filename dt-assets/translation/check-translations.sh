#!/bin/bash
# Translation quality checker using pofilter (translate-toolkit)
# Usage: ./check-translations.sh [language_code]
# Example: ./check-translations.sh fr_FR
# Run without args to check all languages

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Check if pofilter is installed
if ! command -v pofilter &> /dev/null; then
    echo "Error: pofilter not found. Install with: brew install translate-toolkit"
    exit 1
fi

# Tests to run (comment out any you want to skip)
TESTS=(endwhitespace doublespacing printf newlines doublewords nplurals endpunc)
# Note: endpunc may have false positives for non-Latin scripts with different punctuation
# Add these if needed: startpunc unchanged

OUTPUT_DIR="/tmp/po-errors"
mkdir -p "$OUTPUT_DIR"

check_file() {
    local po_file="$1"
    local base_name=$(basename "$po_file" .po)
    local output_file="$OUTPUT_DIR/${base_name}_errors.po"

    # Build -t flags for each test
    local test_flags=""
    for test in "${TESTS[@]}"; do
        test_flags="$test_flags -t $test"
    done

    pofilter --nofuzzy $test_flags "$po_file" "$output_file" 2>/dev/null

    local count=$(grep -c "^msgid \"" "$output_file" 2>/dev/null || echo 0)
    count=$((count - 1))  # Subtract header

    if [ "$count" -gt 0 ]; then
        echo "$base_name: $count issues"

        # Show breakdown by type
        grep "^# (pofilter)" "$output_file" 2>/dev/null | \
            sed 's/# (pofilter) /  - /' | \
            sed 's/:.*//' | \
            sort | uniq -c | sort -rn
        echo ""
    fi
}

if [ -n "$1" ]; then
    # Check specific language
    if [ -f "$1.po" ]; then
        check_file "$1.po"
    else
        echo "File not found: $1.po"
        exit 1
    fi
else
    # Check all languages
    echo "Checking all translation files..."
    echo "================================="
    echo ""

    for po_file in *.po; do
        check_file "$po_file"
    done

    echo "================================="
    echo "Error files saved to: $OUTPUT_DIR"
    echo ""
    echo "To view details for a specific language:"
    echo "  cat $OUTPUT_DIR/LANG_errors.po"
fi

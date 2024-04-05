#!/bin/bash

cd "$(dirname "${BASH_SOURCE[0]}")/../"

printf 'eslint version: %s\n' "$(npx eslint --version)"

eval npx eslint .

eval npx prettier --check .

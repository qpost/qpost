#!/bin/bash
git log --date=format:'%a %b %d, %Y' --all --pretty=format:"* **%ad** %d %s (%aN)" --no-merges --invert-grep --grep="Updated version number" >CHANGELOG.md

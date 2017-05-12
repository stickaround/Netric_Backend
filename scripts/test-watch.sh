#!/usr/bin/env bash

# This uses watchman from facebook to monitor file changes
# brew install watchman

# First run the tests
python test.py ${@}

# Now watch for future changes
watchman-make -p 'lib/**/*.php' 'test/**/*.php' \
 --make="clear && printf '\e[3J' && python test.py ${@}" \
 -t '-c test/phpunit.xml'

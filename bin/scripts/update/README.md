# Updates
This directory contains scrips that are used to update the data of netric.

## Guidelines
- All updates MUST be idempotent meaning they can be re-run without negative consequences
- All updates MUST have automated integration tests
- Updates are run after any schema changes and MUST work on the latest schema and be backwards compatible

## ./always

The always directory contains scripts that will be run every time setup/update is run.
This is useful for things like creating/validating default data sets (groupings, users, etc.)

## ./once

File name format:  [majorversiondirectory]/[minorversiondirectory]/[updatescript.php]

The once directory contain versioned updates. The first level directory is the major version
and MUST be 3 digits (leading zeros if needed). The second directory is the minor version number
and just like the major, it requires three digits.

Within the minor version directory we have the "points" which are files named with three digits
and .php. The reason for the three digits is so we can assure updates are executed
in the right consecutive oder. For example, 001.php will always be executed before 002.php.